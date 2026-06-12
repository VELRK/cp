<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Housing_news_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($category = null, $limit = null, $offset = 0)
    {
        if (!empty($category)) {
            $this->db->where('category', $category);
        }
        $this->db->order_by('createdAt', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('housing_news')->result();
    }

    public function count_all($category = null)
    {
        if (!empty($category)) {
            $this->db->where('category', $category);
        }
        return (int) $this->db->count_all_results('housing_news');
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('housing_news', array('id' => (int) $id))->row();
    }

    public function create($data)
    {
        $this->db->insert('housing_news', $data);
        return (int) $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int) $id)->update('housing_news', $data);
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('housing_news');
        return $this->db->affected_rows() > 0;
    }
}

