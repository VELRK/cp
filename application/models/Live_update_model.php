<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Live_update_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->ensure_schema();
    }

    private function ensure_schema()
    {
        if ($this->db->table_exists('live_updates') && !$this->db->field_exists('status', 'live_updates')) {
            $this->db->query("ALTER TABLE `live_updates` ADD COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'upcoming' AFTER `description`");
        }
    }

    public function get_all($limit = null, $offset = 0)
    {
        $this->db->order_by('liveTime', 'DESC');
        $this->db->order_by('createdAt', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('live_updates')->result();
    }

    public function count_all()
    {
        return (int) $this->db->count_all('live_updates');
    }

    /**
     * Upcoming-only: scheduled liveTime in the future (not null) and not past expiresAt when that column exists.
     *
     * @param string|null $userId Scope to publisher when non-empty and userId column exists
     */
    private function _apply_upcoming_filters()
    {
        if (!$this->db->field_exists('liveTime', 'live_updates')) {
            $this->db->where('1 = 0', null, false);
            return;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->where('liveTime >', $now);
        $this->db->where('liveTime IS NOT NULL', null, false);
        if ($this->db->field_exists('status', 'live_updates')) {
            $this->db->where('status', 'upcoming');
        }
        if ($this->db->field_exists('expiresAt', 'live_updates')) {
            $this->db->group_start();
            $this->db->where('expiresAt >', $now);
            $this->db->or_where('expiresAt IS NULL', null, false);
            $this->db->group_end();
        }
    }

    private function _apply_status_filter($status = null)
    {
        if (!$this->db->field_exists('status', 'live_updates')) {
            return;
        }
        if ($status === null || trim((string) $status) === '') {
            return;
        }
        $this->db->where('status', trim((string) $status));
    }

    /**
     * @param string|null $userId
     * @param bool $upcoming_only Future liveTime only (and non-expired when expiresAt exists)
     */
    public function get_list($userId = null, $limit = null, $offset = 0, $upcoming_only = false, $status = null)
    {
        if ($this->has_user_column() && $userId !== null && (string) $userId !== '') {
            $this->db->where('userId', (string) $userId);
        }
        $this->_apply_status_filter($status);
        if ($upcoming_only) {
            $this->_apply_upcoming_filters();
        }
        if ($this->db->field_exists('liveTime', 'live_updates')) {
            $this->db->order_by('liveTime', $upcoming_only ? 'ASC' : 'DESC');
        }
        if ($this->db->field_exists('createdAt', 'live_updates')) {
            $this->db->order_by('createdAt', 'DESC');
        }
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('live_updates')->result();
    }

    /**
     * @param string|null $userId
     * @param bool $upcoming_only
     */
    public function count_for_list($userId = null, $upcoming_only = false, $status = null)
    {
        if ($this->has_user_column() && $userId !== null && (string) $userId !== '') {
            $this->db->where('userId', (string) $userId);
        }
        $this->_apply_status_filter($status);
        if ($upcoming_only) {
            $this->_apply_upcoming_filters();
        }
        return (int) $this->db->count_all_results('live_updates');
    }

    /** When live_updates.userId exists, count rows for that publisher; otherwise total rows. */
    public function count_all_for_user($userId)
    {
        if ($this->has_user_column()) {
            $this->db->where('userId', (string) $userId);
        }
        return (int) $this->db->count_all_results('live_updates');
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('live_updates', array('id' => (int) $id))->row();
    }

    public function has_user_column()
    {
        return $this->db->field_exists('userId', 'live_updates');
    }

    public function get_all_by_user($userId, $limit = null, $offset = 0)
    {
        if ($this->has_user_column()) {
            $this->db->where('userId', (string) $userId);
        }
        $this->db->order_by('liveTime', 'DESC');
        $this->db->order_by('createdAt', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('live_updates')->result();
    }

    public function create($data)
    {
        $this->db->insert('live_updates', $data);
        return (int) $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int) $id)->update('live_updates', $data);
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('live_updates');
        return $this->db->affected_rows() > 0;
    }
}

