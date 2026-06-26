<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('whatsapp_library');
        $this->output->set_content_type('application/json');
    }

    /**
     * Send OTP to phone number
     * POST /auth/send_otp
     */
    public function send_otp()
    {
        try {
            $phone = $this->input->post('phone');
            $country_code = $this->input->post('country_code') ?: '+91';
            
            if (empty($phone)) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Phone number is required'
                )));
                return;
            }
            
            // Test mode: fixed OTP for test number
            $test_phone        = '9876543210';
            $test_country_code = '+91';
            $test_otp          = '1234';

            if ($phone == $test_phone && $country_code == $test_country_code) {
                $otp = $test_otp;
            } else {
                $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            }
            
            // OTP expires in 5 minutes
            $otp_expires_at = date('Y-m-d H:i:s', time() + 300);
            
            // Check if user exists
            $user = $this->User_model->get_by_phone($phone, $country_code);
            
            if ($user) {
                // Update existing user's OTP
                $this->User_model->update_otp($phone, $country_code, $otp, $otp_expires_at);
            } else {
                // Create new user with OTP
                $this->User_model->create(array(
                    'phone' => $phone,
                    'country_code' => $country_code,
                    'otp' => $otp,
                    'otp_expires_at' => $otp_expires_at,
                    'is_verified' => 0,
                    'status' => 'active'
                ));
            }
            
            // Send OTP via WhatsApp
            $full_phone = $country_code . $phone;
            
            // Load WhatsApp config
            $this->config->load('whatsapp', TRUE);
            $whatsapp_config = $this->config->item('whatsapp');
            
            // Check if development mode is enabled
            $development_mode = isset($whatsapp_config['development_mode']) && $whatsapp_config['development_mode'] === true;
            if ($development_mode) {
                // Development mode: Skip actual WhatsApp sending, show cURL command
                $curl_command = $this->whatsapp_library->get_curl_command($full_phone, $otp);
                
                log_message('debug', 'DEVELOPMENT MODE: OTP generated but not sent via WhatsApp');
                log_message('debug', 'DEVELOPMENT MODE: OTP = ' . $otp);
                log_message('debug', 'DEVELOPMENT MODE: cURL Command: ' . $curl_command);
                
                $response = array(
                    'success' => true,
                    'message' => 'OTP generated (Development Mode - Not sent via WhatsApp)',
                    'is_new_user' => !$user,
                    'development_mode' => true,
                    'otp' => $otp, // Include OTP in response for testing
                    'curl_command' => $curl_command // Include cURL command for testing
                );
            } else {
                // Production mode: Actually send OTP via WhatsApp
                // Log config status for debugging
                $config_status = $this->whatsapp_library->get_config_status();
                log_message('debug', 'WhatsApp Config Status: ' . json_encode($config_status));
                
                // Send OTP via WhatsApp
                $whatsapp_result = $this->whatsapp_library->send_otp($full_phone, $otp);
                
                if ($whatsapp_result['success']) {
                    // OTP sent successfully via WhatsApp
                    $response = array(
                        'success' => true,
                        'message' => 'OTP sent successfully to your WhatsApp number',
                        'is_new_user' => !$user
                    );
                } else {
                    // WhatsApp sending failed - include actual error message
                    $error_message = isset($whatsapp_result['message']) ? $whatsapp_result['message'] : 'Unknown error';
                    $response = array(
                        'success' => false,
                        'message' => 'Failed to send OTP via WhatsApp: ' . $error_message,
                        'is_new_user' => !$user,
                        'error' => $error_message
                    );
                }
            }
            
            $this->output->set_output(json_encode($response));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error sending OTP: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Verify OTP and login
     * POST /auth/verify_otp
     */
    public function verify_otp()
    {
        try {
            $phone = $this->input->post('phone');
            $country_code = $this->input->post('country_code') ?: '+91';
            $otp = $this->input->post('otp');
            
            if (empty($phone) || empty($otp)) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Phone number and OTP are required'
                )));
                return;
            }

            $result = $this->User_model->verify_otp($phone, $country_code, $otp);
            
            if (is_array($result) && isset($result['success']) && $result['success']) {
                $user = $result['user'];
                
                // Set session
                $this->session->set_userdata(array(
                    'user_logged_in' => true,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_phone' => $user->phone,
                    'user_country_code' => $user->country_code,
                    'user_address' => $user->address
                ));
                
                // Check if user needs to complete profile
                $needs_profile = empty($user->name) || empty($user->email);
                
                $this->output->set_output(json_encode(array(
                    'success' => true,
                    'message' => 'OTP verified successfully',
                    'needs_profile' => $needs_profile,
                    'user' => array(
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'country_code' => $user->country_code,
                        'address' => $user->address
                    )
                )));
            } else {
                // Handle different error types
                $error_message = 'Invalid or expired OTP';
                if (is_array($result) && isset($result['message'])) {
                    $error_message = $result['message'];
                }
                
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => $error_message,
                    'error_type' => isset($result['error']) ? $result['error'] : 'unknown'
                )));
            }
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error verifying OTP: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Save/Update user profile
     * POST /auth/save_profile
     */
    public function save_profile()
    {
        try {
            if (!$this->session->userdata('user_logged_in')) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Please login first'
                )));
                return;
            }
            
            $user_id = $this->session->userdata('user_id');
            $name = $this->input->post('name');
            $email = $this->input->post('email');
            $address = $this->input->post('address');
            
            if (empty($name)) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Name is required'
                )));
                return;
            }
            
            $data = array(
                'name' => $name,
                'address' => $address
            );
            
            if (!empty($email)) {
                $data['email'] = $email;
            }
            
            $this->User_model->update($user_id, $data);
            
            // Update session
            $this->session->set_userdata(array(
                'user_name' => $name,
                'user_email' => $email,
                'user_address' => $address
            ));
            
            $this->output->set_output(json_encode(array(
                'success' => true,
                'message' => 'Profile saved successfully'
            )));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error saving profile: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Check if user is logged in
     * GET /auth/check
     */
    public function check()
    {
        if ($this->session->userdata('user_logged_in')) {
            $this->output->set_output(json_encode(array(
                'success' => true,
                'logged_in' => true,
                'user' => array(
                    'id' => $this->session->userdata('user_id'),
                    'name' => $this->session->userdata('user_name'),
                    'email' => $this->session->userdata('user_email'),
                    'phone' => $this->session->userdata('user_phone'),
                    'country_code' => $this->session->userdata('user_country_code'),
                    'address' => $this->session->userdata('user_address')
                )
            )));
        } else {
            $this->output->set_output(json_encode(array(
                'success' => true,
                'logged_in' => false
            )));
        }
    }

    /**
     * Logout
     * POST /auth/logout
     */
    public function logout()
    {
        $this->session->unset_userdata('user_logged_in');
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('user_name');
        $this->session->unset_userdata('user_email');
        $this->session->unset_userdata('user_phone');
        $this->session->unset_userdata('user_country_code');
        $this->session->unset_userdata('user_address');
        
        $this->output->set_output(json_encode(array(
            'success' => true,
            'message' => 'Logged out successfully'
        )));
    }

    /**
     * Check if phone number exists
     * POST /auth/check_phone_exists
     */
    public function check_phone_exists()
    {
        try {
            $phone = $this->input->post('phone');
            $country_code = $this->input->post('country_code') ?: '+91';
            
            if (empty($phone)) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Phone number is required'
                )));
                return;
            }
            
            $exists = $this->User_model->is_phone_exists($phone, $country_code);
            
            $this->output->set_output(json_encode(array(
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Phone number already registered' : 'Phone number available'
            )));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error checking phone: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Get user profile
     * GET /auth/profile
     */
    public function profile()
    {
        try {
            if (!$this->session->userdata('user_logged_in')) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Please login first'
                )));
                return;
            }
            
            $user_id = $this->session->userdata('user_id');
            $user = $this->User_model->get_by_id($user_id);
            
            if ($user) {
                $this->output->set_output(json_encode(array(
                    'success' => true,
                    'user' => array(
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'country_code' => $user->country_code,
                        'address' => $user->address,
                        'is_verified' => (bool)$user->is_verified,
                        'status' => $user->status,
                        'created_at' => $user->created_at
                    )
                )));
            } else {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'User not found'
                )));
            }
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error fetching profile: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Update user profile
     * POST /auth/update_profile
     */
    public function update_profile()
    {
        // Same as save_profile, kept for API consistency
        $this->save_profile();
    }

    /**
     * Resend OTP
     * POST /auth/resend_otp
     */
    public function resend_otp()
    {
        // Same as send_otp, kept for API clarity
        $this->send_otp();
    }

    /**
     * Change phone number
     * POST /auth/change_phone
     */
    public function change_phone()
    {
        try {
            if (!$this->session->userdata('user_logged_in')) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Please login first'
                )));
                return;
            }
            
            $user_id = $this->session->userdata('user_id');
            $new_phone = $this->input->post('phone');
            $country_code = $this->input->post('country_code') ?: '+91';
            
            if (empty($new_phone)) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Phone number is required'
                )));
                return;
            }
            
            // Check if new phone already exists
            $existing_user = $this->User_model->get_by_phone($new_phone, $country_code);
            if ($existing_user && $existing_user->id != $user_id) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Phone number already registered to another account'
                )));
                return;
            }
            
            // Generate OTP for new phone
            $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $otp_expires_at = date('Y-m-d H:i:s', time() + 300);
            
            // Update user with new phone and OTP
            $this->User_model->update($user_id, array(
                'phone' => $new_phone,
                'country_code' => $country_code,
                'otp' => $otp,
                'otp_expires_at' => $otp_expires_at,
                'is_verified' => 0
            ));
            
            // Send OTP via WhatsApp
            $full_phone = $country_code . $new_phone;
            $this->config->load('whatsapp', TRUE);
            $whatsapp_config = $this->config->item('whatsapp');
            $development_mode = isset($whatsapp_config['development_mode']) && $whatsapp_config['development_mode'] === true;
            
            if ($development_mode) {
                $response = array(
                    'success' => true,
                    'message' => 'OTP generated for phone change (Development Mode)',
                    'otp' => $otp,
                    'requires_verification' => true
                );
            } else {
                $whatsapp_result = $this->whatsapp_library->send_otp($full_phone, $otp);
                if ($whatsapp_result['success']) {
                    $response = array(
                        'success' => true,
                        'message' => 'OTP sent to new phone number',
                        'requires_verification' => true
                    );
                } else {
                    $this->output->set_output(json_encode(array(
                        'success' => false,
                        'message' => 'Failed to send OTP: ' . (isset($whatsapp_result['message']) ? $whatsapp_result['message'] : 'Unknown error')
                    )));
                    return;
                }
            }
            
            $this->output->set_output(json_encode($response));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error changing phone: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Verify phone change OTP
     * POST /auth/verify_phone_change
     */
    public function verify_phone_change()
    {
        try {
            if (!$this->session->userdata('user_logged_in')) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Please login first'
                )));
                return;
            }
            
            $user_id = $this->session->userdata('user_id');
            $otp = $this->input->post('otp');
            
            if (empty($otp)) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'OTP is required'
                )));
                return;
            }
            
            $user = $this->User_model->get_by_id($user_id);
            if (!$user) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'User not found'
                )));
                return;
            }
            
            // Verify OTP
            if (empty($user->otp) || $user->otp != $otp) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Invalid OTP'
                )));
                return;
            }
            
            // Check if OTP expired
            if (strtotime($user->otp_expires_at) < time()) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'OTP has expired'
                )));
                return;
            }
            
            // Update user as verified and clear OTP
            $this->User_model->update($user_id, array(
                'is_verified' => 1,
                'otp' => null,
                'otp_expires_at' => null
            ));
            
            // Update session
            $this->session->set_userdata(array(
                'user_phone' => $user->phone,
                'user_country_code' => $user->country_code
            ));
            
            $this->output->set_output(json_encode(array(
                'success' => true,
                'message' => 'Phone number changed successfully'
            )));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error verifying phone change: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Delete user account
     * POST /auth/delete_account
     */
    public function delete_account()
    {
        try {
            if (!$this->session->userdata('user_logged_in')) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Please login first'
                )));
                return;
            }
            
            $user_id = $this->session->userdata('user_id');
            
            // Soft delete - update status to deleted
            $this->User_model->update($user_id, array(
                'status' => 'deleted'
            ));
            
            // Clear session
            $this->session->sess_destroy();
            
            $this->output->set_output(json_encode(array(
                'success' => true,
                'message' => 'Account deleted successfully'
            )));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error deleting account: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Refresh session
     * POST /auth/refresh_session
     */
    public function refresh_session()
    {
        try {
            if (!$this->session->userdata('user_logged_in')) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Please login first'
                )));
                return;
            }
            
            $user_id = $this->session->userdata('user_id');
            $user = $this->User_model->get_by_id($user_id);
            
            if (!$user || $user->status != 'active') {
                $this->session->sess_destroy();
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'User account is inactive or deleted'
                )));
                return;
            }
            
            // Refresh session data
            $this->session->set_userdata(array(
                'user_logged_in' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_phone' => $user->phone,
                'user_country_code' => $user->country_code,
                'user_address' => $user->address
            ));
            
            $this->output->set_output(json_encode(array(
                'success' => true,
                'message' => 'Session refreshed successfully',
                'user' => array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country_code' => $user->country_code,
                    'address' => $user->address
                )
            )));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error refreshing session: ' . $e->getMessage()
            )));
        }
    }
}

