<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TestUpdate extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('db_store');
    }
    
    public function index() {
        // Test user id (replace with a real users.id value in your database)
        $testUserId = 1;
        
        echo "<h2>Testing User Update Process</h2>";
        
        // Step 1: Get existing user
        echo "<h3>1. Getting existing user...</h3>";
        $existingDoc = $this->db_store->getUserById($testUserId);
        
        if ($existingDoc['success']) {
            echo "<p style='color: green;'>✅ User retrieved successfully</p>";
            echo "<pre>" . json_encode($existingDoc['data'], JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Failed to get existing user: " . json_encode($existingDoc['error']) . "</p>";
            return;
        }
        
        echo "<h3>2. Testing update with phone number...</h3>";
        $updateData = array(
            'phoneNumber' => '9999999999'
        );
        
        $updateResult = $this->db_store->updateUser($testUserId, $updateData);
        
        if ($updateResult['success']) {
            echo "<p style='color: green;'>✅ Update successful</p>";
        } else {
            echo "<p style='color: red;'>❌ Update failed: " . json_encode($updateResult['error']) . "</p>";
        }
        
        echo "<h3>3. Getting user after update...</h3>";
        $afterUpdate = $this->db_store->getUserById($testUserId);
        
        if ($afterUpdate['success']) {
            echo "<p style='color: green;'>✅ User retrieved after update</p>";
            echo "<pre>" . json_encode($afterUpdate['data'], JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Failed to get user after update: " . json_encode($afterUpdate['error']) . "</p>";
        }
        
        echo "<h3>Test Complete</h3>";
    }
}
