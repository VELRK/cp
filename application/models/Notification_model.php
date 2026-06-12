<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($status = null)
    {
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('notifications')->result();
    }

    public function get_all_for_admin()
    {
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('notifications')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('notifications', array('id' => $id))->row();
    }

    public function create($data)
    {
        $this->db->insert('notifications', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('notifications', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('notifications');
    }

    public function get_active()
    {
        return $this->db->get_where('notifications', ['status' => 'active'])->result();
    }

    public function count_all($status = null)
    {
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results('notifications');
    }
}
