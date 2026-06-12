<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Seo_settings_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all()
    {
        return $this->db->order_by('id', 'ASC')->get('seo_settings')->result_array();
    }

    public function get_by_key($page_key)
    {
        return $this->db->where('page_key', $page_key)->where('status', 1)->get('seo_settings')->row_array();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get('seo_settings')->row_array();
    }

    public function update($id, $data)
    {
        return $this->db->where('id', $id)->update('seo_settings', $data);
    }
}
