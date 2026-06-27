<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_model');
        $this->load->model('Property_model');
        $this->load->model('Banner_model');
        $this->load->model('Offer_banner_model');
        $this->load->model('Enquiry_model');
        $this->load->model('Contact_model');
        $this->load->model('City_model');
        $this->load->model('Location_model');
        $this->load->model('Category_model');
        $this->load->model('Blog_model');
        $this->load->model('Notification_model');
        $this->load->model('Reelsvideo_model');
        $this->load->model('Video_model');
        $this->load->model('User_model');
        $this->load->model('Nb_delete_request_model');
        $this->load->model('Referral_model');
        $this->load->model('Wishlist_model');
        $this->load->model('Mobile_banner_model');
        $this->load->model('Housing_news_model');
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
    }
    
    private function check_login()
    {
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('admin/login');
        }
    }

    public function login()
    {
        if ($this->session->userdata('admin_logged_in')) {
            redirect('admin/dashboard');
        }

        if ($this->input->post()) {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        
            $admin = $this->Admin_model->login($username, $password);
            if ($admin) {
                $this->session->set_userdata(array(
                    'admin_logged_in' => true,
                    'admin_id' => $admin->id,
                    'admin_username' => $admin->username
                ));
                redirect('admin/dashboard');
        } else {
                $this->session->set_flashdata('error', 'Invalid username or password');
            }
        }

        $this->load->view('admin/login');
    }

    public function logout()
    {
        $this->session->unset_userdata('admin_logged_in');
        $this->session->unset_userdata('admin_id');
        $this->session->unset_userdata('admin_username');
        redirect('admin/login');
    }

    public function dashboard()
    {
        $this->check_login();
        
        $data['total_properties'] = $this->Property_model->count_all();
        $data['active_properties'] = $this->Property_model->count_all('active');
        $data['total_banners'] = count($this->Banner_model->get_all());
        $data['new_enquiries'] = $this->Enquiry_model->count_new();
        $data['new_contacts'] = $this->Contact_model->count_new();
        
        $this->load->view('admin/header');
        $this->load->view('admin/dashboard', $data);
        $this->load->view('admin/footer');
    }

    // Properties Management
    public function properties()
    {
        $this->check_login();
        $data['properties'] = $this->Property_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/properties/list', $data);
        $this->load->view('admin/footer');
    }

    public function property_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
                'category' => $this->input->post('category'),
                'location' => $this->input->post('location'),
                'city' => $this->input->post('city'),
                'price' => $this->input->post('price'),
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'video' => $this->input->post('video'),
                'type' => $this->input->post('type'),
                'status' => $this->input->post('status') ?: 'active',
                'is_latest' => $this->input->post('is_latest') ? 1 : 0,
                'is_featured' => $this->input->post('is_featured') ? 1 : 0,
                'tags_best_rate_localities' => $this->input->post('best_rated') ? 1 : 0,
                'tags_high_growth_localities' => $this->input->post('high_growth') ? 1 : 0
            );

            // Handle gallery upload - Multiple images
            $gallery_files = array();
            
            // Keep existing gallery images if editing
            $existing_gallery = $this->input->post('existing_gallery');
            if ($existing_gallery && is_array($existing_gallery)) {
                $gallery_files = $existing_gallery;
            }
            
            // Add new gallery images
            if (!empty($_FILES['gallery']['name'][0])) {
                $files = $_FILES['gallery'];
                $count = count($files['name']);
                
                // Ensure upload directory exists
                $upload_path = './assets/images/property/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $upload_errors = array();
                
                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] == 0) {
                        $_FILES['file']['name'] = $files['name'][$i];
                        $_FILES['file']['type'] = $files['type'][$i];
                        $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                        $_FILES['file']['error'] = $files['error'][$i];
                        $_FILES['file']['size'] = $files['size'][$i];
                        
                        $config['upload_path'] = $upload_path;
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                        $config['max_size'] = 5120; // 5MB
                        $config['encrypt_name'] = TRUE; // Prevent overwriting
                        
                        // Re-initialize upload library for each file
                        if ($i == 0) {
                            $this->load->library('upload', $config);
                        } else {
                            $this->upload->initialize($config);
                        }
                        
                        if ($this->upload->do_upload('file')) {
                            $upload_data = $this->upload->data();
                            $gallery_files[] = 'assets/images/property/' . $upload_data['file_name'];
                        } else {
                            $error = $this->upload->display_errors('', '');
                            $upload_errors[] = $files['name'][$i] . ': ' . $error;
                        }
                    } else {
                        $upload_errors[] = $files['name'][$i] . ': Upload error code ' . $files['error'][$i];
                    }
                }
                
                // Show errors if any
                if (!empty($upload_errors)) {
                    $this->session->set_flashdata('error', 'Some gallery images failed to upload: ' . implode(', ', $upload_errors));
                }
            }
            
            // Store gallery as JSON array in database
            if (!empty($gallery_files)) {
                $data['gallery'] = json_encode($gallery_files);
            } else {
                // Set empty array if no images
                $data['gallery'] = json_encode(array());
            }

            // Handle main image upload
            if (!empty($_FILES['main_image']['name'])) {
                $upload_path = './assets/images/property/';
                // Ensure upload directory exists
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['max_size'] = 5120; // 5MB
                $config['encrypt_name'] = TRUE;
        
        $this->load->library('upload', $config);
        
                if ($this->upload->do_upload('main_image')) {
                    $upload_data = $this->upload->data();
                    $data['main_image'] = 'assets/images/property/' . $upload_data['file_name'];
                } else {
                    $error = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Main image upload failed: ' . $error);
                    redirect('admin/property_create');
            return;
                }
            }

            // Handle location URL
            if ($this->input->post('location_url')) {
                $data['location_url'] = $this->input->post('location_url');
            }

            // Handle floorplan upload
            if (!empty($_FILES['floorplan']['name'])) {
                $upload_path = './assets/images/property/';
                // Ensure upload directory exists
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload('floorplan')) {
                    $upload_data = $this->upload->data();
                    $data['floorplan'] = 'assets/images/property/' . $upload_data['file_name'];
        } else {
                    $error = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Floorplan upload failed: ' . $error);
                }
            }

            // Handle nearby places with category, name and distance
            $nearby_categories = $this->input->post('nearby_category');
            $nearby_titles = $this->input->post('nearby_title');
            $nearby_distances = $this->input->post('nearby_distance');
            $nearby_places = array();

            if (is_array($nearby_categories)) {
                foreach ($nearby_categories as $index => $category) {
                    $category = trim($category);
                    $name     = isset($nearby_titles[$index])    ? trim($nearby_titles[$index])    : '';
                    $distance = isset($nearby_distances[$index]) ? trim($nearby_distances[$index]) : '';

                    if (!empty($category) || !empty($name)) {
                        $nearby_places[] = array(
                            'category' => $category,
                            'name'     => $name,
                            'distance' => $distance
                        );
                    }
                }
            }
            
            if (!empty($nearby_places)) {
                $data['nearby'] = json_encode($nearby_places);
        } else {
                $data['nearby'] = json_encode(array());
            }

            // Handle features
            $features = $this->input->post('features');
            $features_array = array();
            
            if ($features && is_array($features)) {
                foreach ($features as $feature) {
                    $feature = trim($feature);
                    if (!empty($feature)) {
                        $features_array[] = $feature;
                    }
                }
            }
            
            if (!empty($features_array)) {
                $data['features'] = json_encode($features_array);
            } else {
                $data['features'] = json_encode(array());
            }

            // Generate unique slug from property name
            $baseSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $this->input->post('name')), '-'));
            $baseSlug = $baseSlug ?: 'property';
            $slug = $baseSlug;
            $counter = 1;
            while ($this->Property_model->slug_exists($slug)) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;

            $this->Property_model->create($data);
            $this->session->set_flashdata('success', 'Property created successfully');
            redirect('admin/properties');
        }

        $data['cities'] = $this->City_model->get_all('active');
        $all_locations = $this->Location_model->get_all('active');
        $data['categories'] = $this->Category_model->get_all('active');
        
        // Group locations by city for easier filtering
        $data['locations_by_city'] = array();
        foreach ($all_locations as $location) {
            if (!isset($data['locations_by_city'][$location->city_id])) {
                $data['locations_by_city'][$location->city_id] = array();
            }
            $data['locations_by_city'][$location->city_id][] = $location;
        }
        $data['all_locations'] = $all_locations;
        
        $this->load->view('admin/header');
        $this->load->view('admin/properties/create', $data);
        $this->load->view('admin/footer');
    }

    public function property_edit($id)
    {
        $this->check_login();
        $property = $this->Property_model->get_by_id($id);
        $data['property'] = $property;
        
        if (!$property) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
                'category' => $this->input->post('category'),
                'location' => $this->input->post('location'),
                'city' => $this->input->post('city'),
                'price' => $this->input->post('price'),
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'video' => $this->input->post('video'),
                'type' => $this->input->post('type'),
                'status' => $this->input->post('status') ?: 'active',
                'is_latest' => $this->input->post('is_latest') ? 1 : 0,
                'is_featured' => $this->input->post('is_featured') ? 1 : 0,
                'tags_best_rate_localities' => $this->input->post('best_rated') ? 1 : 0,
                'tags_high_growth_localities' => $this->input->post('high_growth') ? 1 : 0
            );

            // Handle gallery upload - Multiple images (Edit mode)
            $gallery_files = array();
            
            // Keep existing gallery images that were not removed
            $existing_gallery = $this->input->post('existing_gallery');
            if ($existing_gallery && is_array($existing_gallery)) {
                $gallery_files = $existing_gallery;
        } else {
                // If no existing gallery posted (all removed), set empty array
                $gallery_files = array();
            }
            
            // Add new gallery images
            if (!empty($_FILES['gallery']['name'][0])) {
                $files = $_FILES['gallery'];
                $count = count($files['name']);
                
                // Ensure upload directory exists
                $upload_path = './assets/images/property/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $upload_errors = array();
                
                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] == 0) {
                            $_FILES['file']['name'] = $files['name'][$i];
                            $_FILES['file']['type'] = $files['type'][$i];
                            $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                            $_FILES['file']['error'] = $files['error'][$i];
                            $_FILES['file']['size'] = $files['size'][$i];
                            
                        $config['upload_path'] = $upload_path;
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                        $config['max_size'] = 5120; // 5MB
                        $config['encrypt_name'] = TRUE; // Prevent overwriting
                        
                        // Re-initialize upload library for each file
                        if ($i == 0) {
                            $this->load->library('upload', $config);
                        } else {
                            $this->upload->initialize($config);
                        }
                            
                            if ($this->upload->do_upload('file')) {
                            $upload_data = $this->upload->data();
                            $gallery_files[] = 'assets/images/property/' . $upload_data['file_name'];
                        } else {
                            $error = $this->upload->display_errors('', '');
                            $upload_errors[] = $files['name'][$i] . ': ' . $error;
                        }
                    } else {
                        $upload_errors[] = $files['name'][$i] . ': Upload error code ' . $files['error'][$i];
                    }
                }
                
                // Show errors if any
                if (!empty($upload_errors)) {
                    $this->session->set_flashdata('error', 'Some gallery images failed to upload: ' . implode(', ', $upload_errors));
                }
            }
            
            // Store gallery as JSON array in database (even if empty, to clear gallery)
            $update_data['gallery'] = !empty($gallery_files) ? json_encode($gallery_files) : json_encode(array());

            // Handle main image upload
            if (!empty($_FILES['main_image']['name'])) {
                $upload_path = './assets/images/property/';
                // Ensure upload directory exists
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 5120; // 5MB
                $config['encrypt_name'] = TRUE;
                
                // Re-initialize upload library (might already be loaded from gallery upload)
                if (!isset($this->upload)) {
                    $this->load->library('upload', $config);
        } else {
                    $this->upload->initialize($config);
                }
                
                if ($this->upload->do_upload('main_image')) {
                    $upload_data = $this->upload->data();
                    $update_data['main_image'] = 'assets/images/property/' . $upload_data['file_name'];
                    
                    // Delete old main image if exists
                    if (!empty($property->main_image) && file_exists('./' . $property->main_image)) {
                        @unlink('./' . $property->main_image);
                    }
                } else {
                    $error = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Main image upload failed: ' . $error);
                }
            }

            // Handle location URL
            if ($this->input->post('location_url')) {
                $update_data['location_url'] = $this->input->post('location_url');
                } else {
                // If empty, set to null or empty string
                $update_data['location_url'] = '';
            }

            // Handle floorplan upload
            if (!empty($_FILES['floorplan']['name'])) {
                $upload_path = './assets/images/property/';
                // Ensure upload directory exists
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                // Re-initialize upload library (might already be loaded)
                if (!isset($this->upload)) {
                    $this->load->library('upload', $config);
                } else {
                        $this->upload->initialize($config);
                }
                
                if ($this->upload->do_upload('floorplan')) {
                    $upload_data = $this->upload->data();
                    $update_data['floorplan'] = 'assets/images/property/' . $upload_data['file_name'];
                    
                    // Delete old floorplan if exists
                    if (!empty($property->floorplan) && file_exists('./' . $property->floorplan)) {
                        @unlink('./' . $property->floorplan);
                    }
        } else {
                    $error = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Floorplan upload failed: ' . $error);
                }
            }

            // Handle nearby places with category, name and distance
            $nearby_categories = $this->input->post('nearby_category');
            $nearby_titles = $this->input->post('nearby_title');
            $nearby_distances = $this->input->post('nearby_distance');
            $nearby_places = array();

            if (is_array($nearby_categories)) {
                foreach ($nearby_categories as $index => $category) {
                    $category = trim($category);
                    $name     = isset($nearby_titles[$index])    ? trim($nearby_titles[$index])    : '';
                    $distance = isset($nearby_distances[$index]) ? trim($nearby_distances[$index]) : '';

                    if (!empty($category) || !empty($name)) {
                        $nearby_places[] = array(
                            'category' => $category,
                            'name'     => $name,
                            'distance' => $distance
                        );
                    }
                }
            }
            
            if (!empty($nearby_places)) {
                $update_data['nearby'] = json_encode($nearby_places);
            } else {
                $update_data['nearby'] = json_encode(array());
            }

            // Handle features
            $features = $this->input->post('features');
            $features_array = array();
            
            if ($features && is_array($features)) {
                foreach ($features as $feature) {
                    $feature = trim($feature);
                    if (!empty($feature)) {
                        $features_array[] = $feature;
                    }
                }
            }
            
            if (!empty($features_array)) {
                $update_data['features'] = json_encode($features_array);
        } else {
                $update_data['features'] = json_encode(array());
            }

            // Regenerate slug if name changed or slug is missing
            $newName = $this->input->post('name');
            if (empty($property->slug) || $newName !== $property->name) {
                $baseSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $newName), '-'));
                $baseSlug = $baseSlug ?: 'property';
                $slug = $baseSlug;
                $counter = 1;
                while ($this->Property_model->slug_exists($slug, $id)) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $update_data['slug'] = $slug;
            }

            $this->Property_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Property updated successfully');
            redirect('admin/properties');
        }

        $data['cities'] = $this->City_model->get_all('active');
        $all_locations = $this->Location_model->get_all('active');
        $data['categories'] = $this->Category_model->get_all('active');
        $data['all_locations'] = $all_locations;
        
        $this->load->view('admin/header');
        $this->load->view('admin/properties/edit', $data);
        $this->load->view('admin/footer');
    }

    public function property_delete($id)
    {
        $this->check_login();
        $this->Property_model->delete($id);
        $this->session->set_flashdata('success', 'Property deleted successfully');
        redirect('admin/properties');
    }

    // Banners Management
    public function banners()
    {
        $this->check_login();
        // Get only active banners for admin table
        $data['banners'] = $this->Banner_model->get_all_for_admin();
        $this->load->view('admin/header');
        $this->load->view('admin/banners/list', $data);
        $this->load->view('admin/footer');
    }

    public function banner_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
                'status' => $this->input->post('status') ?: 'inactive'
            );

            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/banner/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['max_size'] = 5120; // 5MB
                $config['encrypt_name'] = TRUE;
                $this->load->library('upload', $config);
        
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        
                if ($this->upload->do_upload('image')) {
                    $data['image'] = 'assets/images/banner/' . $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('admin/banner_create');
                    return;
                }
            } else {
                $this->session->set_flashdata('error', 'Banner image is required');
                redirect('admin/banner_create');
            return;
        }
        
            $this->Banner_model->create($data);
            $this->session->set_flashdata('success', 'Banner created successfully');
            redirect('admin/banners');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/banners/create');
        $this->load->view('admin/footer');
    }

    public function banner_edit($id)
    {
        $this->check_login();
        $data['banner'] = $this->Banner_model->get_by_id($id);
        
        if (!$data['banner']) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
                'status' => $this->input->post('status') ?: 'inactive'
            );

            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/banner/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = 2048;
        $this->load->library('upload', $config);
        
                if ($this->upload->do_upload('image')) {
                    $update_data['image'] = 'assets/images/banner/' . $this->upload->data('file_name');
                }
            }

            $this->Banner_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Banner updated successfully');
            redirect('admin/banners');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/banners/edit', $data);
        $this->load->view('admin/footer');
    }

    public function banner_delete($id)
    {
        $this->check_login();
        $this->Banner_model->delete($id);
        $this->session->set_flashdata('success', 'Banner deleted successfully');
        redirect('admin/banners');
    }

    public function banner_toggle($id)
    {
        $this->check_login();
        $banner = $this->Banner_model->get_by_id($id);
        if ($banner) {
            $new_status = $banner->status == 'active' ? 'inactive' : 'active';
            $this->Banner_model->update($id, array('status' => $new_status));
            $message = $new_status == 'active' ? 'Banner activated successfully' : 'Banner deactivated and hidden from table';
            $this->session->set_flashdata('success', $message);
        }
        redirect('admin/banners');
    }

    // Mobile Banners Management
    public function mobile_banners()
    {
        $this->check_login();
        $data['mobile_banners'] = $this->Mobile_banner_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/mobile_banners/list', $data);
        $this->load->view('admin/footer');
    }

    public function mobile_banner_create()
    {
        $this->check_login();

        if ($this->input->post()) {
            if (empty($_FILES['path']['name'])) {
                $this->session->set_flashdata('error', 'Please select an image to upload.');
                redirect('admin/mobile_banner_create');
                return;
            }

            $upload_dir = './assets/images/mobile_banner/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $config['upload_path']   = $upload_dir;
            $config['allowed_types'] = '*';
            $config['encrypt_name']  = TRUE;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('path')) {
                $file_path = 'assets/images/mobile_banner/' . $this->upload->data('file_name');
                $this->Mobile_banner_model->create(array(
                    'path'   => $file_path,
                    'status' => (int)$this->input->post('status')
                ));
                $this->session->set_flashdata('success', 'Mobile banner created successfully.');
                redirect('admin/mobile_banners');
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('admin/mobile_banner_create');
            }
            return;
        }

        $this->load->view('admin/header');
        $this->load->view('admin/mobile_banners/create');
        $this->load->view('admin/footer');
    }

    public function mobile_banner_edit($id)
    {
        $this->check_login();
        $data['mobile_banner'] = $this->Mobile_banner_model->get_by_id($id);

        if (!$data['mobile_banner']) {
            show_404();
        }

        if ($this->input->post()) {
            $update = array('status' => (int)$this->input->post('status'));

            if (!empty($_FILES['path']['name'])) {
                $upload_dir = './assets/images/mobile_banner/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $config['upload_path']   = $upload_dir;
                $config['allowed_types'] = '*';
                $config['encrypt_name']  = TRUE;
                $this->load->library('upload', $config);

                if ($this->upload->do_upload('path')) {
                    $update['path'] = 'assets/images/mobile_banner/' . $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('admin/mobile_banner_edit/' . $id);
                    return;
                }
            }

            $this->Mobile_banner_model->update($id, $update);
            $this->session->set_flashdata('success', 'Mobile banner updated successfully.');
            redirect('admin/mobile_banners');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/mobile_banners/edit', $data);
        $this->load->view('admin/footer');
    }

    public function mobile_banner_delete($id)
    {
        $this->check_login();
        $this->Mobile_banner_model->delete($id);
        $this->session->set_flashdata('success', 'Mobile banner deleted successfully.');
        redirect('admin/mobile_banners');
    }

    public function mobile_banner_toggle($id)
    {
        $this->check_login();
        $banner = $this->Mobile_banner_model->get_by_id($id);
        if ($banner) {
            $new_status = $banner->status ? 0 : 1;
            $this->Mobile_banner_model->update($id, array('status' => $new_status));
            $this->session->set_flashdata('success', 'Status updated successfully.');
        }
        redirect('admin/mobile_banners');
    }

    // Enquiries Management
    public function enquiries()
    {
        $this->check_login();
        $data['enquiries'] = $this->Enquiry_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/enquiries/list', $data);
        $this->load->view('admin/footer');
    }

    public function enquiry_view($id)
    {
        $this->check_login();
        $data['enquiry'] = $this->Enquiry_model->get_by_id($id);
        
        if (!$data['enquiry']) {
            show_404();
        }

        // Mark as read
        if ($data['enquiry']->status == 'new') {
            $this->Enquiry_model->update($id, array('status' => 'read'));
        }

        $this->load->view('admin/header');
        $this->load->view('admin/enquiries/view', $data);
        $this->load->view('admin/footer');
    }

    public function enquiry_delete($id)
    {
        $this->check_login();
        $this->Enquiry_model->delete($id);
        $this->session->set_flashdata('success', 'Enquiry deleted successfully');
        redirect('admin/enquiries');
    }

    // Contacts Management
    public function contacts()
    {
        $this->check_login();
        $data['contacts'] = $this->Contact_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/contacts/list', $data);
        $this->load->view('admin/footer');
    }

    public function contact_view($id)
    {
        $this->check_login();
        $data['contact'] = $this->Contact_model->get_by_id($id);
        
        if (!$data['contact']) {
            show_404();
        }

        // Mark as read
        if ($data['contact']->status == 'new') {
            $this->Contact_model->update($id, array('status' => 'read'));
        }

        $this->load->view('admin/header');
        $this->load->view('admin/contacts/view', $data);
        $this->load->view('admin/footer');
    }

    public function contact_delete($id)
    {
        $this->check_login();
        $this->Contact_model->delete($id);
        $this->session->set_flashdata('success', 'Contact deleted successfully');
        redirect('admin/contacts');
    }

    // Cities Management
    public function cities()
    {
        $this->check_login();
        $data['cities'] = $this->City_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/cities/list', $data);
        $this->load->view('admin/footer');
    }

    public function city_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
                'name' => $this->input->post('name'),
                'status' => $this->input->post('status') ?: 'active'
            );

            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/city/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/city_create');
            return;
                    }
        }
        
        $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $data['image'] = 'assets/images/city/' . $this->upload->data('file_name');
                            } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/city_create');
                    return;
                }
            }

            $this->City_model->create($data);
            $this->session->set_flashdata('success', 'City created successfully');
            redirect('admin/cities');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/cities/create');
        $this->load->view('admin/footer');
    }

    public function city_edit($id)
    {
        $this->check_login();
        $data['city'] = $this->City_model->get_by_id($id);
        
        if (!$data['city']) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
                'name' => $this->input->post('name'),
                'status' => $this->input->post('status') ?: 'active'
            );

            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/city/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/city_edit/' . $id);
            return;
                    }
        }
        
        $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    // Delete old image if exists
                    if (!empty($data['city']->image) && file_exists('./' . $data['city']->image)) {
                        @unlink('./' . $data['city']->image);
                    }
                    $update_data['image'] = 'assets/images/city/' . $this->upload->data('file_name');
                } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/city_edit/' . $id);
                    return;
                }
            }

            $this->City_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'City updated successfully');
            redirect('admin/cities');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/cities/edit', $data);
        $this->load->view('admin/footer');
    }

    public function city_delete($id)
    {
        $this->check_login();
        $this->City_model->delete($id);
        $this->session->set_flashdata('success', 'City deleted successfully');
        redirect('admin/cities');
    }

    // Locations Management
    public function locations()
    {
        $this->check_login();
        $data['locations'] = $this->Location_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/locations/list', $data);
        $this->load->view('admin/footer');
    }
    
    public function location_update_order()
    {
        $this->check_login();

        // Clean any prior output and set JSON header
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        $ordersJson = $this->input->post('orders');

        if (empty($ordersJson)) {
            exit(json_encode(array('success' => false, 'message' => 'No order data received')));
        }

        $orders = json_decode($ordersJson, true);

        if (!is_array($orders) || empty($orders)) {
            exit(json_encode(array('success' => false, 'message' => 'Invalid order data')));
        }

        // Ensure order column exists
        $fields = $this->db->list_fields('locations');
        if (!in_array('order', $fields)) {
            $this->db->query("ALTER TABLE `locations` ADD COLUMN `order` INT(11) NOT NULL DEFAULT 0 AFTER `status`");
        }

        $success = true;
        foreach ($orders as $id => $order) {
            $id    = (int) $id;
            $order = (int) $order;
            if ($id <= 0) continue;
            $this->db->where('id', $id);
            if (!$this->db->update('locations', array('order' => $order))) {
                $success = false;
            }
        }

        if ($success) {
            exit(json_encode(array('success' => true, 'message' => 'Order updated successfully')));
        } else {
            $err = $this->db->error();
            exit(json_encode(array('success' => false, 'message' => 'Update failed: ' . ($err['message'] ?? 'Unknown error'))));
        }
    }

    public function location_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            // Validate required fields
            $city_id = $this->input->post('city_id');
            $name = trim($this->input->post('name'));
            
            if (empty($city_id) || empty($name)) {
                $this->session->set_flashdata('error', 'City and Location Name are required fields.');
                redirect('admin/location_create');
            return;
        }
        
            $data = array(
                'city_id' => $city_id,
                'name' => $name,
                'status' => $this->input->post('status') ?: 'active'
            );
            
            // Set order value if column exists
            $fields = $this->db->list_fields('locations');
            if (in_array('order', $fields)) {
                $this->db->select_max('order');
                $maxOrder = $this->db->get('locations')->row();
                $data['order'] = ($maxOrder && $maxOrder->order !== null) ? (int)$maxOrder->order + 1 : 1;
            }

            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/location/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/location_create');
            return;
                    }
        }
        
        $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $data['image'] = 'assets/images/location/' . $this->upload->data('file_name');
            } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/location_create');
                    return;
                }
            }

            // Insert into database
            $insert_id = $this->Location_model->create($data);
            if ($insert_id) {
                $this->session->set_flashdata('success', 'Location created successfully');
                redirect('admin/locations');
        } else {
                $this->session->set_flashdata('error', 'Failed to create location. Please try again.');
                redirect('admin/location_create');
            }
                return;
            }
            
        $data['cities'] = $this->City_model->get_all('active');
        $this->load->view('admin/header');
        $this->load->view('admin/locations/create', $data);
        $this->load->view('admin/footer');
    }

    public function location_edit($id)
    {
        $this->check_login();
        $data['location'] = $this->Location_model->get_by_id($id);
        
        if (!$data['location']) {
            show_404();
                return;
            }
            
        if ($this->input->post()) {
            // Validate required fields
            $city_id = $this->input->post('city_id');
            $name = trim($this->input->post('name'));
            
            if (empty($city_id) || empty($name)) {
                $this->session->set_flashdata('error', 'City and Location Name are required fields.');
                redirect('admin/location_edit/' . $id);
                return;
            }

            $update_data = array(
                'city_id' => $city_id,
                'name' => $name,
                'status' => $this->input->post('status') ?: 'active'
            );
            
            // Preserve order value if it exists
            if ($this->input->post('order') !== null) {
                $update_data['order'] = (int)$this->input->post('order');
            }

            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/location/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
                if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/location_edit/' . $id);
            return;
        }
                }
                
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    // Delete old image if exists
                    if (!empty($data['location']->image) && file_exists('./' . $data['location']->image)) {
                        @unlink('./' . $data['location']->image);
                    }
                    $update_data['image'] = 'assets/images/location/' . $this->upload->data('file_name');
                } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/location_edit/' . $id);
            return;
        }
            } else {
                // Keep existing image if no new image uploaded
                if (!empty($this->input->post('existing_image'))) {
                    $update_data['image'] = $this->input->post('existing_image');
                }
            }

            // Update database
            $result = $this->Location_model->update($id, $update_data);
            if ($result) {
                $this->session->set_flashdata('success', 'Location updated successfully');
                redirect('admin/locations');
            } else {
                $this->session->set_flashdata('error', 'Failed to update location. Please try again.');
                redirect('admin/location_edit/' . $id);
            }
            return;
        }
        
        $data['cities'] = $this->City_model->get_all('active');
        $this->load->view('admin/header');
        $this->load->view('admin/locations/edit', $data);
        $this->load->view('admin/footer');
    }

    public function location_delete($id)
    {
        $this->check_login();
        $this->Location_model->delete($id);
        $this->session->set_flashdata('success', 'Location deleted successfully');
        redirect('admin/locations');
    }

    // Categories Management
    public function categories()
    {
        $this->check_login();
        $data['categories'] = $this->Category_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/categories/list', $data);
        $this->load->view('admin/footer');
    }

    public function category_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $name = trim($this->input->post('category_name'));
            
            if (empty($name)) {
                $this->session->set_flashdata('error', 'Category Name is required.');
                redirect('admin/category_create');
            return;
        }
        
            $data = array(
                'category_name' => $name,
                'status' => $this->input->post('status') ?: 'active'
            );

            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/category/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/category_create');
            return;
        }
        }
        
        $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $data['image'] = 'assets/images/category/' . $this->upload->data('file_name');
                } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/category_create');
            return;
        }
            }

            $insert_id = $this->Category_model->create($data);
            if ($insert_id) {
                $this->session->set_flashdata('success', 'Category created successfully');
                redirect('admin/categories');
        } else {
                $this->session->set_flashdata('error', 'Failed to create category. Please try again.');
                redirect('admin/category_create');
            }
            return;
        }

        $this->load->view('admin/header');
        $this->load->view('admin/categories/create');
        $this->load->view('admin/footer');
    }

    public function category_edit($id)
    {
        $this->check_login();
        $data['category'] = $this->Category_model->get_by_id($id);
        
        if (!$data['category']) {
            show_404();
            return;
        }
        
        if ($this->input->post()) {
            $name = trim($this->input->post('category_name'));
            
            if (empty($name)) {
                $this->session->set_flashdata('error', 'Category Name is required.');
                redirect('admin/category_edit/' . $id);
            return;
        }
        
            $update_data = array(
                'category_name' => $name,
                'status' => $this->input->post('status') ?: 'active'
            );

            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/category/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 2048; // 2MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/category_edit/' . $id);
            return;
        }
        }
        
        $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    // Delete old image if exists
                    if (!empty($data['category']->image) && file_exists('./' . $data['category']->image)) {
                        @unlink('./' . $data['category']->image);
                    }
                    $update_data['image'] = 'assets/images/category/' . $this->upload->data('file_name');
                } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/category_edit/' . $id);
            return;
        }
            } else {
                // Keep existing image if no new image uploaded
                if (!empty($this->input->post('existing_image'))) {
                    $update_data['image'] = $this->input->post('existing_image');
                }
            }

            $result = $this->Category_model->update($id, $update_data);
            if ($result) {
                $this->session->set_flashdata('success', 'Category updated successfully');
                redirect('admin/categories');
            } else {
                $this->session->set_flashdata('error', 'Failed to update category. Please try again.');
                redirect('admin/category_edit/' . $id);
            }
            return;
        }

        $this->load->view('admin/header');
        $this->load->view('admin/categories/edit', $data);
        $this->load->view('admin/footer');
    }

    public function category_delete($id)
    {
        $this->check_login();
        $this->Category_model->delete($id);
        $this->session->set_flashdata('success', 'Category deleted successfully');
        redirect('admin/categories');
    }

    // Blog Management
    public function blogs()
    {
        $this->check_login();
        $data['blogs'] = $this->Blog_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/blogs/list', $data);
        $this->load->view('admin/footer');
    }

    public function blog_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'short_notes' => $this->input->post('short_notes'),
                'author' => $this->input->post('author'),
                'date' => $this->input->post('date') ?: date('Y-m-d'),
                'status' => $this->input->post('status') ?: 'active'
            );

            // Handle gallery upload - Multiple images
            $gallery_files = array();
            if (!empty($_FILES['gallery']['name'][0])) {
                $files = $_FILES['gallery'];
                $count = count($files['name']);
                
                // Ensure upload directory exists
                $upload_path = './assets/images/blog/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] == 0) {
                            $_FILES['file']['name'] = $files['name'][$i];
                            $_FILES['file']['type'] = $files['type'][$i];
                            $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                            $_FILES['file']['error'] = $files['error'][$i];
                            $_FILES['file']['size'] = $files['size'][$i];
                            
                        $config['upload_path'] = $upload_path;
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                        $config['max_size'] = 5120; // 5MB
                        $config['encrypt_name'] = TRUE;
                        
                        $this->load->library('upload', $config);
                            if ($this->upload->do_upload('file')) {
                            $upload_data = $this->upload->data();
                            $gallery_files[] = 'assets/images/blog/' . $upload_data['file_name'];
                        }
                    }
                }
            }
            
            if (!empty($gallery_files)) {
                $data['gallery'] = json_encode($gallery_files);
                            } else {
                $data['gallery'] = json_encode(array());
            }

            $this->Blog_model->create($data);
            $this->session->set_flashdata('success', 'Blog created successfully');
            redirect('admin/blogs');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/blogs/create');
        $this->load->view('admin/footer');
    }

    public function blog_edit($id)
    {
        $this->check_login();
        $data['blog'] = $this->Blog_model->get_by_id($id);
        
        if (!$data['blog']) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'short_notes' => $this->input->post('short_notes'),
                'author' => $this->input->post('author'),
                'date' => $this->input->post('date') ?: date('Y-m-d'),
                'status' => $this->input->post('status') ?: 'active'
            );

            // Handle gallery upload - Multiple images (Edit mode)
            $gallery_files = array();
            
            // Keep existing gallery images that were not removed
            $existing_gallery = $this->input->post('existing_gallery');
            if ($existing_gallery && is_array($existing_gallery)) {
                $gallery_files = $existing_gallery;
        } else {
                // If no existing gallery posted (all removed), set empty array
                $gallery_files = array();
            }
            
            // Add new gallery images
            if (!empty($_FILES['gallery']['name'][0])) {
                $files = $_FILES['gallery'];
                $count = count($files['name']);
                
                // Ensure upload directory exists
                $upload_path = './assets/images/blog/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $upload_errors = array();
                
                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] == 0) {
                        $_FILES['file']['name'] = $files['name'][$i];
                        $_FILES['file']['type'] = $files['type'][$i];
                        $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                        $_FILES['file']['error'] = $files['error'][$i];
                        $_FILES['file']['size'] = $files['size'][$i];
                        
                        $config['upload_path'] = $upload_path;
                        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                        $config['max_size'] = 5120; // 5MB
                        $config['encrypt_name'] = TRUE;
                        
                        // Re-initialize upload library for each file
                        if ($i == 0) {
                            $this->load->library('upload', $config);
        } else {
                        $this->upload->initialize($config);
                        }
                        
                        if ($this->upload->do_upload('file')) {
                            $upload_data = $this->upload->data();
                            $gallery_files[] = 'assets/images/blog/' . $upload_data['file_name'];
                        } else {
                            $error = $this->upload->display_errors('', '');
                            $upload_errors[] = $files['name'][$i] . ': ' . $error;
                        }
                        } else {
                        $upload_errors[] = $files['name'][$i] . ': Upload error code ' . $files['error'][$i];
                    }
                }
                
                // Show errors if any
                if (!empty($upload_errors)) {
                    $this->session->set_flashdata('error', 'Some gallery images failed to upload: ' . implode(', ', $upload_errors));
                }
            }
            
            // Store gallery as JSON array in database (even if empty, to clear gallery)
            $update_data['gallery'] = !empty($gallery_files) ? json_encode($gallery_files) : json_encode(array());

            $this->Blog_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Blog updated successfully');
            redirect('admin/blogs');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/blogs/edit', $data);
        $this->load->view('admin/footer');
    }

    public function blog_delete($id)
    {
        $this->check_login();
        $this->Blog_model->delete($id);
        $this->session->set_flashdata('success', 'Blog deleted successfully');
        redirect('admin/blogs');
    }

    // Housing News Management
    public function housing_news()
    {
        $this->check_login();
        $data['housing_news'] = $this->Housing_news_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/housing_news/list', $data);
        $this->load->view('admin/footer');
    }

    public function housing_news_create()
    {
        $this->check_login();

        if ($this->input->post()) {
            $data = array(
                'title' => trim((string) $this->input->post('title')),
                'subtitle' => $this->input->post('subtitle') ?: null,
                'description' => $this->input->post('description') ?: null,
                'authorName' => $this->input->post('authorName') ?: null,
                'category' => $this->input->post('category') ?: 'market',
            );

            if ($data['title'] === '') {
                $this->session->set_flashdata('error', 'Title is required');
                redirect('admin/housing_news_create');
                return;
            }

            if (!in_array($data['category'], array('market', 'tips', 'legal'), true)) {
                $data['category'] = 'market';
            }

            $uploaded_images = $this->_upload_housing_news_images('multi_images');
            if ($uploaded_images === false) {
                redirect('admin/housing_news_create');
                return;
            }
            $data['multiImages'] = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

            $this->Housing_news_model->create($data);
            $this->session->set_flashdata('success', 'Housing news created successfully');
            redirect('admin/housing_news');
            return;
        }

        $this->load->view('admin/header');
        $this->load->view('admin/housing_news/create');
        $this->load->view('admin/footer');
    }

    public function housing_news_edit($id)
    {
        $this->check_login();
        $data['item'] = $this->Housing_news_model->get_by_id($id);
        if (!$data['item']) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
                'title' => trim((string) $this->input->post('title')),
                'subtitle' => $this->input->post('subtitle') ?: null,
                'description' => $this->input->post('description') ?: null,
                'authorName' => $this->input->post('authorName') ?: null,
                'category' => $this->input->post('category') ?: 'market',
            );

            if ($update_data['title'] === '') {
                $this->session->set_flashdata('error', 'Title is required');
                redirect('admin/housing_news_edit/' . (int) $id);
                return;
            }

            if (!in_array($update_data['category'], array('market', 'tips', 'legal'), true)) {
                $update_data['category'] = 'market';
            }

            $existing_images = isset($data['item']->multiImages) ? $this->_decode_housing_news_images($data['item']->multiImages) : array();
            $removed_images = $this->input->post('remove_existing_images');
            if (is_array($removed_images) && !empty($removed_images)) {
                $removed_images = array_map('trim', $removed_images);
                $existing_images = array_values(array_diff($existing_images, $removed_images));
            }
            $uploaded_images = $this->_upload_housing_news_images('multi_images');
            if ($uploaded_images === false) {
                redirect('admin/housing_news_edit/' . (int) $id);
                return;
            }
            $all_images = array_values(array_unique(array_merge($existing_images, $uploaded_images)));
            $update_data['multiImages'] = !empty($all_images) ? json_encode($all_images) : null;

            $this->Housing_news_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Housing news updated successfully');
            redirect('admin/housing_news');
            return;
        }

        $this->load->view('admin/header');
        $this->load->view('admin/housing_news/edit', $data);
        $this->load->view('admin/footer');
    }

    public function housing_news_delete($id)
    {
        $this->check_login();
        $this->Housing_news_model->delete($id);
        $this->session->set_flashdata('success', 'Housing news deleted successfully');
        redirect('admin/housing_news');
    }

    private function _decode_housing_news_images($raw)
    {
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return array();
        }
        $clean = array();
        foreach ($decoded as $img) {
            if (!is_string($img)) {
                continue;
            }
            $img = trim($img);
            if ($img === '') {
                continue;
            }
            $clean[] = $img;
        }
        return array_values(array_unique($clean));
    }

    private function _upload_housing_news_images($input_name)
    {
        if (empty($_FILES[$input_name]['name']) || !is_array($_FILES[$input_name]['name'])) {
            return array();
        }

        $upload_path = './assets/images/housing_news/';
        if (!is_dir($upload_path) && !@mkdir($upload_path, 0755, true)) {
            $this->session->set_flashdata('error', 'Could not create housing news upload directory');
            return false;
        }

        $saved = array();
        $files = $_FILES[$input_name];
        $count = count($files['name']);
        $this->load->library('upload');

        for ($i = 0; $i < $count; $i++) {
            if (empty($files['name'][$i])) {
                continue;
            }
            $_FILES['single_housing_image'] = array(
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            );

            $config = array(
                'upload_path' => $upload_path,
                'allowed_types' => 'gif|jpg|png|jpeg|webp',
                'max_size' => 5120,
                'encrypt_name' => true
            );
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('single_housing_image')) {
                $err = strip_tags($this->upload->display_errors('', ''));
                $this->session->set_flashdata('error', 'Image upload failed: ' . $err);
                return false;
            }
            $u = $this->upload->data();
            $saved[] = 'assets/images/housing_news/' . $u['file_name'];
        }

        return $saved;
    }

    // ==================== OFFER BANNERS MANAGEMENT ====================

    public function offer_banners()
    {
        $this->check_login();
        $data['offer_banners'] = $this->Offer_banner_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/offer_banners/list', $data);
        $this->load->view('admin/footer');
    }

    public function offer_banner_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
                'title' => $this->input->post('title') ?: null,
                'link' => $this->input->post('link') ?: null,
                'status' => $this->input->post('status') ?: 'inactive'
            );

            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/offer_banner/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 5120; // 5MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
                if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/offer_banner_create');
            return;
        }
                }
                
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $data['image'] = 'assets/images/offer_banner/' . $this->upload->data('file_name');
            } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/offer_banner_create');
            return;
            }
        } else {
                $this->session->set_flashdata('error', 'Offer banner image is required');
                redirect('admin/offer_banner_create');
                return;
            }
            
            $insert_id = $this->Offer_banner_model->create($data);
            if ($insert_id) {
                $this->session->set_flashdata('success', 'Offer banner created successfully');
                redirect('admin/offer_banners');
            } else {
                $this->session->set_flashdata('error', 'Failed to create offer banner. Please try again.');
                redirect('admin/offer_banner_create');
            }
            return;
        }
            
        $this->load->view('admin/header');
        $this->load->view('admin/offer_banners/create');
        $this->load->view('admin/footer');
    }

    public function offer_banner_edit($id)
    {
        $this->check_login();
        $data['offer_banner'] = $this->Offer_banner_model->get_by_id($id);
        
        if (!$data['offer_banner']) {
            show_404();
            return;
        }
        
        if ($this->input->post()) {
            $update_data = array(
                'title' => $this->input->post('title') ?: null,
                'link' => $this->input->post('link') ?: null,
                'status' => $this->input->post('status') ?: 'inactive'
            );

            if (!empty($_FILES['image']['name'])) {
                $config['upload_path'] = './assets/images/offer_banner/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
                $config['max_size'] = 5120; // 5MB
                $config['encrypt_name'] = TRUE;
                
                // Create directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
                    if (!mkdir($config['upload_path'], 0777, true)) {
                        $this->session->set_flashdata('error', 'Failed to create upload directory.');
                        redirect('admin/offer_banner_edit/' . $id);
            return;
        }
        }
        
        $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    // Delete old image if exists
                    if (!empty($data['offer_banner']->image) && file_exists('./' . $data['offer_banner']->image)) {
                        @unlink('./' . $data['offer_banner']->image);
                    }
                    $update_data['image'] = 'assets/images/offer_banner/' . $this->upload->data('file_name');
                } else {
                    $error_msg = $this->upload->display_errors('', '');
                    $this->session->set_flashdata('error', 'Image upload failed: ' . $error_msg);
                    redirect('admin/offer_banner_edit/' . $id);
            return;
        }
            } else {
                // Keep existing image if no new image uploaded
                if (!empty($this->input->post('existing_image'))) {
                    $update_data['image'] = $this->input->post('existing_image');
                }
            }

            $result = $this->Offer_banner_model->update($id, $update_data);
            if ($result) {
                $this->session->set_flashdata('success', 'Offer banner updated successfully');
                redirect('admin/offer_banners');
            } else {
                $this->session->set_flashdata('error', 'Failed to update offer banner. Please try again.');
                redirect('admin/offer_banner_edit/' . $id);
            }
            return;
        }
        
        $this->load->view('admin/header');
        $this->load->view('admin/offer_banners/edit', $data);
        $this->load->view('admin/footer');
    }

    public function offer_banner_delete($id)
    {
        $this->check_login();
        $offer_banner = $this->Offer_banner_model->get_by_id($id);
        
        if ($offer_banner) {
            // Delete image file if exists
            if (!empty($offer_banner->image) && file_exists('./' . $offer_banner->image)) {
                @unlink('./' . $offer_banner->image);
            }
            
            $this->Offer_banner_model->delete($id);
            $this->session->set_flashdata('success', 'Offer banner deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Offer banner not found');
        }
        
        redirect('admin/offer_banners');
    }

    /**
     * Optional image (field `image`) and video (`video`) uploads for admin notifications.
     *
     * @param object|null $previous Existing row when editing (old files removed on replace)
     * @return string|null Error message or null on success
     */
    private function _notification_merge_uploads(&$data, $previous = null)
    {
        if (!empty($_FILES['image']['name'])) {
            $config = array(
                'upload_path' => './assets/images/notifications/',
                'allowed_types' => 'gif|jpg|png|jpeg|webp',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('image')) {
                return strip_tags($this->upload->display_errors('', ''));
            }
            if ($previous && !empty($previous->image) && file_exists(FCPATH . $previous->image)) {
                @unlink(FCPATH . $previous->image);
            }
            $data['image'] = 'assets/images/notifications/' . $this->upload->data('file_name');
        }

        if (!empty($_FILES['video']['name']) && $this->db->field_exists('video', 'notifications')) {
            $vconfig = array(
                'upload_path' => './assets/videos/notifications/',
                'allowed_types' => 'mp4|webm|mov',
                'max_size' => 30720,
                'encrypt_name' => true,
            );
            if (!is_dir($vconfig['upload_path'])) {
                mkdir($vconfig['upload_path'], 0777, true);
            }
            if (!isset($this->upload)) {
                $this->load->library('upload', $vconfig);
            } else {
                $this->upload->initialize($vconfig);
            }
            if (!$this->upload->do_upload('video')) {
                return strip_tags($this->upload->display_errors('', ''));
            }
            if ($previous && !empty($previous->video) && file_exists(FCPATH . $previous->video)) {
                @unlink(FCPATH . $previous->video);
            }
            $data['video'] = 'assets/videos/notifications/' . $this->upload->data('file_name');
        }

        return null;
    }

    // Notifications Management
    public function notifications()
    {
        $this->check_login();
        $data['notifications'] = $this->Notification_model->get_all_for_admin();
        $this->load->view('admin/header');
        $this->load->view('admin/notifications/list', $data);
        $this->load->view('admin/footer');
    }

    public function notification_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => $this->input->post('status') ?: 'active'
            );

            $err = $this->_notification_merge_uploads($data, null);
            if ($err !== null) {
                $this->session->set_flashdata('error', $err);
                redirect('admin/notification_create');
                return;
            }

            $notification_id = (int) $this->Notification_model->create($data);
            nb_send_fcm_notification($notification_id, $data);

            $this->session->set_flashdata('success', 'Notification sent and saved successfully');
            redirect('admin/notifications');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/notifications/create');
        $this->load->view('admin/footer');
    }

    public function notification_edit($id = null)
    {
        $this->check_login();
        if (!$id) {
            redirect('admin/notifications');
            return;
        }
        $data['notification'] = $this->Notification_model->get_by_id($id);
        
        if (!$data['notification']) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => $this->input->post('status') ?: 'active'
            );

            $err = $this->_notification_merge_uploads($update_data, $data['notification']);
            if ($err !== null) {
                $this->session->set_flashdata('error', $err);
                redirect('admin/notification_edit/' . $id);
                return;
            }
            if (empty($_FILES['image']['name']) && !empty($this->input->post('existing_image'))) {
                $update_data['image'] = $this->input->post('existing_image');
            }
            if ($this->db->field_exists('video', 'notifications')) {
                if (empty($_FILES['video']['name']) && !empty($this->input->post('existing_video'))) {
                    $update_data['video'] = $this->input->post('existing_video');
                }
            }

            $this->Notification_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Notification updated successfully');
            redirect('admin/notifications');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/notifications/edit', $data);
        $this->load->view('admin/footer');
    }

    public function notification_delete($id)
    {
        $this->check_login();
        $notification = $this->Notification_model->get_by_id($id);
        
        if ($notification) {
            // Delete image file if exists
            if (!empty($notification->image) && file_exists(FCPATH . $notification->image)) {
                @unlink(FCPATH . $notification->image);
            }
            if (!empty($notification->video) && file_exists(FCPATH . $notification->video)) {
                @unlink(FCPATH . $notification->video);
            }

            $this->Notification_model->delete($id);
            $this->session->set_flashdata('success', 'Notification deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Notification not found');
        }
        
        redirect('admin/notifications');
    }

    public function notification_toggle($id = null)
    {
        $this->check_login();
        if (!$id) {
            redirect('admin/notifications');
            return;
        }
        $notification = $this->Notification_model->get_by_id($id);
        
        if ($notification) {
            $new_status = $notification->status == 'active' ? 'inactive' : 'active';
            $this->Notification_model->update($id, array('status' => $new_status));
            $this->session->set_flashdata('success', 'Notification status updated successfully');
        } else {
            $this->session->set_flashdata('error', 'Notification not found');
        }
        
        redirect('admin/notifications');
    }

    // ==================== REELS VIDEOS MANAGEMENT ====================
    
    public function reels()
    {
        $this->check_login();
        $data['reels'] = $this->Reelsvideo_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/reels/list', $data);
        $this->load->view('admin/footer');
    }

    private function extractYouTubeVideoId($url)
    {
        if (empty($url)) return null;
        
        $patterns = array(
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
            '/youtube\.com\/.*[?&]v=([^&\n?#]+)/',
            '/youtube\.com\/shorts\/([^&\n?#]+)/'
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    
    private function getYouTubeThumbnail($videoId)
    {
        return 'https://img.youtube.com/vi/' . $videoId . '/maxresdefault.jpg';
    }

    public function reel_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
                'title' => $this->input->post('title'),
                'status' => $this->input->post('status') ?: 'active'
            );
            // index_no will be set automatically by model

            $videoUrl = trim($this->input->post('videoUrl'));
            
            // Require YouTube URL
            if (empty($videoUrl)) {
                $this->session->set_flashdata('error', 'Please provide a YouTube video URL');
                redirect('admin/reel_create');
            return;
        }
            
            // Validate YouTube URL
            $videoId = $this->extractYouTubeVideoId($videoUrl);
            if (!$videoId) {
                $this->session->set_flashdata('error', 'Please provide a valid YouTube video URL');
                redirect('admin/reel_create');
            return;
        }
            
            // Use provided YouTube URL
            $data['videoUrl'] = $videoUrl;
            
            // Auto-generate thumbnail from YouTube
            $thumbnailUrl = $this->getYouTubeThumbnail($videoId);
            $data['thumbnail'] = $thumbnailUrl;
            
            // If thumbnail is provided via hidden input (from JS), use it
            $thumbnailInput = trim($this->input->post('thumbnail'));
            if (!empty($thumbnailInput)) {
                $data['thumbnail'] = $thumbnailInput;
            }

            $this->Reelsvideo_model->create($data);
            $this->session->set_flashdata('success', 'Reel video created successfully');
            redirect('admin/reels');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/reels/create');
        $this->load->view('admin/footer');
    }

    public function reel_edit($id)
    {
        $this->check_login();
        $data['reel'] = $this->Reelsvideo_model->get_by_id($id);
        
        if (!$data['reel']) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
            'title' => $this->input->post('title'),
                'status' => $this->input->post('status') ?: 'active'
            );
            // Don't update index_no here - it's managed by drag and drop

            $videoUrl = trim($this->input->post('videoUrl'));
            
            // Require YouTube URL
            if (empty($videoUrl)) {
                $this->session->set_flashdata('error', 'Please provide a YouTube video URL');
                redirect('admin/reel_edit/' . $id);
            return;
        }
            
            // Validate YouTube URL
            $videoId = $this->extractYouTubeVideoId($videoUrl);
            if (!$videoId) {
                $this->session->set_flashdata('error', 'Please provide a valid YouTube video URL');
                redirect('admin/reel_edit/' . $id);
            return;
        }
            
            // Use provided YouTube URL
            $update_data['videoUrl'] = $videoUrl;
            
            // Auto-generate thumbnail from YouTube
            $thumbnailUrl = $this->getYouTubeThumbnail($videoId);
            $update_data['thumbnail'] = $thumbnailUrl;
            
            // If thumbnail is provided via hidden input (from JS), use it
            $thumbnailInput = trim($this->input->post('thumbnail'));
            if (!empty($thumbnailInput)) {
                $update_data['thumbnail'] = $thumbnailInput;
            }

            $this->Reelsvideo_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Reel video updated successfully');
            redirect('admin/reels');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/reels/edit', $data);
        $this->load->view('admin/footer');
    }

    public function reel_delete($id)
    {
        $this->check_login();
        $reel = $this->Reelsvideo_model->get_by_id($id);
        
        if ($reel) {
            // Delete video file (only if it's a local file, not YouTube URL)
            if (!empty($reel->videoUrl) && !filter_var($reel->videoUrl, FILTER_VALIDATE_URL) && file_exists('./' . $reel->videoUrl)) {
                @unlink('./' . $reel->videoUrl);
            }
            // Delete thumbnail (only if it's a local file, not YouTube thumbnail URL)
            if (!empty($reel->thumbnail) && !filter_var($reel->thumbnail, FILTER_VALIDATE_URL) && file_exists('./' . $reel->thumbnail)) {
                @unlink('./' . $reel->thumbnail);
            }
        }
        
        $this->Reelsvideo_model->delete($id);
        $this->session->set_flashdata('success', 'Reel video deleted successfully');
        redirect('admin/reels');
    }

    public function reel_update_order()
    {
        $this->check_login();
        
        header('Content-Type: application/json');
        
        try {
            $ordersJson = $this->input->post('orders');
            if (empty($ordersJson)) {
                echo json_encode(array('success' => false, 'message' => 'Invalid order data: No data received'));
            return;
        }
            
            $orders = json_decode($ordersJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(array('success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()));
            return;
            }
            
            if (empty($orders) || !is_array($orders)) {
                echo json_encode(array('success' => false, 'message' => 'Invalid order data format: Expected array'));
            return;
        }
            
            $result = $this->Reelsvideo_model->update_orders($orders);
            if ($result) {
                echo json_encode(array('success' => true, 'message' => 'Order updated successfully'));
            } else {
                $db_error = $this->db->error();
                $error_msg = 'Failed to update order';
                if (!empty($db_error['message'])) {
                    $error_msg .= ': ' . $db_error['message'];
                }
                log_message('error', 'Reel order update failed: ' . print_r($db_error, true));
                echo json_encode(array('success' => false, 'message' => $error_msg));
            }
        } catch (Exception $e) {
            log_message('error', 'Reel order update exception: ' . $e->getMessage());
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }

    // ==================== VIDEOS MANAGEMENT ====================
    
    public function videos()
    {
        $this->check_login();
        $data['videos'] = $this->Video_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/videos/list', $data);
        $this->load->view('admin/footer');
    }

    public function video_create()
    {
        $this->check_login();
        
        if ($this->input->post()) {
            $data = array(
            'title' => $this->input->post('title'),
                'status' => $this->input->post('status') ?: 'active'
            );
            // index_no will be set automatically by model

            $videoUrl = trim($this->input->post('videoUrl'));
            
            // Require YouTube URL
            if (empty($videoUrl)) {
                $this->session->set_flashdata('error', 'Please provide a YouTube video URL');
                redirect('admin/video_create');
                return;
            }
            
            // Validate YouTube URL
            $videoId = $this->extractYouTubeVideoId($videoUrl);
            if (!$videoId) {
                $this->session->set_flashdata('error', 'Please provide a valid YouTube video URL');
                redirect('admin/video_create');
                return;
            }
            
            // Use provided YouTube URL
            $data['videoUrl'] = $videoUrl;
            
            // Auto-generate thumbnail from YouTube
            $thumbnailUrl = $this->getYouTubeThumbnail($videoId);
            $data['thumbnail'] = $thumbnailUrl;
            
            // If thumbnail is provided via hidden input (from JS), use it
            $thumbnailInput = trim($this->input->post('thumbnail'));
            if (!empty($thumbnailInput)) {
                $data['thumbnail'] = $thumbnailInput;
            }

            $this->Video_model->create($data);
            $this->session->set_flashdata('success', 'Video created successfully');
            redirect('admin/videos');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/videos/create');
        $this->load->view('admin/footer');
    }

    public function video_edit($id)
    {
        $this->check_login();
        $data['video'] = $this->Video_model->get_by_id($id);
        
        if (!$data['video']) {
            show_404();
        }

        if ($this->input->post()) {
            $update_data = array(
                'title' => $this->input->post('title'),
                'status' => $this->input->post('status') ?: 'active'
            );
            // Don't update index_no here - it's managed by drag and drop

            $videoUrl = trim($this->input->post('videoUrl'));
            
            // Require YouTube URL
            if (empty($videoUrl)) {
                $this->session->set_flashdata('error', 'Please provide a YouTube video URL');
                redirect('admin/video_edit/' . $id);
            return;
        }
            
            // Validate YouTube URL
            $videoId = $this->extractYouTubeVideoId($videoUrl);
            if (!$videoId) {
                $this->session->set_flashdata('error', 'Please provide a valid YouTube video URL');
                redirect('admin/video_edit/' . $id);
            return;
        }
            
            // Use provided YouTube URL
            $update_data['videoUrl'] = $videoUrl;
            
            // Auto-generate thumbnail from YouTube
            $thumbnailUrl = $this->getYouTubeThumbnail($videoId);
            $update_data['thumbnail'] = $thumbnailUrl;
            
            // If thumbnail is provided via hidden input (from JS), use it
            $thumbnailInput = trim($this->input->post('thumbnail'));
            if (!empty($thumbnailInput)) {
                $update_data['thumbnail'] = $thumbnailInput;
            }

            $this->Video_model->update($id, $update_data);
            $this->session->set_flashdata('success', 'Video updated successfully');
            redirect('admin/videos');
        }

        $this->load->view('admin/header');
        $this->load->view('admin/videos/edit', $data);
        $this->load->view('admin/footer');
    }

    public function video_delete($id)
    {
        $this->check_login();
        $video = $this->Video_model->get_by_id($id);
        
        if ($video) {
            // Delete video file (only if it's a local file, not YouTube URL)
            if (!empty($video->videoUrl) && !filter_var($video->videoUrl, FILTER_VALIDATE_URL) && file_exists('./' . $video->videoUrl)) {
                @unlink('./' . $video->videoUrl);
            }
            // Delete thumbnail (only if it's a local file, not YouTube thumbnail URL)
            if (!empty($video->thumbnail) && !filter_var($video->thumbnail, FILTER_VALIDATE_URL) && file_exists('./' . $video->thumbnail)) {
                @unlink('./' . $video->thumbnail);
            }
        }
        
        $this->Video_model->delete($id);
        $this->session->set_flashdata('success', 'Video deleted successfully');
        redirect('admin/videos');
    }

    public function video_update_order()
    {
        $this->check_login();
        
        header('Content-Type: application/json');
        
        try {
            $ordersJson = $this->input->post('orders');
            if (empty($ordersJson)) {
                echo json_encode(array('success' => false, 'message' => 'Invalid order data: No data received'));
                return;
            }
            
            $orders = json_decode($ordersJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(array('success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()));
                return;
            }
            
            if (empty($orders) || !is_array($orders)) {
                echo json_encode(array('success' => false, 'message' => 'Invalid order data format: Expected array'));
                return;
            }
            
            $result = $this->Video_model->update_orders($orders);
            if ($result) {
                echo json_encode(array('success' => true, 'message' => 'Order updated successfully'));
            } else {
                $db_error = $this->db->error();
                $error_msg = 'Failed to update order';
                if (!empty($db_error['message'])) {
                    $error_msg .= ': ' . $db_error['message'];
                }
                log_message('error', 'Video order update failed: ' . print_r($db_error, true));
                echo json_encode(array('success' => false, 'message' => $error_msg));
            }
        } catch (Exception $e) {
            log_message('error', 'Video order update exception: ' . $e->getMessage());
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }

    // ==================== USERS MANAGEMENT ====================

    public function users()
    {
        $this->check_login();

        $search = $this->input->get('search') ?: null;
        $status = $this->input->get('status') ?: null;

        if ($search) {
            $users = $this->User_model->search($search);
        } else {
            $users = $this->User_model->get_all($status);
        }

        $data['users'] = $users;
        $data['search'] = $search;
        $data['status'] = $status;

        $this->load->view('admin/header');
        $this->load->view('admin/users/list', $data);
        $this->load->view('admin/footer');
    }

    public function user_create()
    {
        $this->check_login();

        if ($this->input->post()) {
            $fullname = $this->input->post('fullname');
            $email = $this->input->post('email');
            $phonenumber = $this->input->post('phonenumber');
            $countrycode = $this->input->post('countrycode', '+91');

            // Validation
            if (empty($fullname)) {
                $this->session->set_flashdata('error', 'Full name is required');
                redirect('admin/user_create');
                return;
            }

            if (empty($email) || !$this->User_model->validate_email($email)) {
                $this->session->set_flashdata('error', 'Invalid email address');
                redirect('admin/user_create');
                return;
            }

            if ($this->User_model->is_email_exists($email)) {
                $this->session->set_flashdata('error', 'Email already exists');
                redirect('admin/user_create');
                return;
            }

            if (empty($phonenumber) || !$this->User_model->validate_phone($phonenumber)) {
                $this->session->set_flashdata('error', 'Invalid phone number (must be 10 digits)');
                redirect('admin/user_create');
                return;
            }

            if ($this->User_model->is_phone_exists($phonenumber, $countrycode)) {
                $this->session->set_flashdata('error', 'Phone number already exists');
                redirect('admin/user_create');
                return;
            }

            $data = array(
                'id' => uniqid('user_'),
                'fullname' => $fullname,
                'email' => $email,
                'phonenumber' => $phonenumber,
                'countrycode' => $countrycode,
                'city' => $this->input->post('city') ?: null,
                'state' => $this->input->post('state') ?: null,
                'pincode' => $this->input->post('pincode') ?: null,
                'isactive' => $this->input->post('isactive', 'active'),
                'logintype' => $this->input->post('logintype', 'manual'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($this->input->post('password')) {
                $data['password'] = password_hash($this->input->post('password'), PASSWORD_BCRYPT);
            }

            if ($this->User_model->create($data)) {
                $this->session->set_flashdata('success', 'User created successfully');
                redirect('admin/users');
            } else {
                $this->session->set_flashdata('error', 'Failed to create user');
                redirect('admin/user_create');
            }
        }

        $this->load->view('admin/header');
        $this->load->view('admin/users/create');
        $this->load->view('admin/footer');
    }

    public function user_edit($id)
    {
        $this->check_login();

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            $this->session->set_flashdata('error', 'User not found');
            redirect('admin/users');
            return;
        }

        if ($this->input->post()) {
            $fullname = $this->input->post('fullname');
            $email = $this->input->post('email');
            $phonenumber = $this->input->post('phonenumber');
            $countrycode = $this->input->post('countrycode', '+91');

            // Validation
            if (empty($fullname)) {
                $this->session->set_flashdata('error', 'Full name is required');
                redirect('admin/user_edit/' . $id);
                return;
            }

            if (empty($email) || !$this->User_model->validate_email($email)) {
                $this->session->set_flashdata('error', 'Invalid email address');
                redirect('admin/user_edit/' . $id);
                return;
            }

            if ($email !== $user->email && $this->User_model->is_email_exists($email, $id)) {
                $this->session->set_flashdata('error', 'Email already exists');
                redirect('admin/user_edit/' . $id);
                return;
            }

            if (empty($phonenumber) || !$this->User_model->validate_phone($phonenumber)) {
                $this->session->set_flashdata('error', 'Invalid phone number (must be 10 digits)');
                redirect('admin/user_edit/' . $id);
                return;
            }

            if ($phonenumber !== $user->phonenumber && $this->User_model->is_phone_exists_exclude($phonenumber, $id, $countrycode)) {
                $this->session->set_flashdata('error', 'Phone number already exists');
                redirect('admin/user_edit/' . $id);
                return;
            }

            $data = array(
                'fullname' => $fullname,
                'email' => $email,
                'phonenumber' => $phonenumber,
                'countrycode' => $countrycode,
                'city' => $this->input->post('city') ?: null,
                'state' => $this->input->post('state') ?: null,
                'pincode' => $this->input->post('pincode') ?: null,
                'isactive' => $this->input->post('isactive', 'active'),
                'logintype' => $this->input->post('logintype', 'manual'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($this->User_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'User updated successfully');
                redirect('admin/users');
            } else {
                $this->session->set_flashdata('error', 'Failed to update user');
                redirect('admin/user_edit/' . $id);
            }
        }

        $data['user'] = $user;
        $this->load->view('admin/header');
        $this->load->view('admin/users/edit', $data);
        $this->load->view('admin/footer');
    }

    public function user_delete($id)
    {
        $this->check_login();

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            $this->session->set_flashdata('error', 'User not found');
            redirect('admin/users');
            return;
        }

        if ($this->User_model->delete($id)) {
            $this->session->set_flashdata('success', 'User deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete user');
        }

        redirect('admin/users');
    }

    public function bulk_delete_users()
    {
        $this->check_login();
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['ids']) || !is_array($input['ids'])) {
                echo json_encode(array('success' => false, 'message' => 'No users selected'));
                return;
            }

            if ($this->User_model->delete_bulk($input['ids'])) {
                echo json_encode(array('success' => true, 'message' => count($input['ids']) . ' user(s) deleted successfully'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to delete users'));
            }
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }

    public function bulk_update_status_users()
    {
        $this->check_login();
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['ids']) || !is_array($input['ids'])) {
                echo json_encode(array('success' => false, 'message' => 'No users selected'));
                return;
            }

            if (empty($input['status'])) {
                echo json_encode(array('success' => false, 'message' => 'Status is required'));
                return;
            }

            if ($this->User_model->update_status_bulk($input['ids'], $input['status'])) {
                echo json_encode(array('success' => true, 'message' => 'Status updated for ' . count($input['ids']) . ' user(s)'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to update status'));
            }
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }

    // ==================== DELETE REQUESTS MANAGEMENT ====================

    public function delete_requests()
    {
        $this->check_login();
        $data['requests'] = $this->Nb_delete_request_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/delete_requests/list', $data);
        $this->load->view('admin/footer');
    }

    public function delete_request_status($id)
    {
        $this->check_login();
        $status = $this->input->post('status');
        $allowed = array('pending', 'reviewed', 'done');
        if (!in_array($status, $allowed)) {
            $this->session->set_flashdata('error', 'Invalid status');
            redirect('admin/delete_requests');
            return;
        }
        $this->Nb_delete_request_model->update_status($id, $status);
        $this->session->set_flashdata('success', 'Status updated successfully');
        redirect('admin/delete_requests');
    }

    // ==================== REFERRALS MANAGEMENT ====================

    public function referrals()
    {
        $this->check_login();

        $status = $this->input->get('status') ?: null;
        $referrals = $this->Referral_model->get_all($status);

        $data['referrals'] = $referrals;
        $data['status'] = $status;

        $this->load->view('admin/header');
        $this->load->view('admin/referrals/list', $data);
        $this->load->view('admin/footer');
    }

    public function referral_create()
    {
        $this->check_login();

        if ($this->input->post()) {
            $referrer_id = $this->input->post('referrer_id');
            $referred_id = $this->input->post('referred_id');

            if (empty($referrer_id) || empty($referred_id)) {
                $this->session->set_flashdata('error', 'Both referrer and referred user are required');
                redirect('admin/referral_create');
                return;
            }

            if ($referrer_id === $referred_id) {
                $this->session->set_flashdata('error', 'Referrer and referred cannot be the same user');
                redirect('admin/referral_create');
                return;
            }

            $data = array(
                'id' => uniqid('ref_'),
                'referral_code' => $this->Referral_model->generate_code(),
                'referrer_id' => $referrer_id,
                'referred_id' => $referred_id,
                'status' => $this->input->post('status', 'pending'),
                'reward_points' => $this->input->post('reward_points', 0),
                'reward_amount' => $this->input->post('reward_amount', 0),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($this->Referral_model->create($data)) {
                $this->session->set_flashdata('success', 'Referral created successfully');
                redirect('admin/referrals');
            } else {
                $this->session->set_flashdata('error', 'Failed to create referral');
                redirect('admin/referral_create');
            }
        }

        $data['users'] = $this->User_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/referrals/create', $data);
        $this->load->view('admin/footer');
    }

    public function referral_edit($id)
    {
        $this->check_login();

        $referral = $this->Referral_model->get_by_id($id);
        if (!$referral) {
            $this->session->set_flashdata('error', 'Referral not found');
            redirect('admin/referrals');
            return;
        }

        if ($this->input->post()) {
            $data = array(
                'status' => $this->input->post('status'),
                'reward_points' => $this->input->post('reward_points'),
                'reward_amount' => $this->input->post('reward_amount'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($this->Referral_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'Referral updated successfully');
                redirect('admin/referrals');
            } else {
                $this->session->set_flashdata('error', 'Failed to update referral');
                redirect('admin/referral_edit/' . $id);
            }
        }

        $data['referral'] = $referral;
        $this->load->view('admin/header');
        $this->load->view('admin/referrals/edit', $data);
        $this->load->view('admin/footer');
    }

    public function referral_delete($id)
    {
        $this->check_login();

        if ($this->Referral_model->delete($id)) {
            $this->session->set_flashdata('success', 'Referral deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete referral');
        }

        redirect('admin/referrals');
    }

    // ==================== WISHLISTS MANAGEMENT ====================

    public function wishlists()
    {
        $this->check_login();

        $search = $this->input->get('search') ?: null;
        $user_id = $this->input->get('user_id') ?: null;

        if ($search) {
            $wishlists = $this->Wishlist_model->search($search, $user_id);
        } elseif ($user_id) {
            $wishlists = $this->Wishlist_model->get_by_user($user_id);
        } else {
            $wishlists = $this->Wishlist_model->get_all();
        }

        $data['wishlists'] = $wishlists;
        $data['search'] = $search;
        $data['user_id'] = $user_id;
        $data['users'] = $this->User_model->get_all();

        $this->load->view('admin/header');
        $this->load->view('admin/wishlists/list', $data);
        $this->load->view('admin/footer');
    }

    public function wishlist_view($id)
    {
        $this->check_login();

        $wishlist = $this->Wishlist_model->get_by_id($id);
        if (!$wishlist) {
            $this->session->set_flashdata('error', 'Wishlist item not found');
            redirect('admin/wishlists');
            return;
        }

        $data['wishlist'] = $wishlist;
        $this->load->view('admin/header');
        $this->load->view('admin/wishlists/view', $data);
        $this->load->view('admin/footer');
    }

    public function wishlist_delete($id)
    {
        $this->check_login();

        if ($this->Wishlist_model->delete($id)) {
            $this->session->set_flashdata('success', 'Wishlist item deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete wishlist item');
        }

        redirect('admin/wishlists');
    }

    // ==================== SEO Settings ====================

    public function seo_settings()
    {
        $this->check_login();
        $this->load->model('Seo_settings_model');

        $data['seo_pages'] = $this->Seo_settings_model->get_all();
        $this->load->view('admin/header');
        $this->load->view('admin/seo_settings', $data);
        $this->load->view('admin/footer');
    }

    public function seo_settings_save()
    {
        $this->check_login();
        $this->load->model('Seo_settings_model');

        $pages = $this->input->post('pages');
        if (is_array($pages)) {
            foreach ($pages as $id => $fields) {
                $update = [
                    'meta_title'       => $this->input->post("pages[$id][meta_title]"),
                    'meta_description' => $this->input->post("pages[$id][meta_description]"),
                    'meta_keywords'    => $this->input->post("pages[$id][meta_keywords]"),
                    'og_title'         => $this->input->post("pages[$id][og_title]"),
                    'og_description'   => $this->input->post("pages[$id][og_description]"),
                    'canonical_url'    => $this->input->post("pages[$id][canonical_url]"),
                    'status'           => isset($fields['status']) ? 1 : 0,
                ];
                $this->Seo_settings_model->update((int)$id, $update);
            }
            $this->session->set_flashdata('success', 'SEO settings saved successfully');
        } else {
            $this->session->set_flashdata('error', 'No data received');
        }

        redirect('admin/seo_settings');
    }
}

