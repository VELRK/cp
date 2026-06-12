<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile_banner_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->ensure_schema();
    }

    private function ensure_schema()
    {
        // Ensure path column is TEXT (original table has it as int)
        if ($this->db->table_exists('mobile_banner') && $this->db->field_exists('path', 'mobile_banner')) {
            $fields = $this->db->field_data('mobile_banner');
            foreach ($fields as $field) {
                if ($field->name === 'path' && strtolower($field->type) === 'int') {
                    $this->db->query("ALTER TABLE mobile_banner MODIFY COLUMN path TEXT NULL");
                    break;
                }
            }
        }
    }

    public function get_all()
    {
        $this->db->order_by('id', 'DESC');
        return $this->db->get('mobile_banner')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('mobile_banner', array('id' => $id))->row();
    }

    public function get_active()
    {
        return $this->db->get_where('mobile_banner', array('status' => 1))->result();
    }

    public function create($data)
    {
        $this->db->insert('mobile_banner', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('mobile_banner', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('mobile_banner');
    }
}
