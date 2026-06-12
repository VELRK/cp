<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blog_model extends CI_Model {

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
        $this->db->order_by('date', 'DESC');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('blogs')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('blogs', array('id' => $id))->row();
    }

    public function create($data)
    {
        $this->db->insert('blogs', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('blogs', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('blogs');
    }

    public function count_all($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results('blogs');
    }
}

