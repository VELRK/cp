<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Property_model extends CI_Model {

    /** @var array<string,bool> */
    protected $properties_column_cache = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->ensure_schema();
    }

    /**
     * Ensure optional legacy fields used by mobile/admin exist.
     */
    protected function ensure_schema()
    {
        if (!$this->db->table_exists('properties')) {
            return;
        }
        if (!$this->db->field_exists('nearby', 'properties')) {
            $this->db->query("ALTER TABLE `properties` ADD COLUMN `nearby` LONGTEXT NULL AFTER `description`");
        }
        if (!$this->db->field_exists('tags_best_rate_localities', 'properties')) {
            $this->db->query("ALTER TABLE `properties` ADD COLUMN `tags_best_rate_localities` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`");
        }
        if (!$this->db->field_exists('tags_high_growth_localities', 'properties')) {
            $this->db->query("ALTER TABLE `properties` ADD COLUMN `tags_high_growth_localities` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tags_best_rate_localities`");
        }
    }

    /**
     * Some installs omit optional columns (e.g. is_latest); avoid SQL 1054.
     */
    protected function properties_has_column($column)
    {
        if (!isset($this->properties_column_cache[$column])) {
            $this->properties_column_cache[$column] = $this->db->field_exists($column, 'properties');
        }
        return $this->properties_column_cache[$column];
    }

    public function get_all($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->get('properties')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('properties', array('id' => $id))->row();
    }

    public function create($data)
    {
        $this->db->insert('properties', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('properties', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('properties');
    }

    public function slug_exists($slug, $exclude_id = null)
    {
        $this->db->where('slug', $slug);
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results('properties') > 0;
    }

    public function count_all($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results('properties');
    }

    public function search($filters = array())
    {
        if (isset($filters['category']) && !empty($filters['category'])) {
            $this->db->where('category', $filters['category']);
        }
        if (isset($filters['property_category']) && !empty($filters['property_category'])) {
            $this->db->where('property_category', $filters['property_category']);
        }
        if (isset($filters['city']) && !empty($filters['city'])) {
            $this->db->where('city', $filters['city']);
        }
        if (isset($filters['location']) && !empty($filters['location'])) {
            $this->db->where('location', $filters['location']);
        }
        if (isset($filters['min_price']) && !empty($filters['min_price'])) {
            $this->db->where('price >=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && !empty($filters['max_price'])) {
            $this->db->where('price <=', $filters['max_price']);
        }
        if (isset($filters['is_featured']) && $filters['is_featured'] == 1 && $this->properties_has_column('is_featured')) {
            $this->db->where('is_featured', 1);
        }
        if (isset($filters['is_latest']) && $filters['is_latest'] == 1 && $this->properties_has_column('is_latest')) {
            $this->db->where('is_latest', 1);
        }
        if (isset($filters['tags_best_rate_localities']) && $filters['tags_best_rate_localities'] == 1 && $this->properties_has_column('tags_best_rate_localities')) {
            $this->db->where('tags_best_rate_localities', 1);
        }
        if (isset($filters['tags_high_growth_localities']) && $filters['tags_high_growth_localities'] == 1 && $this->properties_has_column('tags_high_growth_localities')) {
            $this->db->where('tags_high_growth_localities', 1);
        }
        if (isset($filters['type']) && !empty($filters['type'])) {
            $this->db->where('type', $filters['type']);
        }
        if (isset($filters['listing_type']) && !empty($filters['listing_type'])) {
            $this->db->where('listing_type', $filters['listing_type']);
        }
        if (isset($filters['agent_id']) && $filters['agent_id'] !== '') {
            $this->db->where('agent_id', $filters['agent_id']);
        }
        // Status (default active)
        if (isset($filters['status']) && !empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        } else {
            $this->db->where('status', 'active');
        }
        
        // Sorting
        if (isset($filters['sort_by'])) {
            switch($filters['sort_by']) {
                case 'newest':
                    $this->db->order_by('created_at', 'DESC');
                    break;
                case 'oldest':
                    $this->db->order_by('created_at', 'ASC');
                    break;
                case 'featured':
                    if ($this->properties_has_column('is_featured')) {
                        $this->db->order_by('is_featured', 'DESC');
                    }
                    $this->db->order_by('created_at', 'DESC');
                    break;
                case 'price_low':
                    $this->db->order_by('price', 'ASC');
                    break;
                case 'price_high':
                    $this->db->order_by('price', 'DESC');
                    break;
                case 'latest':
                    if ($this->properties_has_column('is_latest')) {
                        $this->db->order_by('is_latest', 'DESC');
                    }
                    $this->db->order_by('created_at', 'DESC');
                    break;
                default:
                    $this->db->order_by('created_at', 'DESC');
            }
        } else {
            $this->db->order_by('created_at', 'DESC');
        }
        // Pagination (optional)
        if (isset($filters['limit']) && (int)$filters['limit'] > 0) {
            $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
            $this->db->limit((int)$filters['limit'], max(0, $offset));
        }

        return $this->db->get('properties')->result();
    }

    public function get_categories()
    {
        $this->db->select('category');
        $this->db->distinct();
        $this->db->where('status', 'active');
        $this->db->order_by('category', 'ASC');
        return $this->db->get('properties')->result();
    }

    public function get_latest_for_sale($limit = 3)
    {
        $this->db->where('status', 'active');
        if ($this->properties_has_column('is_latest')) {
            $this->db->where('is_latest', 1);
        }
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('properties')->result();
    }

    public function get_featured_properties($limit = 3)
    {
        $this->db->where('status', 'active');
        if ($this->properties_has_column('is_featured')) {
            $this->db->where('is_featured', 1);
        }
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('properties')->result();
    }

    public function get_cities_with_property_count($limit = 6)
    {
        $this->db->select('city, COUNT(*) as property_count, MIN(main_image) as sample_image');
        $this->db->from('properties');
        $this->db->where('status', 'active');
        $this->db->group_by('city');
        $this->db->order_by('property_count', 'DESC');
        $this->db->order_by('city', 'ASC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}

