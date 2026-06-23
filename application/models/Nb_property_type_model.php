<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_property_type_model extends CI_Model {

    private $table = 'nb_property_types';

    public function table_exists()
    {
        return $this->db->table_exists($this->table);
    }

    public function has_parent_column()
    {
        return $this->table_exists() && $this->db->field_exists('parent_id', $this->table);
    }

    public function is_main_type($row)
    {
        if (!$row) {
            return false;
        }
        if (!$this->has_parent_column()) {
            return true;
        }
        return empty($row->parent_id);
    }

    private function apply_admin_order()
    {
        if ($this->has_parent_column()) {
            $this->db->order_by('parent_id IS NULL', 'DESC', false);
            $this->db->order_by('parent_id', 'ASC');
        }
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
    }

    public function all_active()
    {
        if (!$this->table_exists()) {
            return array();
        }
        $this->db->where('is_active', 1);
        $this->apply_admin_order();
        return $this->db->get($this->table)->result();
    }

    public function admin_all()
    {
        if (!$this->table_exists()) {
            return array();
        }
        $this->apply_admin_order();
        return $this->db->get($this->table)->result();
    }

    public function admin_grouped_rows()
    {
        $rows = $this->admin_all();
        $mains = array();
        $subs = array();
        foreach ($rows as $r) {
            if ($this->is_main_type($r)) {
                $mains[] = $r;
            } else {
                $pid = (int) $r->parent_id;
                if (!isset($subs[$pid])) {
                    $subs[$pid] = array();
                }
                $subs[$pid][] = $r;
            }
        }
        return array('mains' => $mains, 'subs' => $subs);
    }

    public function main_types($active_only = false)
    {
        if (!$this->table_exists()) {
            return array();
        }
        if ($this->has_parent_column()) {
            $this->db->where('parent_id IS NULL', null, false);
        }
        if ($active_only) {
            $this->db->where('is_active', 1);
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
        if (!$this->table_exists()) {
            return 0;
        }
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

    public function count_children($parent_id)
    {
        if (!$this->table_exists() || !$this->has_parent_column()) {
            return 0;
        }
        return (int) $this->db->where('parent_id', (int) $parent_id)->count_all_results($this->table);
    }

    public function toggle_active($id)
    {
        $row = $this->get_by_id($id);
        if (!$row) {
            return null;
        }
        $new = empty($row->is_active) ? 1 : 0;
        $this->update($id, array('is_active' => $new));
        return array('is_active' => $new);
    }

    public function format_api_row($row)
    {
        $parent_id = ($this->has_parent_column() && isset($row->parent_id) && $row->parent_id)
            ? (int) $row->parent_id
            : null;
        return array(
            'id' => (int) $row->id,
            'parent_id' => $parent_id,
            'type_level' => $parent_id ? 'sub' : 'main',
            'name' => (string) $row->name,
            'slug' => (string) $row->slug,
            'sort_order' => isset($row->sort_order) ? (int) $row->sort_order : 0,
            'is_active' => !empty($row->is_active) ? 1 : 0,
        );
    }

    public function active_flat()
    {
        $out = array();
        foreach ($this->all_active() as $r) {
            $out[] = $this->format_api_row($r);
        }
        return $out;
    }

    public function active_grouped()
    {
        $items = array();
        $subs_by_parent = array();
        foreach ($this->all_active() as $r) {
            if ($this->is_main_type($r)) {
                $items[(int) $r->id] = $this->format_api_row($r);
                $items[(int) $r->id]['sub_types'] = array();
            } else {
                $pid = (int) $r->parent_id;
                if (!isset($subs_by_parent[$pid])) {
                    $subs_by_parent[$pid] = array();
                }
                $subs_by_parent[$pid][] = $this->format_api_row($r);
            }
        }
        foreach ($subs_by_parent as $pid => $subs) {
            if (isset($items[$pid])) {
                $items[$pid]['sub_types'] = $subs;
            } else {
                foreach ($subs as $sub) {
                    $sub['sub_types'] = array();
                    $items[(int) $sub['id']] = $sub;
                }
            }
        }
        return array_values($items);
    }
}
