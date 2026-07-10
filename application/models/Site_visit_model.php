<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site_visit_model extends CI_Model {

    protected $table = 'nb_site_visits';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        nb_ensure_site_visits_table();
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, array('id' => (int) $id))->row();
    }

    public function for_user($user_id, $limit = 50, $offset = 0)
    {
        $this->db->select('sv.*, p.title AS property_title, p.slug AS property_slug, p.locality, c.name AS city_name');
        $this->db->from($this->table . ' sv');
        $this->db->join('nb_properties p', 'p.id = sv.property_id', 'left');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->where('sv.user_id', (int) $user_id);
        $this->db->order_by('sv.scheduled_at', 'DESC');
        $this->db->limit((int) $limit, max(0, (int) $offset));
        return $this->db->get()->result();
    }

    public function for_property_owner($owner_id, $property_id = null, $limit = 50, $offset = 0)
    {
        $this->db->select('sv.*, p.title AS property_title, p.slug AS property_slug, p.locality,
            c.name AS city_name, u.name AS visitor_name, u.phone AS visitor_phone, u.email AS visitor_email');
        $this->db->from($this->table . ' sv');
        $this->db->join('nb_properties p', 'p.id = sv.property_id', 'inner');
        $this->db->join('nb_users u', 'u.id = sv.user_id', 'left');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->where('p.owner_id', (int) $owner_id);
        if ($property_id !== null && (int) $property_id > 0) {
            $this->db->where('sv.property_id', (int) $property_id);
        }
        $this->db->order_by('sv.scheduled_at', 'DESC');
        $this->db->limit((int) $limit, max(0, (int) $offset));
        return $this->db->get()->result();
    }

    public function count_for_owner($owner_id, $status = null)
    {
        $this->db->from($this->table . ' sv');
        $this->db->join('nb_properties p', 'p.id = sv.property_id', 'inner');
        $this->db->where('p.owner_id', (int) $owner_id);
        if ($status !== null && $status !== '') {
            $this->db->where('sv.status', $status);
        }
        return (int) $this->db->count_all_results();
    }

    public function owner_can_manage($visit_id, $owner_id)
    {
        $this->db->select('sv.id');
        $this->db->from($this->table . ' sv');
        $this->db->join('nb_properties p', 'p.id = sv.property_id', 'inner');
        $this->db->where('sv.id', (int) $visit_id);
        $this->db->where('p.owner_id', (int) $owner_id);
        return $this->db->get()->row() !== null;
    }

    public function update_status($id, $status)
    {
        $allowed = array('pending', 'confirmed', 'cancelled', 'completed');
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $this->db->where('id', (int) $id);
        return $this->db->update($this->table, array('status' => $status));
    }

    public function list_admin($filters = array(), $limit = 100, $offset = 0)
    {
        $this->db->select('sv.*, p.title AS property_title, p.slug AS property_slug, p.locality,
            u.name AS visitor_name, u.phone AS visitor_phone, u.email AS visitor_email,
            c.name AS city_name, o.name AS owner_name');
        $this->db->from($this->table . ' sv');
        $this->db->join('nb_properties p', 'p.id = sv.property_id', 'left');
        $this->db->join('nb_users u', 'u.id = sv.user_id', 'left');
        $this->db->join('nb_users o', 'o.id = p.owner_id', 'left');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        if (!empty($filters['status'])) {
            $this->db->where('sv.status', $filters['status']);
        }
        $this->db->order_by('sv.scheduled_at', 'DESC');
        $this->db->limit((int) $limit, max(0, (int) $offset));
        return $this->db->get()->result();
    }

    public function get_admin_detail($id)
    {
        $this->db->select('sv.*, p.title AS property_title, p.slug AS property_slug, p.locality, p.owner_id,
            u.name AS visitor_name, u.phone AS visitor_phone, u.email AS visitor_email,
            c.name AS city_name, o.name AS owner_name, o.phone AS owner_phone, o.email AS owner_email');
        $this->db->from($this->table . ' sv');
        $this->db->join('nb_properties p', 'p.id = sv.property_id', 'left');
        $this->db->join('nb_users u', 'u.id = sv.user_id', 'left');
        $this->db->join('nb_users o', 'o.id = p.owner_id', 'left');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->where('sv.id', (int) $id);
        return $this->db->get()->row();
    }

    public function update($id, array $data)
    {
        $this->db->where('id', (int) $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id);
        return $this->db->delete($this->table);
    }
}
