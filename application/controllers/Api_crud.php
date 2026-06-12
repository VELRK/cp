<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CRUD API Controller
 * Provides GET (all/by ID), UPDATE, DELETE operations for all modules
 */
class Api_crud extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');

        // Load all models
        $this->load->model('Property_model');
        $this->load->model('Nb_property_model');
        $this->load->model('Blog_model');
        $this->load->model('Category_model');
        $this->load->model('City_model');
        $this->load->model('Location_model');
        $this->load->model('Banner_model');
        $this->load->model('Offer_banner_model');
        $this->load->model('Contact_model');
        $this->load->model('Enquiry_model');
        $this->load->model('User_model');
        $this->load->model('Notification_model');
        $this->load->model('Reelsvideo_model');
        $this->load->model('Video_model');
        $this->load->model('Referral_model');
        $this->load->model('Wishlist_model');
        $this->load->model('Mobile_banner_model');

        // Set JSON output
        $this->output->set_content_type('application/json');
    }

    /**
     * Send JSON response
     */
    private function _send_response($success, $data = null, $message = '', $errors = null)
    {
        $response = array(
            'success' => $success,
            'message' => $message
        );
        
        if ($success) {
            if ($data !== null) {
                $response['data'] = $data;
            }
        } else {
            if ($errors !== null) {
                $response['errors'] = $errors;
            }
        }
        
        $this->output->set_output(json_encode($response));
    }

    /**
     * Get input data - handles both JSON and form data
     */
    private function _get_input($key = null)
    {
        $content_type = $this->input->server('CONTENT_TYPE');
        if (strpos($content_type, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if ($key === null) {
                return $data ?: array();
            }
            
            return isset($data[$key]) ? $data[$key] : null;
        }
        
        if ($key === null) {
            return array_merge($this->input->post(), $this->input->get());
        }
        
        $value = $this->input->post($key);
        if ($value === null) {
            $value = $this->input->get($key);
        }
        
        return $value;
    }

    /**
     * Prepend base_url to file/image fields on a single object
     * @param object|array $item
     * @param array $fields        Plain path fields e.g. ['image','main_image']
     * @param array $json_fields   JSON-array fields e.g. ['gallery']
     */
    private function _add_base_url($item, $fields = array(), $json_fields = array())
    {
        if (empty($item)) return $item;
        $arr = is_object($item) ? (array)$item : $item;

        foreach ($fields as $field) {
            if (!empty($arr[$field]) && strpos($arr[$field], 'http') !== 0) {
                $arr[$field] = base_url($arr[$field]);
            }
        }

        foreach ($json_fields as $field) {
            if (!empty($arr[$field])) {
                $images = json_decode($arr[$field], true);
                if (is_array($images)) {
                    $arr[$field] = array_map(function($img) {
                        return (strpos($img, 'http') !== 0) ? base_url($img) : $img;
                    }, $images);
                }
            }
        }

        return (object)$arr;
    }

    /**
     * Apply _add_base_url to every item in a list
     */
    private function _add_base_url_list($items, $fields = array(), $json_fields = array())
    {
        return array_map(function($item) use ($fields, $json_fields) {
            return $this->_add_base_url($item, $fields, $json_fields);
        }, $items);
    }

    // ==================== PROPERTIES API ====================

    /**
     * GET /api/crud/properties
     * Get all properties
     * Query params: status (optional), limit (optional), offset (optional)
     */
    public function properties()
    {
        try {
            $limit  = $this->input->get('limit')  ? (int)$this->input->get('limit')  : 0;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            $properties = $this->Nb_property_model->get_all_active($limit, $offset);
            $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));

            $this->_send_response(true, array(
                'properties' => $properties,
                'total'      => count($properties),
                'limit'      => $limit,
                'offset'     => $offset,
            ), 'Properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/{id}
     * Get property by ID
     */
    public function property($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }

            if (!$id) {
                $this->_send_response(false, null, 'Property ID is required', array('id' => 'Property ID is required'));
                return;
            }

            $property = $this->Nb_property_model->get_by_id($id);

            if (!$property) {
                $this->_send_response(false, null, 'Property not found', array('id' => 'Property not found'));
                return;
            }

            $property = $this->_add_base_url($property, array('location_image'), array('images'));
            $this->_send_response(true, $property, 'Property retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving property', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/properties/{id}
     * Update property
     */
    public function update_property($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Property ID is required', array('id' => 'Property ID is required'));
                return;
            }
            
            $property = $this->Property_model->get_by_id($id);
            if (!$property) {
                $this->_send_response(false, null, 'Property not found', array('id' => 'Property not found'));
                return;
            }
            
            $input = $this->_get_input();
            
            // Remove id from update data
            unset($input['id']);
            
            $result = $this->Property_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Property_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Property updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update property', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating property', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/properties/{id}
     * Delete property
     */
    public function delete_property($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Property ID is required', array('id' => 'Property ID is required'));
                return;
            }
            
            $property = $this->Property_model->get_by_id($id);
            if (!$property) {
                $this->_send_response(false, null, 'Property not found', array('id' => 'Property not found'));
                return;
            }
            
            $result = $this->Property_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Property deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete property', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting property', array('message' => $e->getMessage()));
        }
    }

    // ==================== PROPERTIES FILTER APIs ====================

    /**
     * GET /api/crud/properties/by-location/{location_id}
     * Get all properties by location ID
     * Query params: limit, offset, sort_by
     */
    public function properties_by_location($location_id = null)
    {
        try {
            if (!$location_id) {
                $location_id = $this->uri->segment(5);
            }

            // No ID → return all properties
            if (!$location_id) {
                $properties = $this->Property_model->get_all('active');
                $properties = $this->_add_base_url_list($properties, array('main_image', 'floorplan'), array('gallery'));
                $this->_send_response(true, array(
                    'properties' => $properties,
                    'total'      => count($properties),
                ), 'All properties retrieved successfully');
                return;
            }

            $location = $this->Location_model->get_by_id($location_id);
            if (!$location) {
                $this->_send_response(false, null, 'Location not found', array('location_id' => 'Location not found'));
                return;
            }
            $location = $this->_add_base_url($location, array('image'));

            $filters = array(
                'location' => $location->name,
                'sort_by'  => $this->input->get('sort_by') ?: 'newest',
            );
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 0;
            if ($limit) {
                $filters['limit']  = $limit;
                $filters['offset'] = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            }

            $properties = $this->Property_model->search($filters);
            $properties = $this->_add_base_url_list($properties, array('main_image', 'floorplan'), array('gallery'));

            $this->_send_response(true, array(
                'location'   => $location,
                'properties' => $properties,
                'total'      => count($properties),
            ), 'Properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/by-category/{category_id}
     * Get all properties by category ID
     * Query params: limit, offset, sort_by
     */
    public function properties_by_category($category_id = null)
    {
        try {
            if (!$category_id) {
                $category_id = $this->uri->segment(5);
            }

            // No ID → return all properties
            if (!$category_id) {
                $properties = $this->Property_model->get_all('active');
                $properties = $this->_add_base_url_list($properties, array('main_image', 'floorplan'), array('gallery'));
                $this->_send_response(true, array(
                    'properties' => $properties,
                    'total'      => count($properties),
                ), 'All properties retrieved successfully');
                return;
            }

            $category = $this->Category_model->get_by_id($category_id);
            if (!$category) {
                $this->_send_response(false, null, 'Category not found', array('category_id' => 'Category not found'));
                return;
            }
            $category = $this->_add_base_url($category, array('image'));

            $filters = array(
                'category' => $category->category_name,
                'sort_by'  => $this->input->get('sort_by') ?: 'newest',
            );
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 0;
            if ($limit) {
                $filters['limit']  = $limit;
                $filters['offset'] = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            }

            $properties = $this->Property_model->search($filters);
            $properties = $this->_add_base_url_list($properties, array('main_image', 'floorplan'), array('gallery'));

            $this->_send_response(true, array(
                'category'   => $category,
                'properties' => $properties,
                'total'      => count($properties),
            ), 'Properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/by-city/{city_id}
     * Get all properties by city ID
     * Query params: limit, offset, sort_by
     */
    public function properties_by_city($city_id = null)
    {
        try {
            if (!$city_id) {
                $city_id = $this->uri->segment(5);
            }

            $limit  = $this->input->get('limit')  ? (int)$this->input->get('limit')  : 0;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            if (!$city_id) {
                $properties = $this->Nb_property_model->get_all_active($limit, $offset);
                $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));
                $this->_send_response(true, array(
                    'properties' => $properties,
                    'total'      => count($properties),
                ), 'All properties retrieved successfully');
                return;
            }

            $city = $this->db->get_where('nb_cities', array('id' => (int)$city_id))->row();
            if (!$city) {
                $this->_send_response(false, null, 'City not found', array('city_id' => 'City not found'));
                return;
            }
            $city = $this->_add_base_url($city, array('image'));

            $filters = array('city_id' => (int)$city_id);
            $properties = $this->Nb_property_model->search($filters, $limit, $offset);
            $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));

            $this->_send_response(true, array(
                'city'       => $city,
                'properties' => $properties,
                'total'      => count($properties),
                'limit'      => $limit,
                'offset'     => $offset,
            ), 'Properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/featured
     * Get featured properties (is_featured = 1)
     * Query params: limit, offset
     */
    public function properties_featured()
    {
        try {
            $limit  = $this->input->get('limit')  ? (int)$this->input->get('limit')  : 0;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            $properties = $this->Nb_property_model->featured($limit, $offset);
            $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));

            $this->_send_response(true, array(
                'properties' => $properties,
                'total'      => count($properties),
                'limit'      => $limit,
                'offset'     => $offset,
            ), 'Featured properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving featured properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/search
     * Search/filter properties with all supported filters
     * Query params: city_id, property_type, listing_type, min_price, max_price,
     *               bedrooms, locality_q, tags_best_rate_localities,
     *               tags_high_growth_localities, is_featured, sort, limit, offset
     */
    public function properties_search()
    {
        try {
            $limit  = $this->input->get('limit')  ? (int)$this->input->get('limit')  : 0;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            $filters = array();
            $map = array(
                'city_id', 'property_type', 'listing_type',
                'min_price', 'max_price', 'bedrooms', 'locality_q',
                'tags_best_rate_localities', 'tags_high_growth_localities',
                'is_featured', 'sort', 'owner_id',
            );
            foreach ($map as $key) {
                $val = $this->input->get($key);
                if ($val !== null && $val !== '') {
                    $filters[$key] = $val;
                }
            }

            $properties = $this->Nb_property_model->search($filters, $limit, $offset);
            $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));

            $this->_send_response(true, array(
                'properties' => $properties,
                'total'      => count($properties),
                'filters'    => $filters,
                'limit'      => $limit,
                'offset'     => $offset,
            ), 'Properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error searching properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/by-owner/{owner_id}
     * Get all active properties for a given owner (includes owner name + phone)
     * Query params: limit, offset
     */
    public function properties_by_owner($owner_id = null)
    {
        try {
            if (!$owner_id) {
                $owner_id = $this->uri->segment(5);
            }

            if (!$owner_id) {
                $this->_send_response(false, null, 'Owner ID is required', array('owner_id' => 'Owner ID is required'));
                return;
            }

            $properties = $this->Nb_property_model->for_owner((int)$owner_id);
            $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));

            $this->_send_response(true, array(
                'properties' => $properties,
                'total'      => count($properties),
                'owner_id'   => (int)$owner_id,
            ), 'Properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving owner properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/best-rated
     * Get properties tagged as best rated (tags_best_rate_localities = 1)
     * Query params: limit, offset
     */
    public function properties_best_rated()
    {
        try {
            $limit  = $this->input->get('limit')  ? (int)$this->input->get('limit')  : 0;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            $properties = $this->Nb_property_model->get_best_rated($limit, $offset);
            $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));

            $this->_send_response(true, array(
                'properties' => $properties,
                'total'      => count($properties),
                'limit'      => $limit,
                'offset'     => $offset,
            ), 'Best rated properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving best rated properties', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/properties/high-growth
     * Get properties tagged as high growth (tags_high_growth_localities = 1)
     * Query params: limit, offset
     */
    public function properties_high_growth()
    {
        try {
            $limit  = $this->input->get('limit')  ? (int)$this->input->get('limit')  : 0;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            $properties = $this->Nb_property_model->get_high_growth($limit, $offset);
            $properties = $this->_add_base_url_list($properties, array('location_image'), array('images'));

            $this->_send_response(true, array(
                'properties' => $properties,
                'total'      => count($properties),
                'limit'      => $limit,
                'offset'     => $offset,
            ), 'High growth properties retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving high growth properties', array('message' => $e->getMessage()));
        }
    }

    // ==================== BLOGS API ====================

    /**
     * GET /api/crud/blogs
     * Get all blogs
     */
    public function blogs()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            
            $blogs = $this->Blog_model->get_all($status);
            $total = count($blogs);
            
            if ($limit) {
                $blogs = array_slice($blogs, $offset, $limit);
            }
            
            $blogs = $this->_add_base_url_list($blogs, array(), array('gallery'));
            $this->_send_response(true, array(
                'blogs' => $blogs,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ), 'Blogs retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving blogs', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/blogs/{id}
     * Get blog by ID
     */
    public function blog($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Blog ID is required', array('id' => 'Blog ID is required'));
                return;
            }
            
            $blog = $this->Blog_model->get_by_id($id);
            
            if (!$blog) {
                $this->_send_response(false, null, 'Blog not found', array('id' => 'Blog not found'));
                return;
            }
            
            $blog = $this->_add_base_url($blog, array(), array('gallery'));
            $this->_send_response(true, $blog, 'Blog retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving blog', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/blogs/{id}
     * Update blog
     */
    public function update_blog($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Blog ID is required', array('id' => 'Blog ID is required'));
                return;
            }
            
            $blog = $this->Blog_model->get_by_id($id);
            if (!$blog) {
                $this->_send_response(false, null, 'Blog not found', array('id' => 'Blog not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Blog_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Blog_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Blog updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update blog', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating blog', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/blogs/{id}
     * Delete blog
     */
    public function delete_blog($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Blog ID is required', array('id' => 'Blog ID is required'));
                return;
            }
            
            $blog = $this->Blog_model->get_by_id($id);
            if (!$blog) {
                $this->_send_response(false, null, 'Blog not found', array('id' => 'Blog not found'));
                return;
            }
            
            $result = $this->Blog_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Blog deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete blog', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting blog', array('message' => $e->getMessage()));
        }
    }

    // ==================== CATEGORIES API ====================

    /**
     * GET /api/crud/categories
     * Get all categories
     */
    public function categories()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $categories = $this->Category_model->get_all($status);
            
            $categories = $this->_add_base_url_list($categories, array('image'));
            $this->_send_response(true, array('categories' => $categories), 'Categories retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving categories', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/categories/{id}
     * Get category by ID
     */
    public function category($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Category ID is required', array('id' => 'Category ID is required'));
                return;
            }
            
            $category = $this->Category_model->get_by_id($id);
            
            if (!$category) {
                $this->_send_response(false, null, 'Category not found', array('id' => 'Category not found'));
                return;
            }
            
            $category = $this->_add_base_url($category, array('image'));
            $this->_send_response(true, $category, 'Category retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving category', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/categories/{id}
     * Update category
     */
    public function update_category($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Category ID is required', array('id' => 'Category ID is required'));
                return;
            }
            
            $category = $this->Category_model->get_by_id($id);
            if (!$category) {
                $this->_send_response(false, null, 'Category not found', array('id' => 'Category not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Category_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Category_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Category updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update category', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating category', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/categories/{id}
     * Delete category
     */
    public function delete_category($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Category ID is required', array('id' => 'Category ID is required'));
                return;
            }
            
            $category = $this->Category_model->get_by_id($id);
            if (!$category) {
                $this->_send_response(false, null, 'Category not found', array('id' => 'Category not found'));
                return;
            }
            
            $result = $this->Category_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Category deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete category', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting category', array('message' => $e->getMessage()));
        }
    }

    // ==================== CITIES API ====================

    /**
     * GET /api/crud/cities
     * Get all cities
     */
    public function cities()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $cities = $this->City_model->get_all($status);
            
            $cities = $this->_add_base_url_list($cities, array('image'));
            $this->_send_response(true, array('cities' => $cities), 'Cities retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving cities', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/cities/{id}
     * Get city by ID
     */
    public function city($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'City ID is required', array('id' => 'City ID is required'));
                return;
            }
            
            $city = $this->City_model->get_by_id($id);
            
            if (!$city) {
                $this->_send_response(false, null, 'City not found', array('id' => 'City not found'));
                return;
            }
            
            $city = $this->_add_base_url($city, array('image'));
            $this->_send_response(true, $city, 'City retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving city', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/cities/{id}
     * Update city
     */
    public function update_city($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'City ID is required', array('id' => 'City ID is required'));
                return;
            }
            
            $city = $this->City_model->get_by_id($id);
            if (!$city) {
                $this->_send_response(false, null, 'City not found', array('id' => 'City not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->City_model->update($id, $input);
            
            if ($result) {
                $updated = $this->City_model->get_by_id($id);
                $this->_send_response(true, $updated, 'City updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update city', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating city', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/cities/{id}
     * Delete city
     */
    public function delete_city($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'City ID is required', array('id' => 'City ID is required'));
                return;
            }
            
            $city = $this->City_model->get_by_id($id);
            if (!$city) {
                $this->_send_response(false, null, 'City not found', array('id' => 'City not found'));
                return;
            }
            
            $result = $this->City_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'City deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete city', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting city', array('message' => $e->getMessage()));
        }
    }

    // ==================== LOCATIONS API ====================

    /**
     * GET /api/crud/locations
     * Get all locations
     */
    public function locations()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $city_id = $this->input->get('city_id') ?: null;
            
            if ($city_id) {
                $locations = $this->Location_model->get_by_city($city_id, $status);
            } else {
                $locations = $this->Location_model->get_all($status);
            }
            
            $locations = $this->_add_base_url_list($locations, array('image'));
            $this->_send_response(true, array('locations' => $locations), 'Locations retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving locations', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/locations/{id}
     * Get location by ID
     */
    public function location($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Location ID is required', array('id' => 'Location ID is required'));
                return;
            }
            
            $location = $this->Location_model->get_by_id($id);
            
            if (!$location) {
                $this->_send_response(false, null, 'Location not found', array('id' => 'Location not found'));
                return;
            }
            
            $location = $this->_add_base_url($location, array('image'));
            $this->_send_response(true, $location, 'Location retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving location', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/locations/{id}
     * Update location
     */
    public function update_location($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Location ID is required', array('id' => 'Location ID is required'));
                return;
            }
            
            $location = $this->Location_model->get_by_id($id);
            if (!$location) {
                $this->_send_response(false, null, 'Location not found', array('id' => 'Location not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Location_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Location_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Location updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update location', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating location', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/locations/{id}
     * Delete location
     */
    public function delete_location($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Location ID is required', array('id' => 'Location ID is required'));
                return;
            }
            
            $location = $this->Location_model->get_by_id($id);
            if (!$location) {
                $this->_send_response(false, null, 'Location not found', array('id' => 'Location not found'));
                return;
            }
            
            $result = $this->Location_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Location deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete location', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting location', array('message' => $e->getMessage()));
        }
    }

    // ==================== BANNERS API ====================

    /**
     * GET /api/crud/banners
     * Get all banners
     */
    public function banners()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $banners = $this->Banner_model->get_all($status);
            
            $banners = $this->_add_base_url_list($banners, array('image'));
            $this->_send_response(true, array('banners' => $banners), 'Banners retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving banners', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/banners/{id}
     * Get banner by ID
     */
    public function banner($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Banner ID is required', array('id' => 'Banner ID is required'));
                return;
            }
            
            $banner = $this->Banner_model->get_by_id($id);
            
            if (!$banner) {
                $this->_send_response(false, null, 'Banner not found', array('id' => 'Banner not found'));
                return;
            }
            
            $banner = $this->_add_base_url($banner, array('image'));
            $this->_send_response(true, $banner, 'Banner retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving banner', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/banners/{id}
     * Update banner
     */
    public function update_banner($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Banner ID is required', array('id' => 'Banner ID is required'));
                return;
            }
            
            $banner = $this->Banner_model->get_by_id($id);
            if (!$banner) {
                $this->_send_response(false, null, 'Banner not found', array('id' => 'Banner not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Banner_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Banner_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Banner updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update banner', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating banner', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/banners/{id}
     * Delete banner
     */
    public function delete_banner($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Banner ID is required', array('id' => 'Banner ID is required'));
                return;
            }
            
            $banner = $this->Banner_model->get_by_id($id);
            if (!$banner) {
                $this->_send_response(false, null, 'Banner not found', array('id' => 'Banner not found'));
                return;
            }
            
            $result = $this->Banner_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Banner deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete banner', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting banner', array('message' => $e->getMessage()));
        }
    }

    // ==================== OFFER BANNERS API ====================

    /**
     * GET /api/crud/offer-banners
     * Get all offer banners
     */
    public function offer_banners()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $banners = $this->Offer_banner_model->get_all($status);
            
            $banners = $this->_add_base_url_list($banners, array('image'));
            $this->_send_response(true, array('offer_banners' => $banners), 'Offer banners retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving offer banners', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/offer-banners/{id}
     * Get offer banner by ID
     */
    public function offer_banner($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Offer banner ID is required', array('id' => 'Offer banner ID is required'));
                return;
            }
            
            $banner = $this->Offer_banner_model->get_by_id($id);
            
            if (!$banner) {
                $this->_send_response(false, null, 'Offer banner not found', array('id' => 'Offer banner not found'));
                return;
            }
            
            $banner = $this->_add_base_url($banner, array('image'));
            $this->_send_response(true, $banner, 'Offer banner retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving offer banner', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/offer-banners/{id}
     * Update offer banner
     */
    public function update_offer_banner($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Offer banner ID is required', array('id' => 'Offer banner ID is required'));
                return;
            }
            
            $banner = $this->Offer_banner_model->get_by_id($id);
            if (!$banner) {
                $this->_send_response(false, null, 'Offer banner not found', array('id' => 'Offer banner not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Offer_banner_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Offer_banner_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Offer banner updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update offer banner', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating offer banner', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/offer-banners/{id}
     * Delete offer banner
     */
    public function delete_offer_banner($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Offer banner ID is required', array('id' => 'Offer banner ID is required'));
                return;
            }
            
            $banner = $this->Offer_banner_model->get_by_id($id);
            if (!$banner) {
                $this->_send_response(false, null, 'Offer banner not found', array('id' => 'Offer banner not found'));
                return;
            }
            
            $result = $this->Offer_banner_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Offer banner deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete offer banner', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting offer banner', array('message' => $e->getMessage()));
        }
    }

    // ==================== CONTACTS API ====================

    /**
     * GET /api/crud/contacts
     * Get all contacts
     */
    public function contacts()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            
            $contacts = $this->Contact_model->get_all($status);
            $total = count($contacts);
            
            if ($limit) {
                $contacts = array_slice($contacts, $offset, $limit);
            }
            
            $this->_send_response(true, array(
                'contacts' => $contacts,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ), 'Contacts retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving contacts', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/contacts/{id}
     * Get contact by ID
     */
    public function contact($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Contact ID is required', array('id' => 'Contact ID is required'));
                return;
            }
            
            $contact = $this->Contact_model->get_by_id($id);
            
            if (!$contact) {
                $this->_send_response(false, null, 'Contact not found', array('id' => 'Contact not found'));
                return;
            }
            
            $this->_send_response(true, $contact, 'Contact retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving contact', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/contacts/{id}
     * Update contact
     */
    public function update_contact($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Contact ID is required', array('id' => 'Contact ID is required'));
                return;
            }
            
            $contact = $this->Contact_model->get_by_id($id);
            if (!$contact) {
                $this->_send_response(false, null, 'Contact not found', array('id' => 'Contact not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Contact_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Contact_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Contact updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update contact', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating contact', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/contacts/{id}
     * Delete contact
     */
    public function delete_contact($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Contact ID is required', array('id' => 'Contact ID is required'));
                return;
            }
            
            $contact = $this->Contact_model->get_by_id($id);
            if (!$contact) {
                $this->_send_response(false, null, 'Contact not found', array('id' => 'Contact not found'));
                return;
            }
            
            $result = $this->Contact_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Contact deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete contact', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting contact', array('message' => $e->getMessage()));
        }
    }

    // ==================== ENQUIRIES API ====================

    /**
     * GET /api/crud/enquiries
     * Get all enquiries
     */
    public function enquiries()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            
            $enquiries = $this->Enquiry_model->get_all($status);
            $total = count($enquiries);
            
            if ($limit) {
                $enquiries = array_slice($enquiries, $offset, $limit);
            }
            
            $enquiries = $this->_add_base_url_list($enquiries, array('property_image'));
            $this->_send_response(true, array(
                'enquiries' => $enquiries,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ), 'Enquiries retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving enquiries', array('message' => $e->getMessage()));
        }
    }

    /**
     * POST /api/crud/enquiries/create
     * Create enquiry
     */
    public function create_enquiry()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $this->input->post();
            }

            if (empty($input['name']) || empty($input['email'])) {
                $this->_send_response(false, null, 'Validation failed', array('message' => 'name and email are required'));
                return;
            }

            $data = array(
                'property_id' => !empty($input['property_id']) ? (int)$input['property_id'] : null,
                'user_id'     => !empty($input['user_id'])     ? (int)$input['user_id']     : null,
                'name'        => trim($input['name']),
                'email'       => trim($input['email']),
                'phone'       => !empty($input['phone'])   ? trim($input['phone'])   : null,
                'message'     => !empty($input['message']) ? trim($input['message']) : null,
                'status'      => 'new'
            );

            $id = $this->Enquiry_model->create($data);
            if ($id) {
                $enquiry = $this->Enquiry_model->get_by_id($id);
                $this->_send_response(true, $enquiry, 'Enquiry submitted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to save enquiry', array('message' => 'Database error'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error submitting enquiry', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/enquiries/{id}
     * Get enquiry by ID
     */
    public function enquiry($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Enquiry ID is required', array('id' => 'Enquiry ID is required'));
                return;
            }
            
            $enquiry = $this->Enquiry_model->get_by_id($id);
            
            if (!$enquiry) {
                $this->_send_response(false, null, 'Enquiry not found', array('id' => 'Enquiry not found'));
                return;
            }
            
            $enquiry = $this->_add_base_url($enquiry, array('property_image'));
            $this->_send_response(true, $enquiry, 'Enquiry retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving enquiry', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/enquiries/{id}
     * Update enquiry
     */
    public function update_enquiry($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Enquiry ID is required', array('id' => 'Enquiry ID is required'));
                return;
            }
            
            $enquiry = $this->Enquiry_model->get_by_id($id);
            if (!$enquiry) {
                $this->_send_response(false, null, 'Enquiry not found', array('id' => 'Enquiry not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Enquiry_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Enquiry_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Enquiry updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update enquiry', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating enquiry', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/enquiries/{id}
     * Delete enquiry
     */
    public function delete_enquiry($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Enquiry ID is required', array('id' => 'Enquiry ID is required'));
                return;
            }
            
            $enquiry = $this->Enquiry_model->get_by_id($id);
            if (!$enquiry) {
                $this->_send_response(false, null, 'Enquiry not found', array('id' => 'Enquiry not found'));
                return;
            }
            
            $result = $this->Enquiry_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Enquiry deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete enquiry', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting enquiry', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/enquiries/user/{user_id}
     * Get enquiries by user ID
     * Query params: status (optional), limit (optional), offset (optional)
     */
    public function enquiries_by_user($user_id = null)
    {
        try {
            if (!$user_id) {
                $user_id = $this->uri->segment(5);
            }

            if (!$user_id) {
                $this->_send_response(false, null, 'user_id is required', array('user_id' => 'user_id is required'));
                return;
            }

            $status = $this->input->get('status') ?: null;
            $limit  = $this->input->get('limit')  ? (int)$this->input->get('limit')  : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            $enquiries = $this->Enquiry_model->get_by_userid($user_id, $status);
            $total     = count($enquiries);

            if ($limit) {
                $enquiries = array_slice($enquiries, $offset, $limit);
            }

            $formatted = array();
            foreach ($enquiries as $enq) {
                $formatted[] = array(
                    'id'             => (int)$enq->id,
                    'property_id'    => $enq->property_id ? (int)$enq->property_id : null,
                    'property_name'  => isset($enq->property_name)  ? $enq->property_name  : null,
                    'property_image' => !empty($enq->property_image) ? base_url($enq->property_image) : null,
                    'user_id'        => (int)$enq->user_id,
                    'name'           => $enq->name,
                    'email'          => $enq->email,
                    'phone'          => $enq->phone,
                    'message'        => $enq->message,
                    'status'         => $enq->status,
                    'created_at'     => $enq->created_at,
                );
            }

            $this->_send_response(true, array(
                'enquiries' => $formatted,
                'total'     => $total,
                'user_id'   => (int)$user_id,
                'limit'     => $limit,
                'offset'    => $offset,
            ), 'Enquiries retrieved successfully');

        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving enquiries', array('message' => $e->getMessage()));
        }
    }

    // ==================== USERS API ====================

    /**
     * GET /api/crud/users
     * Get all users with optional search and pagination
     * Query params: status (optional), search (optional), limit (optional), offset (optional)
     */
    public function users()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $search = $this->input->get('search') ?: null;
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            if ($search) {
                $users = $this->User_model->search($search);
            } else {
                $users = $this->User_model->get_all($status);
            }

            $total = count($users);

            if ($limit) {
                $users = array_slice($users, $offset, $limit);
            }

            $users = $this->_add_base_url_list($users, array('profilepic'));
            $this->_send_response(true, array(
                'users' => $users,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ), 'Users retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving users', array('message' => $e->getMessage()));
        }
    }

    /**
     * POST /api/crud/users/create
     * Create a new user
     */
    public function create_user()
    {
        try {
            $input = $this->_get_input();
            $errors = array();

            // Validation
            if (empty($input['fullname'])) {
                $errors['fullname'] = 'Full name is required';
            }

            if (empty($input['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!$this->User_model->validate_email($input['email'])) {
                $errors['email'] = 'Invalid email format';
            } elseif ($this->User_model->is_email_exists($input['email'])) {
                $errors['email'] = 'Email already exists';
            }

            if (empty($input['phonenumber'])) {
                $errors['phonenumber'] = 'Phone number is required';
            } elseif (!$this->User_model->validate_phone($input['phonenumber'])) {
                $errors['phonenumber'] = 'Invalid phone number (must be 10 digits)';
            } elseif ($this->User_model->is_phone_exists($input['phonenumber'], $input['countrycode'] ?? '+91')) {
                $errors['phonenumber'] = 'Phone number already exists';
            }

            if (!empty($errors)) {
                $this->_send_response(false, null, 'Validation failed', $errors);
                return;
            }

            // Prepare data
            $data = array(
                'id' => uniqid('user_'),
                'fullname' => $input['fullname'],
                'email' => $input['email'],
                'phonenumber' => $input['phonenumber'],
                'countrycode' => $input['countrycode'] ?? '+91',
                'city' => $input['city'] ?? null,
                'state' => $input['state'] ?? null,
                'pincode' => $input['pincode'] ?? null,
                'isactive' => $input['isactive'] ?? 'active',
                'logintype' => $input['logintype'] ?? 'manual',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if (isset($input['password']) && !empty($input['password'])) {
                $data['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }

            $result = $this->User_model->create($data);

            if ($result) {
                $user = $this->User_model->get_by_id($data['id']);
                $this->_send_response(true, $user, 'User created successfully');
            } else {
                $this->_send_response(false, null, 'Failed to create user', array('message' => 'Creation failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error creating user', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/users/{id}
     * Get user by ID
     */
    public function user($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'User ID is required', array('id' => 'User ID is required'));
                return;
            }
            
            $user = $this->User_model->get_by_id($id);
            
            if (!$user) {
                $this->_send_response(false, null, 'User not found', array('id' => 'User not found'));
                return;
            }
            
            $user = $this->_add_base_url($user, array('profilepic'));
            $this->_send_response(true, $user, 'User retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving user', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/users/{id}
     * Update user with validation
     */
    public function update_user($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }

            if (!$id) {
                $this->_send_response(false, null, 'User ID is required', array('id' => 'User ID is required'));
                return;
            }

            $user = $this->User_model->get_by_id($id);
            if (!$user) {
                $this->_send_response(false, null, 'User not found', array('id' => 'User not found'));
                return;
            }

            $input = $this->_get_input();
            unset($input['id']);

            // Don't allow password updates through this endpoint
            unset($input['password']);
            unset($input['password_hash']);

            // Validate email if it's being changed
            if (isset($input['email']) && $input['email'] !== $user->email) {
                if (!$this->User_model->validate_email($input['email'])) {
                    $this->_send_response(false, null, 'Invalid email format', array('email' => 'Invalid email format'));
                    return;
                }
                if ($this->User_model->is_email_exists($input['email'], $id)) {
                    $this->_send_response(false, null, 'Email already exists', array('email' => 'Email already exists'));
                    return;
                }
            }

            // Validate phone if it's being changed
            if (isset($input['phonenumber']) && $input['phonenumber'] !== $user->phonenumber) {
                if (!$this->User_model->validate_phone($input['phonenumber'])) {
                    $this->_send_response(false, null, 'Invalid phone number', array('phonenumber' => 'Invalid phone number (must be 10 digits)'));
                    return;
                }
                $country_code = isset($input['countrycode']) ? $input['countrycode'] : ($user->countrycode ?? '+91');
                if ($this->User_model->is_phone_exists_exclude($input['phonenumber'], $id, $country_code)) {
                    $this->_send_response(false, null, 'Phone number already exists', array('phonenumber' => 'Phone number already exists'));
                    return;
                }
            }

            $input['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->User_model->update($id, $input);

            if ($result) {
                $updated = $this->User_model->get_by_id($id);
                $this->_send_response(true, $updated, 'User updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update user', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating user', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/users/{id}
     * Delete user
     */
    public function delete_user($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'User ID is required', array('id' => 'User ID is required'));
                return;
            }
            
            $user = $this->User_model->get_by_id($id);
            if (!$user) {
                $this->_send_response(false, null, 'User not found', array('id' => 'User not found'));
                return;
            }
            
            $result = $this->User_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'User deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete user', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting user', array('message' => $e->getMessage()));
        }
    }

    /**
     * POST /api/crud/users/bulk-delete
     * Delete multiple users
     */
    public function bulk_delete_users()
    {
        try {
            $input = $this->_get_input();

            if (empty($input['ids']) || !is_array($input['ids'])) {
                $this->_send_response(false, null, 'User IDs array is required', array('ids' => 'IDs array is required'));
                return;
            }

            $result = $this->User_model->delete_bulk($input['ids']);

            if ($result) {
                $this->_send_response(true, null, count($input['ids']) . ' user(s) deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete users', array('message' => 'Deletion failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting users', array('message' => $e->getMessage()));
        }
    }

    /**
     * POST /api/crud/users/bulk-status
     * Update status for multiple users
     */
    public function bulk_update_status_users()
    {
        try {
            $input = $this->_get_input();

            if (empty($input['ids']) || !is_array($input['ids'])) {
                $this->_send_response(false, null, 'User IDs array is required', array('ids' => 'IDs array is required'));
                return;
            }

            if (empty($input['status'])) {
                $this->_send_response(false, null, 'Status is required', array('status' => 'Status is required'));
                return;
            }

            $result = $this->User_model->update_status_bulk($input['ids'], $input['status']);

            if ($result) {
                $this->_send_response(true, null, 'Status updated for ' . count($input['ids']) . ' user(s)');
            } else {
                $this->_send_response(false, null, 'Failed to update user status', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating user status', array('message' => $e->getMessage()));
        }
    }

    // ==================== NOTIFICATIONS API ====================

    /**
     * GET /api/crud/notifications
     * Get all notifications
     */
    public function notifications()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $notifications = $this->Notification_model->get_all($status);
            
            $notifications = $this->_add_base_url_list($notifications, array('image'));
            $this->_send_response(true, array('notifications' => $notifications), 'Notifications retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving notifications', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/notifications/{id}
     * Get notification by ID
     */
    public function notification($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Notification ID is required', array('id' => 'Notification ID is required'));
                return;
            }
            
            $notification = $this->Notification_model->get_by_id($id);
            
            if (!$notification) {
                $this->_send_response(false, null, 'Notification not found', array('id' => 'Notification not found'));
                return;
            }
            
            $notification = $this->_add_base_url($notification, array('image'));
            $this->_send_response(true, $notification, 'Notification retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving notification', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/notifications/{id}
     * Update notification
     */
    public function update_notification($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Notification ID is required', array('id' => 'Notification ID is required'));
                return;
            }
            
            $notification = $this->Notification_model->get_by_id($id);
            if (!$notification) {
                $this->_send_response(false, null, 'Notification not found', array('id' => 'Notification not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Notification_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Notification_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Notification updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update notification', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating notification', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/notifications/{id}
     * Delete notification
     */
    public function delete_notification($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Notification ID is required', array('id' => 'Notification ID is required'));
                return;
            }
            
            $notification = $this->Notification_model->get_by_id($id);
            if (!$notification) {
                $this->_send_response(false, null, 'Notification not found', array('id' => 'Notification not found'));
                return;
            }
            
            $result = $this->Notification_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Notification deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete notification', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting notification', array('message' => $e->getMessage()));
        }
    }

    // ==================== REELS VIDEOS API ====================

    /**
     * GET /api/crud/reels-videos
     * Get all reels videos
     */
    public function reels_videos()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $reels = $this->Reelsvideo_model->get_all($status);
            
            $reels = $this->_add_base_url_list($reels, array('thumbnail', 'video_url'));
            $this->_send_response(true, array('reels_videos' => $reels), 'Reels videos retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving reels videos', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/reels-videos/{id}
     * Get reel video by ID
     */
    public function reel_video($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Reel video ID is required', array('id' => 'Reel video ID is required'));
                return;
            }
            
            $reel = $this->Reelsvideo_model->get_by_id($id);
            
            if (!$reel) {
                $this->_send_response(false, null, 'Reel video not found', array('id' => 'Reel video not found'));
                return;
            }
            
            $reel = $this->_add_base_url($reel, array('thumbnail', 'video_url'));
            $this->_send_response(true, $reel, 'Reel video retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving reel video', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/reels-videos/{id}
     * Update reel video
     */
    public function update_reel_video($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Reel video ID is required', array('id' => 'Reel video ID is required'));
                return;
            }
            
            $reel = $this->Reelsvideo_model->get_by_id($id);
            if (!$reel) {
                $this->_send_response(false, null, 'Reel video not found', array('id' => 'Reel video not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Reelsvideo_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Reelsvideo_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Reel video updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update reel video', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating reel video', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/reels-videos/{id}
     * Delete reel video
     */
    public function delete_reel_video($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Reel video ID is required', array('id' => 'Reel video ID is required'));
                return;
            }
            
            $reel = $this->Reelsvideo_model->get_by_id($id);
            if (!$reel) {
                $this->_send_response(false, null, 'Reel video not found', array('id' => 'Reel video not found'));
                return;
            }
            
            $result = $this->Reelsvideo_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Reel video deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete reel video', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting reel video', array('message' => $e->getMessage()));
        }
    }

    // ==================== VIDEOS API ====================

    /**
     * GET /api/crud/videos
     * Get all videos
     */
    public function videos()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $videos = $this->Video_model->get_all($status);
            
            $videos = $this->_add_base_url_list($videos, array('thumbnail', 'video_url'));
            $this->_send_response(true, array('videos' => $videos), 'Videos retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving videos', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/videos/{id}
     * Get video by ID
     */
    public function video($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Video ID is required', array('id' => 'Video ID is required'));
                return;
            }
            
            $video = $this->Video_model->get_by_id($id);
            
            if (!$video) {
                $this->_send_response(false, null, 'Video not found', array('id' => 'Video not found'));
                return;
            }
            
            $video = $this->_add_base_url($video, array('thumbnail', 'video_url'));
            $this->_send_response(true, $video, 'Video retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving video', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/videos/{id}
     * Update video
     */
    public function update_video($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Video ID is required', array('id' => 'Video ID is required'));
                return;
            }
            
            $video = $this->Video_model->get_by_id($id);
            if (!$video) {
                $this->_send_response(false, null, 'Video not found', array('id' => 'Video not found'));
                return;
            }
            
            $input = $this->_get_input();
            unset($input['id']);
            
            $result = $this->Video_model->update($id, $input);
            
            if ($result) {
                $updated = $this->Video_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Video updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update video', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating video', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/videos/{id}
     * Delete video
     */
    public function delete_video($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            
            if (!$id) {
                $this->_send_response(false, null, 'Video ID is required', array('id' => 'Video ID is required'));
                return;
            }
            
            $video = $this->Video_model->get_by_id($id);
            if (!$video) {
                $this->_send_response(false, null, 'Video not found', array('id' => 'Video not found'));
                return;
            }
            
            $result = $this->Video_model->delete($id);
            
            if ($result) {
                $this->_send_response(true, null, 'Video deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete video', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting video', array('message' => $e->getMessage()));
        }
    }

    // ==================== REFERRALS API ====================

    /**
     * GET /api/crud/referrals
     * Get all referrals with optional status filtering
     */
    public function referrals()
    {
        try {
            $status = $this->input->get('status') ?: null;
            $referrer_id = $this->input->get('referrer_id') ?: null;
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            if ($referrer_id) {
                $referrals = $this->Referral_model->get_by_referrer($referrer_id, $status);
            } else {
                $referrals = $this->Referral_model->get_all($status);
            }

            $total = count($referrals);

            if ($limit) {
                $referrals = array_slice($referrals, $offset, $limit);
            }

            $this->_send_response(true, array(
                'referrals' => $referrals,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ), 'Referrals retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving referrals', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/referrals/{id}
     * Get referral by ID
     */
    public function referral($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }

            if (!$id) {
                $this->_send_response(false, null, 'Referral ID is required', array('id' => 'ID required'));
                return;
            }

            $referral = $this->Referral_model->get_by_id($id);

            if (!$referral) {
                $this->_send_response(false, null, 'Referral not found', array('id' => 'Not found'));
                return;
            }

            $this->_send_response(true, $referral, 'Referral retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving referral', array('message' => $e->getMessage()));
        }
    }

    /**
     * POST /api/crud/referrals/create
     * Create a new referral
     */
    public function create_referral()
    {
        try {
            $input = $this->_get_input();
            $errors = array();

            if (empty($input['referrer_id'])) {
                $errors['referrer_id'] = 'Referrer ID is required';
            }

            if (empty($input['referred_id'])) {
                $errors['referred_id'] = 'Referred user ID is required';
            }

            if ($input['referrer_id'] === $input['referred_id']) {
                $errors['referred_id'] = 'Referrer and referred cannot be the same';
            }

            if (!empty($errors)) {
                $this->_send_response(false, null, 'Validation failed', $errors);
                return;
            }

            $data = array(
                'id' => uniqid('ref_'),
                'referral_code' => $this->Referral_model->generate_code(),
                'referrer_id' => $input['referrer_id'],
                'referred_id' => $input['referred_id'],
                'status' => $input['status'] ?? 'pending',
                'reward_points' => $input['reward_points'] ?? 0,
                'reward_amount' => $input['reward_amount'] ?? 0.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($this->Referral_model->create($data)) {
                $referral = $this->Referral_model->get_by_id($data['id']);
                $this->_send_response(true, $referral, 'Referral created successfully');
            } else {
                $this->_send_response(false, null, 'Failed to create referral', array('message' => 'Creation failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error creating referral', array('message' => $e->getMessage()));
        }
    }

    /**
     * PUT /api/crud/referrals/{id}
     * Update referral
     */
    public function update_referral($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }

            if (!$id) {
                $this->_send_response(false, null, 'Referral ID is required', array('id' => 'ID required'));
                return;
            }

            $referral = $this->Referral_model->get_by_id($id);
            if (!$referral) {
                $this->_send_response(false, null, 'Referral not found', array('id' => 'Not found'));
                return;
            }

            $input = $this->_get_input();
            unset($input['id']);
            unset($input['referral_code']);

            $input['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->Referral_model->update($id, $input);

            if ($result) {
                $updated = $this->Referral_model->get_by_id($id);
                $this->_send_response(true, $updated, 'Referral updated successfully');
            } else {
                $this->_send_response(false, null, 'Failed to update referral', array('message' => 'Update failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error updating referral', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/referrals/{id}
     * Delete referral
     */
    public function delete_referral($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }

            if (!$id) {
                $this->_send_response(false, null, 'Referral ID is required', array('id' => 'ID required'));
                return;
            }

            $referral = $this->Referral_model->get_by_id($id);
            if (!$referral) {
                $this->_send_response(false, null, 'Referral not found', array('id' => 'Not found'));
                return;
            }

            if ($this->Referral_model->delete($id)) {
                $this->_send_response(true, null, 'Referral deleted successfully');
            } else {
                $this->_send_response(false, null, 'Failed to delete referral', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error deleting referral', array('message' => $e->getMessage()));
        }
    }

    // ==================== WISHLISTS API ====================

    /**
     * GET /api/crud/wishlists
     * Get all wishlist items
     */
    public function wishlists()
    {
        try {
            $user_id = $this->input->get('user_id') ?: null;
            $property_id = $this->input->get('property_id') ?: null;
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;

            if ($user_id) {
                $wishlists = $this->Wishlist_model->get_by_user($user_id, $limit, $offset);
            } elseif ($property_id) {
                $wishlists = $this->Wishlist_model->get_by_property($property_id);
            } else {
                $wishlists = $this->Wishlist_model->get_all();
                $total = count($wishlists);
                if ($limit) {
                    $wishlists = array_slice($wishlists, $offset, $limit);
                }
            }

            if (!$user_id && !$property_id) {
                $total = $this->Wishlist_model->count_all();
            } else {
                $total = count($wishlists);
            }

            $this->_send_response(true, array(
                'wishlists' => $wishlists,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ), 'Wishlists retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving wishlists', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/wishlists/{id}
     * Get wishlist by ID
     */
    public function wishlist($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }

            if (!$id) {
                $this->_send_response(false, null, 'Wishlist ID is required', array('id' => 'ID required'));
                return;
            }

            $wishlist = $this->Wishlist_model->get_by_id($id);

            if (!$wishlist) {
                $this->_send_response(false, null, 'Wishlist not found', array('id' => 'Not found'));
                return;
            }

            $this->_send_response(true, $wishlist, 'Wishlist retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving wishlist', array('message' => $e->getMessage()));
        }
    }

    /**
     * POST /api/crud/wishlists/create
     * Add item to wishlist
     */
    public function create_wishlist()
    {
        try {
            $input = $this->_get_input();
            $errors = array();

            if (empty($input['user_id'])) {
                $errors['user_id'] = 'User ID is required';
            }

            if (empty($input['property_id'])) {
                $errors['property_id'] = 'Property ID is required';
            }

            if (!empty($errors)) {
                $this->_send_response(false, null, 'Validation failed', $errors);
                return;
            }

            if ($this->Wishlist_model->is_wishlisted($input['user_id'], $input['property_id'])) {
                $this->_send_response(false, null, 'Item already in wishlist', array('property_id' => 'Already wishlisted'));
                return;
            }

            $data = array(
                'id' => uniqid('wish_'),
                'user_id' => $input['user_id'],
                'property_id' => $input['property_id'],
                'property_name' => $input['property_name'] ?? null,
                'property_image' => $input['property_image'] ?? null,
                'property_price' => $input['property_price'] ?? null,
                'property_location' => $input['property_location'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($this->Wishlist_model->create($data)) {
                $wishlist = $this->Wishlist_model->get_by_id($data['id']);
                $this->_send_response(true, $wishlist, 'Item added to wishlist successfully');
            } else {
                $this->_send_response(false, null, 'Failed to add to wishlist', array('message' => 'Creation failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error adding to wishlist', array('message' => $e->getMessage()));
        }
    }

    /**
     * DELETE /api/crud/wishlists/{id}
     * Remove item from wishlist
     */
    public function delete_wishlist($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }

            if (!$id) {
                $this->_send_response(false, null, 'Wishlist ID is required', array('id' => 'ID required'));
                return;
            }

            $wishlist = $this->Wishlist_model->get_by_id($id);
            if (!$wishlist) {
                $this->_send_response(false, null, 'Wishlist not found', array('id' => 'Not found'));
                return;
            }

            if ($this->Wishlist_model->delete($id)) {
                $this->_send_response(true, null, 'Item removed from wishlist successfully');
            } else {
                $this->_send_response(false, null, 'Failed to remove from wishlist', array('message' => 'Delete failed'));
            }
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error removing from wishlist', array('message' => $e->getMessage()));
        }
    }

    // ---------------------------------------------------------------
    // MOBILE BANNERS
    // ---------------------------------------------------------------

    /**
     * GET /api/crud/mobile_banners
     * Returns all active mobile banners (status=1)
     */
    public function mobile_banners()
    {
        try {
            $banners = $this->Mobile_banner_model->get_active();
            $banners = $this->_add_base_url_list($banners, array('path'));
            $this->_send_response(true, array('mobile_banners' => $banners), 'Mobile banners retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving mobile banners', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/mobile_banners/all
     * Returns all mobile banners regardless of status (admin use)
     */
    public function mobile_banners_all()
    {
        try {
            $banners = $this->Mobile_banner_model->get_all();
            $banners = $this->_add_base_url_list($banners, array('path'));
            $this->_send_response(true, array('mobile_banners' => $banners), 'Mobile banners retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving mobile banners', array('message' => $e->getMessage()));
        }
    }

    /**
     * GET /api/crud/mobile_banners/{id}
     * Returns single mobile banner by ID
     */
    public function mobile_banner($id = null)
    {
        try {
            if (!$id) {
                $id = $this->uri->segment(4);
            }
            if (!$id) {
                $this->_send_response(false, null, 'Banner ID is required');
                return;
            }

            $banner = $this->Mobile_banner_model->get_by_id($id);
            if (!$banner) {
                $this->_send_response(false, null, 'Mobile banner not found');
                return;
            }

            $banner = $this->_add_base_url($banner, array('path'));
            $this->_send_response(true, $banner, 'Mobile banner retrieved successfully');
        } catch (Exception $e) {
            $this->_send_response(false, null, 'Error retrieving mobile banner', array('message' => $e->getMessage()));
        }
    }
}
