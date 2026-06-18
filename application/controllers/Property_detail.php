<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Property_detail extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('db_store');
    }

    public function index($propertyIdOrSlug = null)
    {
        $data['title'] = 'Property Details - Real Estate';
        $data['page'] = 'property_detail';

        // If property ID or slug is provided, fetch property from MySQL
        if ($propertyIdOrSlug) {
            $propertyResult = null;

            // Try to get by slug first (if it's not a pure numeric ID)
            if (!ctype_digit((string) $propertyIdOrSlug)) {
                $propertyResult = $this->db_store->getPropertyBySlug($propertyIdOrSlug);
            }

            // If not found by slug or it's a numeric ID, try by ID
            $accessedById = false;
            if (!$propertyResult || !$propertyResult['success'] || !isset($propertyResult['property'])) {
                $propertyResult = $this->db_store->getPropertyById($propertyIdOrSlug);
                $accessedById = true;
            }

            if ($propertyResult['success'] && isset($propertyResult['property'])) {
                $data['property'] = $propertyResult['property'];
                $propName = isset($data['property']['propertyName']) ? $data['property']['propertyName'] : 'Property Details';
                $data['title'] = $propName . ' | Coimbatore Properties';
                $desc = isset($data['property']['description']) ? strip_tags($data['property']['description']) : '';
                $data['meta_description'] = $desc ? mb_substr($desc, 0, 160) : 'View details for ' . $propName . ' — location, price, features and more at Coimbatore Properties.';

                // Redirect numeric ID URLs to slug URLs for SEO
                if ($accessedById && !empty($data['property']['slug'])) {
                    redirect('property-detail/' . $data['property']['slug'], 'location', 301);
                    return;
                }
            } else {
                // Property not found, show 404
                show_404();
                return;
            }
        } else {
            // No property ID provided, show 404
            show_404();
            return;
        }
        $this->load->view('header', $data);
        $this->load->view('property_detail', $data);
        $this->load->view('footer');
    }
}
