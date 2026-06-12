<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_city_model extends CI_Model {

    protected $table = 'nb_cities';

    public function all_active()
    {
        return $this->db->where('is_active', 1)
            ->order_by('sort_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, array('id' => (int) $id))->row();
    }

    public function count_all()
    {
        return (int) $this->db->count_all($this->table);
    }

    /** @return array<int, object> */
    public function admin_all()
    {
        return $this->db->order_by('sort_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
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

    /**
     * Properties, users, or localities referencing this city.
     */
    public function count_references($city_id)
    {
        $city_id = (int) $city_id;
        $p = (int) $this->db->where('city_id', $city_id)->count_all_results('nb_properties');
        $u = (int) $this->db->where('city_id', $city_id)->count_all_results('nb_users');
        $l = (int) $this->db->where('city_id', $city_id)->count_all_results('nb_localities');
        return $p + $u + $l;
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id);
        return $this->db->delete($this->table);
    }
}
