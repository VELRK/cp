<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Listing extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('db_store');
    }

    /**
     * Old /listing URLs permanently redirect to /our-projects (query string preserved).
     */
    public function redirect_legacy()
    {
        $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
        redirect(site_url('our-projects') . $qs, 'location', 301);
    }

    public function index()
    {

      
        //print_r($this->input->post());exit;

        $data['title'] = 'Property Listing - Real Estate';
        $data['page']  = 'listing';
        
        // Get filter parameters from POST (form submission) or GET (pagination/sorting)
        $city     = $this->input->post('city') ? $this->input->post('city') : $this->input->get('city');
        $location = $this->input->post('location') ? $this->input->post('location') : $this->input->get('location');
        
        // Get pagination parameters
        $page      = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $limit     = $this->input->get('limit') ? (int)$this->input->get('limit') : 30;
        $sort      = $this->input->get('sort') ? $this->input->get('sort') : 'newest'; // newest, oldest, price_low, price_high
        $price_min = $this->input->get('price_min') ? (int)$this->input->get('price_min') : null;
        $price_max = $this->input->get('price_max') ? (int)$this->input->get('price_max') : null;
        
        // Store filter values for view
        $data['selected_city']     = $city;
        $data['selected_location'] = $location;


        // Fetch cities
        $citiesResult = $this->db_store->getCities();
        $data['cities'] = array();
        if ($citiesResult['success']) {
            $data['cities'] = $citiesResult['cities'];
        }
        
        // Fetch locations
        $locationsResult = $this->db_store->getLocationsList();
        $data['locations'] = array();
        if ($locationsResult['success']) {
            $data['locations'] = $locationsResult['locations'];
        }
        
        // Fetch categories
        $categoriesResult = $this->db_store->getCategories();
        $data['categories'] = array();
        if ($categoriesResult['success']) {
            $data['categories'] = $categoriesResult['categories'];
        }
        
        // Get selected category from POST or GET
        $category = $this->input->post('category') ? $this->input->post('category') : $this->input->get('category');
        $data['selected_category'] = $category; 
        
        // Validate limit (30 or 50 only)
        if (!in_array($limit, [30, 50])) {
            $limit = 30;
        }
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // If filtering by city, location, or category, we need to fetch all properties first
        $needAllProperties = ($city || $location || $category);
        $fetchLimit = $needAllProperties ? 1000 : $limit;
        $fetchOffset = $needAllProperties ? 0 : $offset;
        
        // Fetch properties from MySQL with city, location, and category filters
        $propertiesResult = $this->db_store->getProperties($fetchLimit, $fetchOffset, $sort, $price_min, $price_max, $city, $location, $category);
        $data['properties'] = array();
        $data['total_properties'] = 0;
        $data['current_page'] = $page;
        $data['limit'] = $limit;
        $data['sort'] = $sort;
        $data['price_min'] = $price_min;
        $data['price_max'] = $price_max;
        
        if ($propertiesResult['success']) {
            $allProperties = $propertiesResult['properties'];
            
            // Properties are already filtered in getProperties method
            // Apply sorting
            if (!empty($allProperties)) {
                $allProperties = $this->db_store->sortProperties($allProperties, $sort);
            }
            
            // Apply pagination
            $data['total_properties'] = count($allProperties);
            $data['properties'] = array_slice($allProperties, $offset, $limit);
            $data['total_pages'] = ceil($data['total_properties'] / $limit);
        } else {
            // Log error and use empty array as fallback
            error_log('Failed to fetch properties: ' . json_encode($propertiesResult['error']));
        }
               // Fetch locations
     
        
        $this->load->view('header', $data);
        $this->load->view('listing', $data);
        $this->load->view('footer', $data);
    }
}
