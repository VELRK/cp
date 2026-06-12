<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_property_type_model extends CI_Model {

    private $table = 'nb_property_types';

    public function table_exists()
    {
        return $this->db->table_exists($this->table);
    }

    public function all_active()
    {
        if (!$this->table_exists()) {
            return array();
        }
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function admin_all()
    {
        if (!$this->table_exists()) {
            return array();
        }
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        if (!$this->table_exists()) {
            return null;
        }
        return $this->db->get_where($this->table, array('id' => (int) $id))->row();
    }

    public function get_by_slug($slug)
    {
        if (!$this->table_exists()) {
            return null;
        }
        return $this->db->get_where($this->table, array('slug' => (string) $slug))->row();
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

    public function count_references($slug)
    {
        if (!$this->db->table_exists('nb_properties')) {
            return 0;
        }
        return (int) $this->db->where('property_type', (string) $slug)->count_all_results('nb_properties');
    }
}

