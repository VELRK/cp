<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_enquiry_model extends CI_Model {

    protected $table = 'nb_enquiries';

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, array('id' => (int) $id))->row();
    }

    public function list_for_tenant($tenant_id, $limit = 50)
    {
        $this->db->select('e.*, p.title AS property_title, c.name AS city_name');
        $this->db->from($this->table . ' e');
        $this->db->join('nb_properties p', 'p.id = e.property_id');
        $this->db->join('nb_cities c', 'c.id = p.city_id');
        $this->db->where('e.tenant_id', (int) $tenant_id);
        $this->db->order_by('e.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function list_admin($filters = array(), $limit = 50, $offset = 0)
    {
        $this->db->select('e.*, p.title AS property_title, u.name AS tenant_name, c.name AS city_name');
        $this->db->from($this->table . ' e');
        $this->db->join('nb_properties p', 'p.id = e.property_id');
        $this->db->join('nb_users u', 'u.id = e.tenant_id');
        $this->db->join('nb_cities c', 'c.id = p.city_id');
        if (!empty($filters['status'])) {
            $this->db->where('e.status', $filters['status']);
        }
        $this->db->order_by('e.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_for_owner_property_ids(array $property_ids)
    {
        if (empty($property_ids)) {
            return 0;
        }
        return (int) $this->db->where_in('property_id', $property_ids)->count_all_results($this->table);
    }

    public function recent_for_owner_properties(array $property_ids, $limit = 5)
    {
        if (empty($property_ids)) {
            return array();
        }
        $this->db->select('e.*, p.title AS property_title, u.name AS tenant_name');
        $this->db->from($this->table . ' e');
        $this->db->join('nb_properties p', 'p.id = e.property_id');
        $this->db->join('nb_users u', 'u.id = e.tenant_id');
        $this->db->where_in('e.property_id', $property_ids);
        $this->db->order_by('e.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /** Full enquiry rows for owner panel (property + enquirer contact details). */
    public function list_for_owner($owner_id, $limit = 100)
    {
        $owner_id = (int) $owner_id;
        if ($owner_id < 1) {
            return array();
        }
        $this->db->select(
            'e.id, e.tenant_id, e.property_id, e.message, e.phone, e.email, e.status, e.created_at, e.updated_at, '
            . 'p.title AS property_title, p.slug AS property_slug, p.locality, p.price, p.listing_type, p.property_type, '
            . 'c.name AS city_name, '
            . 'u.name AS tenant_name, u.email AS tenant_email, u.phone AS tenant_phone'
        );
        $this->db->from($this->table . ' e');
        $this->db->join('nb_properties p', 'p.id = e.property_id');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = e.tenant_id');
        $this->db->where('p.owner_id', $owner_id);
        $this->db->order_by('e.created_at', 'DESC');
        $this->db->limit((int) $limit);
        return $this->db->get()->result();
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

    public function count_today()
    {
        $start = date('Y-m-d 00:00:00');
        return (int) $this->db->where('created_at >=', $start)->count_all_results($this->table);
    }
}
