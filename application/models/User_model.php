<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_phone($phone, $country_code = '+91')
    {
        return $this->db->get_where('users', array(
            'phonenumber' => $phone,
            'countrycode'  => $country_code
        ))->row();
    }

    /**
     * Legacy `users` row, or `nb_users` mapped to the same shape when `users` is absent.
     */
    public function get_by_id($id)
    {
        if ($this->db->table_exists('users')) {
            return $this->db->get_where('users', array('id' => $id))->row();
        }
        if ($this->db->table_exists('nb_users')) {
            $row = $this->db->get_where('nb_users', array('id' => (int) $id))->row();
            return $row ? $this->_map_nb_user_to_legacy_row($row) : null;
        }
        return null;
    }

    /**
     * @param object $row nb_users row
     * @return object Fields expected by Api_mobile / Auth (fullname, phonenumber, isactive, …)
     */
    protected function _map_nb_user_to_legacy_row($row)
    {
        $name = isset($row->name) ? (string) $row->name : '';
        $phone = isset($row->phone) ? (string) $row->phone : '';
        $approved = isset($row->status) && strtolower((string) $row->status) === 'approved';
        $o = new stdClass();
        $o->id = isset($row->id) ? (int) $row->id : 0;
        $o->fullname = $name;
        $o->name = $name;
        $o->email = isset($row->email) ? (string) $row->email : '';
        $o->phonenumber = $phone;
        $o->phone = $phone;
        $o->countrycode = '+91';
        $o->city = null;
        $o->state = null;
        $o->pincode = null;
        $o->isactive = $approved ? 'active' : 'inactive';
        $o->is_verified = $approved ? 1 : 0;
        $o->referralcode = isset($row->referralcode) ? $row->referralcode : null;
        $o->user_type = isset($row->user_type) ? $row->user_type : null;
        $o->role = isset($row->role) ? $row->role : null;
        $o->status = isset($row->status) ? $row->status : null;
        if (!empty($row->profile_pic)) {
            $o->profilepic = $row->profile_pic;
        }
        if (isset($row->aadhar_no)) {
            $o->aadhar_number = $row->aadhar_no;
        }
        if (isset($row->aadhar_file)) {
            $o->aadhar_image = $row->aadhar_file;
        }
        if (isset($row->experience_years)) {
            $o->experience_years = $row->experience_years;
        }
        if (isset($row->created_at)) {
            $o->created_at = $row->created_at;
        }
        return $o;
    }

    public function create($data)
    {
        $this->db->insert('users', $data);
        return $this->db->affected_rows() > 0;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    public function update_otp($phone, $country_code, $otp, $expires_at)
    {
        $this->db->where('phonenumber', $phone);
        $this->db->where('countrycode', $country_code);
        return $this->db->update('users', array(
            'otp' => $otp,
            'otp_expires_at' => $expires_at
        ));
    }

    public function verify_otp($phone, $country_code, $otp)
    {
        $user = $this->get_by_phone($phone, $country_code);
        
        if (!$user) {
            return array('success' => false, 'error' => 'user_not_found', 'message' => 'User not found');
        }
        
        // Check if OTP exists
        if (empty($user->otp)) {
            return array('success' => false, 'error' => 'no_otp', 'message' => 'No OTP found. Please request a new OTP.');
        }
        
        // Check if OTP is expired
        if (empty($user->otp_expires_at) || strtotime($user->otp_expires_at) <= time()) {
            return array('success' => false, 'error' => 'otp_expired', 'message' => 'OTP has expired. Please request a new OTP.');
        }
        
        // Check if OTP matches
        if ($user->otp != $otp) {
            return array('success' => false, 'error' => 'invalid_otp', 'message' => 'Invalid OTP. Please check and try again.');
        }
        
        // OTP is valid - Mark as verified and clear OTP
        $this->db->where('id', $user->id);
        $this->db->update('users', array(
            'is_verified' => 1,
            'otp' => null,
            'otp_expires_at' => null
        ));
        
        return array('success' => true, 'user' => $user);
    }
    
    /**
     * Check OTP expiration status
     * @param string $phone
     * @param string $country_code
     * @return array Status information
     */
    public function check_otp_status($phone, $country_code = '+91')
    {
        $user = $this->get_by_phone($phone, $country_code);
        
        if (!$user || empty($user->otp)) {
            return array(
                'has_otp' => false,
                'expired' => false,
                'expires_at' => null,
                'time_remaining' => 0
            );
        }
        
        $expires_at = strtotime($user->otp_expires_at);
        $current_time = time();
        $time_remaining = max(0, $expires_at - $current_time);
        $is_expired = $expires_at <= $current_time;
        
        return array(
            'has_otp' => true,
            'expired' => $is_expired,
            'expires_at' => $user->otp_expires_at,
            'time_remaining' => $time_remaining,
            'expires_at_timestamp' => $expires_at
        );
    }

    public function is_phone_exists($phone, $country_code = '+91')
    {
        $this->db->where('phonenumber', $phone);
        $this->db->where('countrycode', $country_code);
        return $this->db->count_all_results('users') > 0;
    }

    /**
     * Get all users with optional status filtering
     * @param string $status Optional status filter (active/inactive)
     * @return array Array of user objects
     */
    public function get_all($status = null)
    {
        $this->db->select('*');
        if ($status) {
            $this->db->where('isactive', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('users')->result();
    }

    /**
     * Get users by city
     * @param string $city City name
     * @return array Array of user objects
     */
    public function get_by_city($city)
    {
        $this->db->where('city', $city);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('users')->result();
    }

    /**
     * Search users by name, email, or phone
     * @param string $search Search keyword
     * @return array Array of user objects
     */
    public function search($search = '')
    {
        if (!$search) {
            return $this->get_all();
        }

        $this->db->like('fullname', $search);
        $this->db->or_like('email', $search);
        $this->db->or_like('phonenumber', $search);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('users')->result();
    }

    /**
     * Check if email already exists
     * @param string $email Email address
     * @param string $exclude_id Optional user ID to exclude from check
     * @return bool True if email exists
     */
    public function is_email_exists($email, $exclude_id = null)
    {
        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results('users') > 0;
    }

    /**
     * Check if phone exists
     * @param string $phone Phone number
     * @param string $exclude_id Optional user ID to exclude from check
     * @return bool True if phone exists
     */
    public function is_phone_exists_exclude($phone, $exclude_id = null, $country_code = '+91')
    {
        $this->db->where('phonenumber', $phone);
        $this->db->where('countrycode', $country_code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results('users') > 0;
    }

    /**
     * Count all users or by status
     * @param string $status Optional status filter
     * @return int Total count
     */
    public function count_all($status = null)
    {
        if ($status) {
            $this->db->where('isactive', $status);
        }
        return $this->db->count_all_results('users');
    }

    /**
     * Validate Indian phone number format (10 digits)
     * @param string $phone Phone number
     * @return bool True if valid
     */
    public function validate_phone($phone)
    {
        // Remove any non-digit characters
        $clean_phone = preg_replace('/\D/', '', $phone);
        // Indian phone numbers are 10 digits
        return strlen($clean_phone) === 10;
    }

    /**
     * Validate email format
     * @param string $email Email address
     * @return bool True if valid
     */
    public function validate_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Delete user record
     * @param string $id User ID
     * @return bool True if deleted successfully
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('users');
    }

    /**
     * Delete multiple users
     * @param array $ids Array of user IDs
     * @return bool True if deleted successfully
     */
    public function delete_bulk($ids)
    {
        if (empty($ids)) {
            return false;
        }
        $this->db->where_in('id', $ids);
        return $this->db->delete('users');
    }

    /**
     * Update status for multiple users
     * @param array $ids Array of user IDs
     * @param string $status New status (active/inactive)
     * @return bool True if updated successfully
     */
    public function update_status_bulk($ids, $status)
    {
        if (empty($ids)) {
            return false;
        }
        $this->db->where_in('id', $ids);
        return $this->db->update('users', array('isactive' => $status));
    }
}

