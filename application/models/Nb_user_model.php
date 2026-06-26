<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_user_model extends CI_Model {

    protected $table = 'nb_users';

    public function get_by_email($email)
    {
        return $this->db->get_where($this->table, array('email' => $email))->row();
    }

    /**
     * Find user by phone; matches exact stored value or last 10 digits (handles +91, spaces, dashes).
     */
    public function get_by_phone($input)
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }
        $row = $this->db->get_where($this->table, array('phone' => $input))->row();
        if ($row) {
            return $row;
        }
        $digits = preg_replace('/\D+/', '', $input);
        if (strlen($digits) < 10) {
            return null;
        }
        $last10 = substr($digits, -10);
        $t = $this->db->dbprefix($this->table);
        $sql = "SELECT * FROM {$t} WHERE RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'(',''),')',''),'+',''), 10) = ? LIMIT 1";
        return $this->db->query($sql, array($last10))->row();
    }

    /** @param string $identifier email or phone */
    public function get_by_email_or_phone($identifier)
    {
        $identifier = trim((string) $identifier);
        if ($identifier === '') {
            return null;
        }
        if (strpos($identifier, '@') !== false) {
            return $this->get_by_email(strtolower($identifier));
        }
        return $this->get_by_phone($identifier);
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, array('id' => (int) $id))->row();
    }

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int) $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id);
        return $this->db->delete($this->table);
    }

    /** @return object|null */
    public function get_by_api_token($token)
    {
        $token = trim((string) $token);
        if ($token === '' || !$this->db->field_exists('api_token', $this->table)) {
            return null;
        }
        $row = $this->db->get_where($this->table, array('api_token' => $token))->row();
        if (!$row) {
            return null;
        }
        if ($this->db->field_exists('api_token_expires_at', $this->table)
            && !empty($row->api_token_expires_at)) {
            $t = strtotime($row->api_token_expires_at);
            if ($t !== false && $t < time()) {
                return null;
            }
        }
        return $row;
    }

    /**
     * @param string|null $expires_at Y-m-d H:i:s or null = no expiry
     */
    public function set_api_token($user_id, $token, $expires_at = null)
    {
        if (!$this->db->field_exists('api_token', $this->table)) {
            return false;
        }
        $data = array('api_token' => $token);
        if ($this->db->field_exists('api_token_expires_at', $this->table)) {
            $data['api_token_expires_at'] = $expires_at;
        }
        $this->db->where('id', (int) $user_id);
        return $this->db->update($this->table, $data);
    }

    public function clear_api_token($user_id)
    {
        if (!$this->db->field_exists('api_token', $this->table)) {
            return false;
        }
        $data = array('api_token' => null);
        if ($this->db->field_exists('api_token_expires_at', $this->table)) {
            $data['api_token_expires_at'] = null;
        }
        $this->db->where('id', (int) $user_id);
        return $this->db->update($this->table, $data);
    }

    public function count_all()
    {
        return (int) $this->db->count_all($this->table);
    }

    /**
     * Distinct non-empty FCM registration tokens (for push to all devices that registered a token).
     *
     * @return string[]
     */
    public function get_distinct_fcm_tokens()
    {
        if (!$this->db->field_exists('fcm_token', $this->table)) {
            return array();
        }
        $this->db->distinct();
        $this->db->select('fcm_token');
        $this->db->where('fcm_token IS NOT NULL', null, false);
        $this->db->where('fcm_token !=', '');
        $rows = $this->db->get($this->table)->result();
        $out = array();
        foreach ($rows as $row) {
            $t = isset($row->fcm_token) ? trim((string) $row->fcm_token) : '';
            if ($t !== '') {
                $out[] = $t;
            }
        }
        return array_values(array_unique($out));
    }

    public function count_pending()
    {
        if ($this->db->field_exists('is_verified', $this->table)) {
            return (int) $this->db->where('is_verified', 0)->count_all_results($this->table);
        }
        return 0;
    }

    public function count_unverified()
    {
        if (!$this->db->field_exists('is_verified', $this->table)) {
            return 0;
        }
        return (int) $this->db->where('is_verified', 0)->count_all_results($this->table);
    }

    /** @return object[] rows with id, name, email */
    public function owners_for_select()
    {
        $this->db->select('id, name, email');
        $this->db->where('role', 'owner');
        $this->db->where('status', 'approved');
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function admin_list($limit = 30, $offset = 0, $filters = array())
    {
        $this->db->reset_query();
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $this->db->group_start();
            $this->db->like('name', $q);
            $this->db->or_like('email', $q);
            $this->db->group_end();
        }
        if (!empty($filters['role'])) {
            $this->db->where('role', $filters['role']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }

        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get($this->table)->result();
    }

    public function update_otp($user_id, $otp, $expires_at)
    {
        if (!$this->db->field_exists('otp', $this->table)) {
            return false;
        }
        $this->db->where('id', (int) $user_id);
        return $this->db->update($this->table, array(
            'otp' => $otp,
            'otp_expires_at' => $expires_at,
        ));
    }

    public function verify_otp($phone, $otp)
    {
        $user = $this->get_by_phone($phone);
        if (!$user) {
            return array('success' => false, 'error' => 'user_not_found', 'message' => 'User not found');
        }
        if (empty($user->otp)) {
            return array('success' => false, 'error' => 'no_otp', 'message' => 'No OTP found. Please request a new OTP.');
        }
        if (empty($user->otp_expires_at) || strtotime($user->otp_expires_at) <= time()) {
            return array('success' => false, 'error' => 'otp_expired', 'message' => 'OTP has expired. Please request a new OTP.');
        }
        if ((string) $user->otp !== (string) $otp) {
            return array('success' => false, 'error' => 'invalid_otp', 'message' => 'Invalid OTP. Please check and try again.');
        }
        $this->db->where('id', (int) $user->id);
        $this->db->update($this->table, array(
            'otp' => null,
            'otp_expires_at' => null,
        ));
        return array('success' => true, 'user' => $user);
    }
}
