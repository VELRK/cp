<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('db_store');
        $this->load->helper('url');
    }

    // Dashboard wishlist page
    public function wishlist() {
        $data['title'] = 'My Wishlist';
        $data['page'] = 'wishlist';
        
        // Get user data from session
        $userData = $this->getUserData();
        if (!$userData) {
            redirect('login');
            return;
        }
        
        // Get wishlist data
        $wishlistData = $this->getWishlistData($userData['userId']);
        $data['wishlist'] = $wishlistData;
        $data['userData'] = $userData;
        
        $this->load->view('dashboard/layout/header', $data);
        $this->load->view('dashboard/layout/sidebar', $data);
        $this->load->view('dashboard/wishlist', $data);
        $this->load->view('dashboard/layout/footer', $data);
    }

    // Dashboard enquiries page
    public function enquiries() {
        $data['title'] = 'My Enquiries';
        $data['page'] = 'enquiries';
        
        // Get user data from session
        $userData = $this->getUserData();
        if (!$userData) {
            redirect('login');
            return;
        }
        
        // Get enquiry data
        $enquiryData = $this->getEnquiryData($userData['userId']);
        $data['enquiries'] = $enquiryData;
        $data['userData'] = $userData;
        
        $this->load->view('dashboard/layout/header', $data);
        $this->load->view('dashboard/layout/sidebar', $data);
        $this->load->view('dashboard/enquiries', $data);
        $this->load->view('dashboard/layout/footer', $data);
    }

    // Get user data from session
    private function getUserData() {
        $this->load->library('session');
        $user = $this->session->userdata('user');
        
        if (!$user || empty($user)) {
            return null;
        }
        
        // Extract user data from session
        return array(
            'userId' => isset($user['documentId']) ? $user['documentId'] : (isset($user['localId']) ? $user['localId'] : ''),
            'userName' => isset($user['fullName']) ? $user['fullName'] : (isset($user['displayName']) ? $user['displayName'] : 'User'),
            'userEmail' => isset($user['email']) ? $user['email'] : (isset($user['phoneNumber']) ? $user['phoneNumber'] : ''),
            'phoneNumber' => isset($user['phoneNumber']) ? $user['phoneNumber'] : '',
            'documentId' => isset($user['documentId']) ? $user['documentId'] : ''
        );
    }

    // Get wishlist data
    private function getWishlistData($userId) {
        // This can be replaced with a dedicated wishlist table when available.
        // For now, return sample data
        return array(
            array(
                'id' => '1',
                'propertyId' => 'prop1',
                'propertyName' => 'Gorgeous Apartment Building',
                'propertyPrice' => '₹7,50,000',
                'propertyImage' => base_url('assets/images/home/house-18.jpg'),
                'addedAt' => '2024-03-22',
                'status' => 'active'
            ),
            array(
                'id' => '2',
                'propertyId' => 'prop2',
                'propertyName' => 'Mountain Mist Retreat, Aspen',
                'propertyPrice' => '₹12,50,000',
                'propertyImage' => base_url('assets/images/home/house-33.jpg'),
                'addedAt' => '2024-03-21',
                'status' => 'active'
            )
        );
    }

    // Get enquiry data
    private function getEnquiryData($userId) {
        $result = $this->db_store->getEnquiries($userId);
        
        if ($result['success']) {
            return $result['enquiries'];
        } else {
            error_log('Failed to fetch enquiries: ' . json_encode($result['error']));
            return array();
        }
    }

    /**
     * Delete user account
     */
    public function delete_account() {
        header('Content-Type: application/json');
        
        // Check if user is logged in
        $this->load->library('session');
        $user = $this->session->userdata('user');
        
        if (!$user || empty($user)) {
            echo json_encode(array('success' => false, 'message' => 'User not logged in'));
            return;
        }
        
        // Get user document ID
        $userId = isset($user['documentId']) ? $user['documentId'] : (isset($user['localId']) ? $user['localId'] : '');
        
        if (empty($userId)) {
            echo json_encode(array('success' => false, 'message' => 'User ID not found'));
            return;
        }
        
        // Delete user from users table
        $result = $this->db_store->deleteUser($userId);
        
        if ($result['success']) {
            // Clear session
            $this->session->unset_userdata('user');
            $this->session->sess_destroy();
            
            echo json_encode(array('success' => true, 'message' => 'Account deleted successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to delete account: ' . (isset($result['error']) ? json_encode($result['error']) : 'Unknown error')));
        }
    }
}
