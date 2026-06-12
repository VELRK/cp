<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Referral_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all referrals with optional status filtering
     * @param string $status Optional status filter
     * @return array Array of referral objects
     */
    public function get_all($status = null)
    {
        $userJoin = $this->resolve_user_join();
        $select = 'r.*';
        if ($userJoin) {
            $select .= ', u1.' . $userJoin['name_col'] . ' as referrer_name, u1.email as referrer_email, u2.' . $userJoin['name_col'] . ' as referred_name, u2.email as referred_email';
        }
        $this->db->select($select);
        $this->db->from('referrals r');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u1', 'r.referrer_id = u1.id', 'left');
            $this->db->join($userJoin['table'] . ' u2', 'r.referred_id = u2.id', 'left');
        }

        if ($status) {
            $this->db->where('r.status', $status);
        }

        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get referral by ID
     * @param string $id Referral ID
     * @return object Referral object or null
     */
    public function get_by_id($id)
    {
        $userJoin = $this->resolve_user_join();
        $select = 'r.*';
        if ($userJoin) {
            $select .= ', u1.' . $userJoin['name_col'] . ' as referrer_name, u1.email as referrer_email, u2.' . $userJoin['name_col'] . ' as referred_name, u2.email as referred_email';
        }
        $this->db->select($select);
        $this->db->from('referrals r');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u1', 'r.referrer_id = u1.id', 'left');
            $this->db->join($userJoin['table'] . ' u2', 'r.referred_id = u2.id', 'left');
        }
        $this->db->where('r.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Get referrals by referrer user ID
     * @param string $user_id User ID of referrer
     * @param string $status Optional status filter
     * @return array Array of referral objects
     */
    public function get_by_referrer($user_id, $status = null)
    {
        $userJoin = $this->resolve_user_join();
        $select = 'r.*';
        if ($userJoin) {
            $select .= ', u2.' . $userJoin['name_col'] . ' as referred_name, u2.email as referred_email';
        }
        $this->db->select($select);
        $this->db->from('referrals r');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u2', 'r.referred_id = u2.id', 'left');
        }
        $this->db->where('r.referrer_id', $user_id);

        if ($status) {
            $this->db->where('r.status', $status);
        }

        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get referrals by referred user ID
     * @param string $user_id User ID of referred person
     * @return array Array of referral objects
     */
    public function get_by_referred($user_id)
    {
        $userJoin = $this->resolve_user_join();
        $select = 'r.*';
        if ($userJoin) {
            $select .= ', u1.' . $userJoin['name_col'] . ' as referrer_name, u1.email as referrer_email';
        }
        $this->db->select($select);
        $this->db->from('referrals r');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u1', 'r.referrer_id = u1.id', 'left');
        }
        $this->db->where('r.referred_id', $user_id);
        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result();
    }

    private function resolve_user_join()
    {
        if ($this->db->table_exists('nb_users')) {
            $nameCol = $this->db->field_exists('fullname', 'nb_users') ? 'fullname' : 'name';
            return array('table' => 'nb_users', 'name_col' => $nameCol);
        }
        if ($this->db->table_exists('users')) {
            $nameCol = $this->db->field_exists('fullname', 'users') ? 'fullname' : 'name';
            return array('table' => 'users', 'name_col' => $nameCol);
        }
        return null;
    }

    /**
     * Create a new referral
     * @param array $data Referral data
     * @return string Inserted ID or false
     */
    public function create($data)
    {
        if ($this->db->insert('referrals', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update referral
     * @param string $id Referral ID
     * @param array $data Update data
     * @return bool Update result
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('referrals', $data);
    }

    /**
     * Delete referral
     * @param string $id Referral ID
     * @return bool Delete result
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('referrals');
    }

    /**
     * Check if referral code exists
     * @param string $code Referral code
     * @return bool True if exists
     */
    public function is_code_exists($code)
    {
        $this->db->where('referral_code', $code);
        return $this->db->count_all_results('referrals') > 0;
    }

    /**
     * Get referral by code
     * @param string $code Referral code
     * @return object Referral object or null
     */
    public function get_by_code($code)
    {
        $this->db->where('referral_code', $code);
        return $this->db->get('referrals')->row();
    }

    /**
     * Count referrals by status
     * @param string $status Status filter
     * @return int Count of referrals
     */
    public function count_by_status($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results('referrals');
    }

    /**
     * Get referral statistics for a user
     * @param string $user_id User ID (referrer)
     * @return array Statistics including count and total rewards
     */
    public function get_user_stats($user_id)
    {
        $this->db->select('
            COUNT(*) as total_referrals,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_referrals,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_referrals,
            SUM(reward_points) as total_points,
            SUM(reward_amount) as total_earned
        ');
        $this->db->where('referrer_id', $user_id);
        $result = $this->db->get('referrals')->row();
        return (array)$result;
    }

    /**
     * Delete bulk referrals
     * @param array $ids Array of referral IDs
     * @return bool Delete result
     */
    public function delete_bulk($ids)
    {
        if (empty($ids)) {
            return false;
        }
        $this->db->where_in('id', $ids);
        return $this->db->delete('referrals');
    }

    /**
     * Update status for bulk referrals
     * @param array $ids Array of referral IDs
     * @param string $status New status
     * @return bool Update result
     */
    public function update_status_bulk($ids, $status)
    {
        if (empty($ids)) {
            return false;
        }
        $this->db->where_in('id', $ids);
        return $this->db->update('referrals', array('status' => $status));
    }

    /**
     * Generate unique referral code
     * @return string Unique referral code
     */
    public function generate_code()
    {
        $code = 'REF' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // Ensure uniqueness
        while ($this->is_code_exists($code)) {
            $code = 'REF' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        }

        return $code;
    }
}
