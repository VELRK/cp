<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Banner_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Legacy compatibility: some DBs use `banner` instead of `banners`.
     */
    private function table_name()
    {
        if ($this->db->table_exists('banners')) {
            return 'banners';
        }
        if ($this->db->table_exists('banner')) {
            return 'banner';
        }
        return null;
    }

    public function get_all($status = null)
    {
        $table = $this->table_name();
        if ($table === null) {
            return array();
        }
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->apply_default_order($table);
        return $this->db->get($table)->result();
    }

    public function get_all_for_admin()
    {
        $table = $this->table_name();
        if ($table === null) {
            return array();
        }
        $this->apply_default_order($table);
        return $this->db->get($table)->result();
    }

    public function get_by_id($id)
    {
        $table = $this->table_name();
        if ($table === null) {
            return null;
        }
        return $this->db->get_where($table, array('id' => $id))->row();
    }

    public function create($data)
    {
        $table = $this->table_name();
        if ($table === null) {
            return 0;
        }
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $table = $this->table_name();
        if ($table === null) {
            return false;
        }
        $this->db->where('id', $id);
        return $this->db->update($table, $data);
    }

    public function delete($id)
    {
        $table = $this->table_name();
        if ($table === null) {
            return false;
        }
        $this->db->where('id', $id);
        return $this->db->delete($table);
    }

    public function get_active()
    {
        $table = $this->table_name();
        if ($table === null) {
            return array();
        }
        return $this->db->get_where($table, ['status' => 'active'])->result();
    }

    /**
     * Keep compatibility with older schemas where created_at is missing.
     */
    private function apply_default_order($table)
    {
        if ($this->db->field_exists('createdAt', $table)) {
            $this->db->order_by('createdAt', 'DESC');
            return;
        }
        if ($this->db->field_exists('created_at', $table)) {
            $this->db->order_by('created_at', 'DESC');
            return;
        }
        if ($this->db->field_exists('id', $table)) {
            $this->db->order_by('id', 'DESC');
        }
    }
}

