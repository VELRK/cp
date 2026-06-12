<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class City_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->ensure_schema();
    }

    /**
     * Backward compatibility for installs where cities.image is missing.
     */
    private function ensure_schema()
    {
        if ($this->db->table_exists('cities') && !$this->db->field_exists('image', 'cities')) {
            $this->db->query("ALTER TABLE `cities` ADD COLUMN `image` VARCHAR(500) NULL AFTER `name`");
        }
    }

    private function table_name()
    {
        if ($this->db->table_exists('cities')) {
            return 'cities';
        }
        if ($this->db->table_exists('nb_cities')) {
            return 'nb_cities';
        }
        return 'cities';
    }

    public function get_all($status = null)
    {
        $table = $this->table_name();
        if ($status) {
            if ($this->db->field_exists('status', $table)) {
                $this->db->where('status', $status);
            } elseif ($this->db->field_exists('is_active', $table)) {
                $this->db->where('is_active', strtolower((string) $status) === 'active' ? 1 : 0);
            }
        }
        $this->db->order_by('name', 'ASC');
        return $this->db->get($table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table_name(), array('id' => $id))->row();
    }

    public function create($data)
    {
        $table = $this->table_name();
        if ($this->db->field_exists('is_active', $table) && isset($data['status'])) {
            $data['is_active'] = strtolower((string) $data['status']) === 'active' ? 1 : 0;
            unset($data['status']);
        }
        if (!$this->db->field_exists('image', $table) && isset($data['image'])) {
            unset($data['image']);
        }
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $table = $this->table_name();
        if ($this->db->field_exists('is_active', $table) && isset($data['status'])) {
            $data['is_active'] = strtolower((string) $data['status']) === 'active' ? 1 : 0;
            unset($data['status']);
        }
        if (!$this->db->field_exists('image', $table) && isset($data['image'])) {
            unset($data['image']);
        }
        $this->db->where('id', $id);
        return $this->db->update($table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_name());
    }
}

