<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Offer_banner_model extends CI_Model {

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
        return $this->db->get('offer_banners')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('offer_banners', array('id' => $id))->row();
    }

    public function create($data)
    {
        $this->db->insert('offer_banners', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('offer_banners', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('offer_banners');
    }

    public function get_active()
    {
        $this->db->where('status', 'active');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get('offer_banners')->row();
    }
}

