<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('contacts')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('contacts', array('id' => $id))->row();
    }

    public function create($data)
    {
        $this->db->insert('contacts', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('contacts', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('contacts');
    }

    public function count_new()
    {
        return $this->db->where('status', 'new')->count_all_results('contacts');
    }
}

