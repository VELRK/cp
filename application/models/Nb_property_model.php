<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_property_model extends CI_Model {

    protected $table = 'nb_properties';

    public function get_by_id($id)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.id', (int) $id);
        return $this->db->get()->row();
    }

    /**
     * @param string $slug URL segment (already decoded)
     */
    public function get_by_slug($slug)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return null;
        }
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.slug', $slug);
        return $this->db->get()->row();
    }

    /**
     * @param mixed $slug DB value
     * @return bool True if NULL, empty string, or whitespace only
     */
    public function slug_is_empty($slug)
    {
        return $slug === null || trim((string) $slug) === '';
    }

    /**
     * True when slug is the legacy SQL backfill pattern `property-{id}` (not title-based).
     */
    public function slug_is_legacy_property_id_fallback($slug)
    {
        $s = trim((string) $slug);
        return $s !== '' && preg_match('#^property-\d+$#', $s) === 1;
    }

    /**
     * On publish / repair: set slug from title when missing, or replace legacy `property-{id}`.
     *
     * @param object $p Row with id, title, slug
     * @return array<string,string>
     */
    public function slug_publish_patch($p)
    {
        if (!$this->db->field_exists('slug', $this->table)) {
            return array();
        }
        $title = isset($p->title) ? $p->title : '';
        $cur = isset($p->slug) ? $p->slug : null;
        if ($this->slug_is_empty($cur)) {
            return array('slug' => $this->unique_slug($title, (int) $p->id));
        }
        if ($this->slug_is_legacy_property_id_fallback($cur)) {
            return array('slug' => $this->unique_slug($title, (int) $p->id));
        }
        return array();
    }

    /**
     * @deprecated Use slug_publish_patch
     */
    public function slug_update_if_empty($p)
    {
        return $this->slug_publish_patch($p);
    }

    /**
     * Rebuild every listing slug from its title (SEO). Oldest id first so stable uniqueness.
     *
     * @return int Rows updated
     */
    public function backfill_all_slugs_from_titles()
    {
        if (!$this->db->field_exists('slug', $this->table)) {
            return 0;
        }
        $this->db->select('id, title')->from($this->table)->order_by('id', 'ASC');
        $rows = $this->db->get()->result();
        $n = 0;
        foreach ($rows as $r) {
            $slug = $this->unique_slug($r->title, (int) $r->id);
            $this->db->where('id', (int) $r->id)->update($this->table, array('slug' => $slug));
            $n++;
        }
        return $n;
    }

    /**
     * Unique slug for title (append -2, -3 if needed).
     *
     * @param int|null $exclude_id Pass when updating
     */
    public function unique_slug($title, $exclude_id = null)
    {
        $this->load->helper('nb');
        $base = nb_slugify($title);
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
            $suffix = '-' . $n;
            $max = 255 - strlen($suffix);
            $candidate = ($max > 0 ? substr($base, 0, $max) : 'p') . $suffix;
        }
    }

    public function increment_views($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->set('views', 'views+1', false);
        return $this->db->update($this->table);
    }

    public function get_best_rated($limit = 0, $offset = 0)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.tags_best_rate_localities', 1);
        $this->db->order_by('p.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit, max(0, $offset));
        }
        return $this->db->get()->result();
    }

    public function get_high_growth($limit = 0, $offset = 0)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.tags_high_growth_localities', 1);
        $this->db->order_by('p.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit, max(0, $offset));
        }
        return $this->db->get()->result();
    }

    public function get_all_active($limit = 0, $offset = 0)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.is_active', 1);
        $this->db->order_by('p.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit, max(0, $offset));
        }
        return $this->db->get()->result();
    }

    public function featured($limit = 6, $offset = 0)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.is_featured', 1);
        $this->db->order_by('p.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit, max(0, $offset));
        }
        return $this->db->get()->result();
    }

    public function similar($city_id, $property_type, $exclude_id, $limit = 4)
    {
        $this->db->select('p.*, c.name AS city_name');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.city_id', (int) $city_id);
        $this->db->where('p.property_type', $property_type);
        $this->db->where('p.id !=', (int) $exclude_id);
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function search($filters, $limit = 0, $offset = 0)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.is_active', 1);

        if (!empty($filters['city_id'])) {
            $this->db->where('p.city_id', (int) $filters['city_id']);
        }
        if (!empty($filters['property_type'])) {
            $this->db->where('p.property_type', $filters['property_type']);
        }
        if (!empty($filters['property_types_in']) && is_array($filters['property_types_in'])) {
            $this->db->where_in('p.property_type', $filters['property_types_in']);
        }
        if (!empty($filters['property_types_not_in']) && is_array($filters['property_types_not_in'])) {
            $this->db->where_not_in('p.property_type', $filters['property_types_not_in']);
        }
        if (!empty($filters['listing_type'])) {
            $this->db->where('p.listing_type', $filters['listing_type']);
        }
        if (isset($filters['owner_id']) && $filters['owner_id'] !== '' && $filters['owner_id'] !== null) {
            $this->db->where('p.owner_id', (int) $filters['owner_id']);
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $this->db->where('p.price >=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $this->db->where('p.price <=', (float) $filters['max_price']);
        }
        if (!empty($filters['bedrooms'])) {
            $this->db->where('p.bedrooms', (int) $filters['bedrooms']);
        }
        if (!empty($filters['tags_best_rate_localities'])) {
            $this->db->where('p.tags_best_rate_localities', 1);
        }
        if (!empty($filters['tags_high_growth_localities'])) {
            $this->db->where('p.tags_high_growth_localities', 1);
        }
        if (!empty($filters['is_featured'])) {
            $this->db->where('p.is_featured', 1);
        }
        $this->apply_search_location_filter($filters);

        $sort = isset($filters['sort']) ? $filters['sort'] : 'new';
        if ($sort === 'price_asc') {
            $this->db->order_by('p.price', 'ASC');
        } elseif ($sort === 'price_desc') {
            $this->db->order_by('p.price', 'DESC');
        } else {
            $this->db->order_by('p.created_at', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit, max(0, $offset));
        }
        return $this->db->get()->result();
    }

    public function count_search($filters)
    {
        $this->db->from($this->table . ' p');
        $this->db->where('p.is_active', 1);
        if (!empty($filters['city_id'])) {
            $this->db->where('p.city_id', (int) $filters['city_id']);
        }
        if (!empty($filters['property_type'])) {
            $this->db->where('p.property_type', $filters['property_type']);
        }
        if (!empty($filters['property_types_in']) && is_array($filters['property_types_in'])) {
            $this->db->where_in('p.property_type', $filters['property_types_in']);
        }
        if (!empty($filters['property_types_not_in']) && is_array($filters['property_types_not_in'])) {
            $this->db->where_not_in('p.property_type', $filters['property_types_not_in']);
        }
        if (!empty($filters['listing_type'])) {
            $this->db->where('p.listing_type', $filters['listing_type']);
        }
        if (isset($filters['owner_id']) && $filters['owner_id'] !== '' && $filters['owner_id'] !== null) {
            $this->db->where('p.owner_id', (int) $filters['owner_id']);
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $this->db->where('p.price >=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $this->db->where('p.price <=', (float) $filters['max_price']);
        }
        if (!empty($filters['bedrooms'])) {
            $this->db->where('p.bedrooms', (int) $filters['bedrooms']);
        }
        $this->apply_search_location_filter($filters);
        return (int) $this->db->count_all_results();
    }

    /**
     * Google Places lat/lng + radius (km) narrows listings; otherwise text q on locality/address.
     */
    protected function apply_search_location_filter($filters)
    {
        $geo = $this->parse_search_geo($filters);
        if ($geo !== null) {
            $lat = $geo['lat'];
            $lng = $geo['lng'];
            $km = $geo['km'];
            $this->db->where('p.latitude IS NOT NULL');
            $this->db->where('p.longitude IS NOT NULL');
            $expr = '(6371 * ACOS(GREATEST(-1, LEAST(1, COS(RADIANS(' . $lat . ')) * COS(RADIANS(p.latitude)) * COS(RADIANS(p.longitude) - RADIANS(' . $lng . ')) + SIN(RADIANS(' . $lat . ')) * SIN(RADIANS(p.latitude))))))';
            $this->db->where($expr . ' <= ' . $km, null, false);
            return;
        }
        if (!empty($filters['locality_q'])) {
            $this->db->group_start();
            $this->db->like('p.locality', $filters['locality_q']);
            $this->db->or_like('p.address', $filters['locality_q']);
            $this->db->or_like('p.title', $filters['locality_q']);
            $this->db->group_end();
        }
    }

    /**
     * @return array{lat: float, lng: float, km: float}|null
     */
    protected function parse_search_geo($filters)
    {
        if (!isset($filters['lat']) || $filters['lat'] === '' || $filters['lat'] === null) {
            return null;
        }
        if (!isset($filters['lng']) || $filters['lng'] === '' || $filters['lng'] === null) {
            return null;
        }
        $lat = (float) $filters['lat'];
        $lng = (float) $filters['lng'];
        if ($lat < -90.0 || $lat > 90.0 || $lng < -180.0 || $lng > 180.0) {
            return null;
        }
        $km = 15.0;
        if (isset($filters['radius_km']) && $filters['radius_km'] !== '' && $filters['radius_km'] !== null) {
            $km = (float) $filters['radius_km'];
        }
        if ($km < 1) {
            $km = 1;
        }
        if ($km > 100) {
            $km = 100;
        }
        return array('lat' => $lat, 'lng' => $lng, 'km' => $km);
    }

    public function for_owner($owner_id)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.owner_id', (int) $owner_id);
        $this->db->where('p.is_active', 1);
        $this->db->order_by('p.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function for_owner_all($owner_id)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.phone AS owner_phone');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        $this->db->where('p.owner_id', (int) $owner_id);
        $this->db->order_by('p.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
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

    public function count_all_active()
    {
        return (int) $this->db->where('is_active', 1)->count_all_results($this->table);
    }

    /** Draft / awaiting publication */
    public function count_pending_publication()
    {
        return (int) $this->db->where('is_active', 0)->count_all_results($this->table);
    }

    public function ids_for_owner($owner_id)
    {
        $rows = $this->db->select('id')->where('owner_id', (int) $owner_id)->get($this->table)->result();
        $ids = array();
        foreach ($rows as $r) {
            $ids[] = (int) $r->id;
        }
        return $ids;
    }

    public function admin_list($filters = array(), $limit = 30, $offset = 0)
    {
        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.email AS owner_email');
        $this->db->from($this->table . ' p');
        $this->db->join('nb_cities c', 'c.id = p.city_id');
        $this->db->join('nb_users u', 'u.id = p.owner_id');
        if (!empty($filters['city_id'])) {
            $this->db->where('p.city_id', (int) $filters['city_id']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $this->db->where('p.is_active', (int) $filters['is_active']);
        }
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
}
