<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($status = null)
    {
        if (!$this->db->table_exists('categories')) {
            return array();
        }
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('category_name', 'ASC');
        return $this->db->get('categories')->result();
    }

    public function get_by_id($id)
    {
        if (!$this->db->table_exists('categories')) {
            return null;
        }
        return $this->db->get_where('categories', array('id' => $id))->row();
    }

    public function create($data)
    {
        $this->db->insert('categories', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('categories', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('categories');
    }

    public function get_all_with_property_count($status = 'active', $city = null, $location = null)
    {
        if (!$this->db->table_exists('categories')) {
            return array();
        }
        if (!$this->db->table_exists('properties')) {
            return array();
        }
        $this->db->select('categories.*');
        $this->db->select('(SELECT COUNT(*) FROM properties 
                           WHERE properties.category = categories.category_name 
                           AND (' . ($city !== null ? 'properties.city = ' . $this->db->escape($city) : '1=1') . ')
                           AND (' . ($location !== null ? 'properties.location = ' . $this->db->escape($location) : '1=1') . ')
                           AND properties.status = "active") as property_count', FALSE);
        $this->db->from('categories');
        if ($status) {
            $this->db->where('categories.status', $status);
        }
        $this->db->order_by('property_count', 'DESC');
        $this->db->order_by('categories.category_name', 'ASC');
        return $this->db->get()->result();
    }
}

