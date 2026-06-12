<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_amenity_model extends CI_Model {

    protected $table = 'nb_amenities';

    /** @return array<int, object> */
    public function all_active()
    {
        return $this->db->where('is_active', 1)
            ->order_by('sort_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    /** @return array<int, object> */
    public function admin_all()
    {
        return $this->db->order_by('sort_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, array('id' => (int) $id))->row();
    }

    public function get_by_slug($slug)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return null;
        }
        return $this->db->get_where($this->table, array('slug' => $slug))->row();
    }

    /**
     * Active amenity names allowed on property save (name => true).
     *
     * @return array<string, true>
     */
    public function allowed_names_map()
    {
        $rows = $this->db->select('name')
            ->where('is_active', 1)
            ->get($this->table)
            ->result();
        $m = array();
        foreach ($rows as $r) {
            $m[$r->name] = true;
        }
        return $m;
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

    /**
     * Unique slug from name (append -2, -3 if needed).
     *
     * @param int|null $exclude_id
     */
    public function unique_slug($name, $exclude_id = null)
    {
        $this->load->helper('nb');
        $base = nb_slugify($name);
        if ($base === 'property') {
            $base = 'amenity';
        }
        $candidate = $base;
        $n = 0;
        while (true) {
            $this->db->from($this->table);
            $this->db->where('slug', $candidate);
            if ($exclude_id !== null) {
                $this->db->where('id !=', (int) $exclude_id);
            }
            if ($this->db->count_all_results() === 0) {
                return $candidate;
            }
            $n++;
            $candidate = $base . '-' . $n;
        }
    }
}
