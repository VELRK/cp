<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('db_store');
        $this->load->library('email');
    }

    public function index()
    {
        $data['title'] = 'Contact Us - Real Estate';
        $data['page'] = 'contact';
        // Single format: E.164 for tel: / SMS, readable label for display (India +91)
        $data['contact_phone_tel'] = '+918988982030';
        $data['contact_phone_display'] = '+91 89889 82030';
        $data['contact_email'] = 'reachmr.karthick@gmail.com';

        $this->load->view('header', $data);
        $this->load->view('contact', $data);
        $this->load->view('footer', $data);
    }
    
    /**
     * Handle contact form submission
     */
    public function submit()
    {
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $subject = $this->input->post('subject');
        $message = $this->input->post('message');
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($message)) {
            $this->json_response(false, 'Please fill in all required fields');
            return;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json_response(false, 'Please enter a valid email address');
            return;
        }
        
        // Check if user is logged in and get user details from user document
        $this->load->library('session');
        $user = $this->session->userdata('user');
        $userDetails = array();
        
        // Extract country code from phone if it starts with +
        $countryCode = '+91'; // Default
        $phoneNumber = $phone; // Keep original phone for form data
        if (!empty($phone) && preg_match('/^(\+\d{1,4})/', $phone, $matches)) {
            $countryCode = $matches[1];
            $phoneNumber = preg_replace('/^' . preg_quote($countryCode, '/') . '/', '', $phone);
        }
        
        if ($user && !empty($user)) {
            // Get user data from users table if documentId exists
            $userDocumentData = array();
            if (isset($user['documentId']) && !empty($user['documentId'])) {
                $userDocResult = $this->db_store->getUserById($user['documentId']);
                if ($userDocResult['success'] && isset($userDocResult['data'])) {
                    $fields = $userDocResult['data'];
                    $userDocumentData = array(
                        'name' => isset($fields['fullName']) ? $fields['fullName'] : (isset($user['fullName']) ? $user['fullName'] : (isset($user['displayName']) ? $user['displayName'] : $name)),
                        'phone' => isset($fields['phoneNumber']) ? $fields['phoneNumber'] : (isset($user['phoneNumber']) ? $user['phoneNumber'] : $phone),
                        'email' => isset($fields['email']) ? $fields['email'] : (isset($user['email']) ? $user['email'] : $email),
                        'countryCode' => isset($fields['countryCode']) ? $fields['countryCode'] : (isset($user['countryCode']) ? $user['countryCode'] : $countryCode)
                    );
                }
            }
            
            // If we couldn't get from document, use session data or form data
            if (empty($userDocumentData['name'])) {
                $userDocumentData = array(
                    'name' => isset($user['fullName']) ? $user['fullName'] : (isset($user['displayName']) ? $user['displayName'] : $name),
                    'phone' => isset($user['phoneNumber']) ? $user['phoneNumber'] : $phoneNumber,
                    'email' => isset($user['email']) ? $user['email'] : $email,
                    'countryCode' => isset($user['countryCode']) ? $user['countryCode'] : $countryCode
                );
            }
            
            // Add user details to array (from user document) - include documentId
            $documentId = isset($user['documentId']) ? $user['documentId'] : (isset($user['localId']) ? $user['localId'] : '');
            $userDetails = array(
                'documentId' => $documentId,
                'userId' => $documentId,
                'name' => $userDocumentData['name'],
                'email' => $userDocumentData['email'],
                'phone' => $userDocumentData['phone'],
                'countryCode' => $userDocumentData['countryCode'],
                'isLoggedIn' => true
            );
        } else {
            // User not logged in - use form data for user details
            $userDetails = array(
                'documentId' => '',
                'userId' => '',
                'name' => $name,
                'email' => $email,
                'phone' => $phoneNumber,
                'countryCode' => $countryCode,
                'isLoggedIn' => false
            );
        }
        
        // Prepare contact data - only include columns that exist in the contacts table
        // Table columns: id, name, email, phone, subject, message, status, created_at
        $contactData = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject ?: 'General Inquiry',
            'message' => $message,
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s')
        );
        
        // Prepare customer data - only include columns that exist in the customers table
        // Table columns: id, name, email, phone, contactCount, status, source, lastContactDate, 
        //               lastEnquiryProperty, lastEnquiryPropertyId, ip_address, user_agent, createdAt, updatedAt
        // Note: Customer storage is optional and won't block contact submission if it fails
        $customerData = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'status' => 'active',
            'source' => 'contact_form',
            'lastContactDate' => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        );
        
        // Save contact first (do not block contact submission if customer sync fails)
        $contactResult = $this->db_store->addContact($contactData);

        if (!$contactResult['success']) {
            // Log detailed error for debugging
            error_log('Contact form DB error: ' . json_encode($contactResult));
            
            $errorMessage = 'Failed to save your message. Please try again.';
            if (isset($contactResult['error']) && is_array($contactResult['error'])) {
                if (isset($contactResult['error']['error']['message'])) {
                    $errorMessage = 'Error: ' . $contactResult['error']['error']['message'];
                } elseif (isset($contactResult['error']['message'])) {
                    $errorMessage = 'Error: ' . $contactResult['error']['message'];
                }
            } elseif (isset($contactResult['error']) && is_string($contactResult['error'])) {
                $errorMessage = 'Error: ' . $contactResult['error'];
            }
            
            $this->json_response(false, $errorMessage, $contactResult);
            return;
        }

        // Customer storage is best-effort only.
        $customerResult = $this->db_store->addOrUpdateCustomer($customerData);
        if (!$customerResult['success']) {
            error_log('Customer storage error: ' . json_encode($customerResult));
        }
        
        // Send email to client
        $emailSent = $this->sendContactEmail($contactData);
        
        if ($emailSent) {
            $this->json_response(true, 'Thank you for contacting us! We will get back to you soon.');
        } else {
            // Even if email fails, still return success since data is saved
            $this->json_response(true, 'Thank you for contacting us! Your message has been received.');
        }
    }
    
    /**
     * Send email notification to client
     */
    private function sendContactEmail($contactData)
    {
        try {
            // Configure email settings (adjust these based on your email config)
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = 'smtp.gmail.com'; // Change to your SMTP server
            $config['smtp_port'] = 587;
            $config['smtp_user'] = 'your-email@gmail.com'; // Change to your email
            $config['smtp_pass'] = 'your-password'; // Change to your password
            $config['smtp_crypto'] = 'tls';
            $config['mailtype'] = 'html';
            $config['charset'] = 'utf-8';
            $config['newline'] = "\r\n";
            
            $this->email->initialize($config);
            
            $this->email->from($contactData['email'], $contactData['name']);
            $this->email->to('your-email@gmail.com'); // Change to your receiving email
            $this->email->subject('New Contact Form Submission: ' . $contactData['subject']);
            
            $emailBody = "
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$contactData['name']}</p>
                <p><strong>Email:</strong> {$contactData['email']}</p>
                <p><strong>Phone:</strong> {$contactData['phone']}</p>
                <p><strong>Subject:</strong> {$contactData['subject']}</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($contactData['message'])) . "</p>
                <p><strong>Submitted:</strong> " . (isset($contactData['created_at']) ? $contactData['created_at'] : (isset($contactData['createdAt']) ? $contactData['createdAt'] : date('Y-m-d H:i:s'))) . "</p>
            ";
            
            $this->email->message($emailBody);
            
            if ($this->email->send()) {
                return true;
            } else {
                error_log('Email Error: ' . $this->email->print_debugger());
                return false;
            }
        } catch (Exception $e) {
            error_log('Email Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * JSON response helper
     */
    private function json_response($success, $message, $data = null)
    {
        header('Content-Type: application/json');
        $response = array(
            'success' => $success,
            'message' => $message
        );
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
}
