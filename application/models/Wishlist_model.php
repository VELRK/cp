<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wishlist_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all wishlist items
     * @return array Array of wishlist objects
     */
    public function get_all()
    {
        $userJoin = $this->resolve_user_join();
        $propCols = $this->resolve_property_cols();
        $select = 'w.*';
        if ($propCols) {
            $select .= ', p.' . $propCols['name_col'] . ' as property_name_db, p.' . $propCols['price_col'] . ' as property_price_db';
            if ($propCols['category_col'] !== null) {
                $select .= ', p.' . $propCols['category_col'] . ' as property_category';
            }
            if ($propCols['image_col'] !== null) {
                $select .= ', p.' . $propCols['image_col'] . ' as property_image_db';
            }
            if (!empty($propCols['has_images_json'])) {
                $select .= ', p.images as property_images_json_raw';
            }
        }
        if ($userJoin) {
            $select .= ', u.' . $userJoin['name_col'] . ' as user_name, u.email as user_email, u.phone as user_phone';
        }
        $this->db->select($select);
        $this->db->from('wishlists w');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u', 'w.user_id = u.id', 'left');
        }
        if ($propCols) {
            $this->db->join($propCols['table'] . ' p', 'w.property_id = p.id', 'left');
        }
        $this->db->order_by('w.created_at', 'DESC');
        $rows = $this->db->get()->result();
        if ($propCols) {
            $this->hydrate_wishlist_property_fields($rows, $propCols);
        }
        return $rows;
    }

    /**
     * Get wishlist by ID
     * @param string $id Wishlist ID
     * @return object Wishlist object or null
     */
    public function get_by_id($id)
    {
        $userJoin = $this->resolve_user_join();
        $propCols = $this->resolve_property_cols();
        $select = 'w.*';
        if ($propCols) {
            $select .= ', p.' . $propCols['name_col'] . ' as property_name_db, p.' . $propCols['price_col'] . ' as property_price_db';
            if ($propCols['category_col'] !== null) {
                $select .= ', p.' . $propCols['category_col'] . ' as property_category';
            }
            if ($propCols['image_col'] !== null) {
                $select .= ', p.' . $propCols['image_col'] . ' as property_image_db';
            }
            if (!empty($propCols['has_images_json'])) {
                $select .= ', p.images as property_images_json_raw';
            }
        }
        if ($userJoin) {
            $select .= ', u.' . $userJoin['name_col'] . ' as user_name, u.email as user_email, u.phone as user_phone';
        }
        $this->db->select($select);
        $this->db->from('wishlists w');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u', 'w.user_id = u.id', 'left');
        }
        if ($propCols) {
            $this->db->join($propCols['table'] . ' p', 'w.property_id = p.id', 'left');
        }
        $this->db->where('w.id', $id);
        $row = $this->db->get()->row();
        if ($row && $propCols) {
            $this->hydrate_wishlist_property_fields(array($row), $propCols);
        }
        return $row;
    }

    /**
     * Get wishlist items for a specific user
     * @param string $user_id User ID
     * @param int $limit Optional limit
     * @param int $offset Optional offset
     * @return array Array of wishlist objects
     */
    public function get_by_user($user_id, $limit = null, $offset = 0)
    {
        $propCols = $this->resolve_property_cols();
        $select = 'w.*';
        $pt = $propCols ? $propCols['table'] : null;
        $hasLocality = false;
        $hasCityId = false;
        $hasCityName = false;
        if ($propCols && $pt) {
            $select .= ', p.' . $propCols['name_col'] . ' as property_name_db, p.' . $propCols['price_col'] . ' as property_price_db';
            if ($propCols['category_col'] !== null) {
                $select .= ', p.' . $propCols['category_col'] . ' as property_category';
            }
            if ($propCols['image_col'] !== null) {
                $select .= ', p.' . $propCols['image_col'] . ' as property_image_db';
            }
            if (!empty($propCols['has_images_json'])) {
                $select .= ', p.images as property_images_json_raw';
            }
            if (!empty($propCols['has_slug'])) {
                $select .= ', p.slug as property_slug_db';
            }
            $hasLocality = $this->db->field_exists('locality', $pt);
            $hasCityId = $this->db->field_exists('city_id', $pt);
            $hasCityName = $this->db->field_exists('city', $pt);
            if ($hasLocality) {
                $select .= ', p.locality as property_locality_db';
            }
            if ($hasCityName) {
                $select .= ', p.city as property_city_name_db';
            } elseif ($hasCityId && $this->db->table_exists('nb_cities')) {
                $select .= ', nc.name as property_city_name_db';
            } elseif ($hasCityId && $this->db->table_exists('cities')) {
                $select .= ', nc.name as property_city_name_db';
            }
        }
        $this->db->select($select);
        $this->db->from('wishlists w');
        if ($propCols && $pt) {
            $this->db->join($pt . ' p', 'w.property_id = p.id', 'left');
            if (!$hasCityName && $hasCityId && $this->db->table_exists('nb_cities')) {
                $this->db->join('nb_cities nc', 'p.city_id = nc.id', 'left');
            } elseif (!$hasCityName && $hasCityId && $this->db->table_exists('cities')) {
                $this->db->join('cities nc', 'p.city_id = nc.id', 'left');
            }
        }
        $this->db->where('w.user_id', $user_id);

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $this->db->order_by('w.created_at', 'DESC');
        $rows = $this->db->get()->result();
        if ($propCols) {
            $this->hydrate_wishlist_property_fields($rows, $propCols);
        }
        return $rows;
    }

    /**
     * Get wishlist items for a property
     * @param string $property_id Property ID
     * @return array Array of wishlist objects
     */
    public function get_by_property($property_id)
    {
        $userJoin = $this->resolve_user_join();
        $select = 'w.*';
        if ($userJoin) {
            $select .= ', u.' . $userJoin['name_col'] . ' as user_name, u.email as user_email, u.phone as user_phone';
        }
        $this->db->select($select);
        $this->db->from('wishlists w');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u', 'w.user_id = u.id', 'left');
        }
        $this->db->where('w.property_id', $property_id);
        $this->db->order_by('w.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get one wishlist row by user + property.
     *
     * @param string $user_id
     * @param int $property_id
     * @return object|null
     */
    public function get_row_by_user_property($user_id, $property_id)
    {
        $propCols = $this->resolve_property_cols();
        $select = 'w.*';
        if ($propCols) {
            $select .= ', p.' . $propCols['name_col'] . ' as property_name_db, p.' . $propCols['price_col'] . ' as property_price_db';
            if ($propCols['category_col'] !== null) {
                $select .= ', p.' . $propCols['category_col'] . ' as property_category';
            }
            if ($propCols['image_col'] !== null) {
                $select .= ', p.' . $propCols['image_col'] . ' as property_image_db';
            }
            if (!empty($propCols['has_images_json'])) {
                $select .= ', p.images as property_images_json_raw';
            }
        }
        $this->db->select($select);
        $this->db->from('wishlists w');
        if ($propCols) {
            $this->db->join($propCols['table'] . ' p', 'w.property_id = p.id', 'left');
        }
        $this->db->where('w.user_id', $user_id);
        $this->db->where('w.property_id', (int) $property_id);
        $this->db->order_by('w.created_at', 'DESC');
        $row = $this->db->get()->row();
        if ($row && $propCols) {
            $this->hydrate_wishlist_property_fields(array($row), $propCols);
        }
        return $row;
    }

    /**
     * Create a new wishlist entry
     * @param array $data Wishlist data
     * @return string Inserted ID or false
     */
    public function create($data)
    {
        if ($this->db->insert('wishlists', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update wishlist entry
     * @param string $id Wishlist ID
     * @param array $data Update data
     * @return bool Update result
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('wishlists', $data);
    }

    /**
     * Delete wishlist entry
     * @param string $id Wishlist ID
     * @return bool Delete result
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('wishlists');
    }

    /**
     * Check if item exists in wishlist
     * @param string $user_id User ID
     * @param string $property_id Property ID
     * @return bool True if exists
     */
    public function is_wishlisted($user_id, $property_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('property_id', $property_id);
        return $this->db->count_all_results('wishlists') > 0;
    }

    /**
     * Get count of wishlist items for a user
     * @param string $user_id User ID
     * @return int Count of items
     */
    public function count_by_user($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results('wishlists');
    }

    /**
     * Get count of wishlists for a property
     * @param string $property_id Property ID
     * @return int Count of wishlist entries
     */
    public function count_by_property($property_id)
    {
        $this->db->where('property_id', $property_id);
        return $this->db->count_all_results('wishlists');
    }

    /**
     * Remove item from wishlist (alias for delete)
     * @param string $user_id User ID
     * @param string $property_id Property ID
     * @return bool Result
     */
    public function remove_from_wishlist($user_id, $property_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('property_id', $property_id);
        return $this->db->delete('wishlists');
    }

    /**
     * Get total count of all wishlist items
     * @return int Total count
     */
    public function count_all()
    {
        return $this->db->count_all_results('wishlists');
    }

    /**
     * Get most wishlisted properties
     * @param int $limit Limit results
     * @return array Array of wishlisted properties with counts
     */
    public function get_popular_properties($limit = 10)
    {
        $this->db->select('
            w.property_id,
            w.property_name,
            w.property_image,
            w.property_price,
            w.property_location,
            COUNT(*) as wishlist_count
        ');
        $this->db->from('wishlists w');
        $this->db->group_by('w.property_id');
        $this->db->order_by('wishlist_count', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Delete bulk wishlist items
     * @param array $ids Array of wishlist IDs
     * @return bool Delete result
     */
    public function delete_bulk($ids)
    {
        if (empty($ids)) {
            return false;
        }
        $this->db->where_in('id', $ids);
        return $this->db->delete('wishlists');
    }

    /**
     * Clear all wishlist items for a user
     * @param string $user_id User ID
     * @return bool Result
     */
    public function clear_user_wishlist($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->delete('wishlists');
    }

    /**
     * Search wishlist items
     * @param string $search Search keyword
     * @param string $user_id Optional filter by user
     * @return array Search results
     */
    public function search($search, $user_id = null)
    {
        $userJoin = $this->resolve_user_join();
        $select = 'w.*';
        if ($userJoin) {
            $select .= ', u.' . $userJoin['name_col'] . ' as user_name, u.email as user_email, u.phone as user_phone';
        }
        $this->db->select($select);
        $this->db->from('wishlists w');
        if ($userJoin) {
            $this->db->join($userJoin['table'] . ' u', 'w.user_id = u.id', 'left');
        }

        $this->db->like('w.property_name', $search);
        $this->db->or_like('w.property_location', $search);
        if ($userJoin) {
            $this->db->or_like('u.' . $userJoin['name_col'], $search);
        }

        if ($user_id) {
            $this->db->where('w.user_id', $user_id);
        }

        $this->db->order_by('w.created_at', 'DESC');
        return $this->db->get()->result();
    }

    private function resolve_user_join()
    {
        if ($this->db->table_exists('nb_users')) {
            $nameCol = $this->db->field_exists('fullname', 'nb_users') ? 'fullname' : 'name';
            return array('table' => 'nb_users', 'name_col' => $nameCol);
        }
        if ($this->db->table_exists('users')) {
            $nameCol = $this->db->field_exists('fullname', 'users') ? 'fullname' : 'name';
            return array('table' => 'users', 'name_col' => $nameCol);
        }
        return null;
    }

    /**
     * Nobroker listings use nb_properties; legacy installs may use properties only.
     */
    private function resolve_property_table()
    {
        if ($this->db->table_exists('nb_properties')) {
            return 'nb_properties';
        }
        if ($this->db->table_exists('properties')) {
            return 'properties';
        }
        return null;
    }

    private function resolve_property_cols()
    {
        $table = $this->resolve_property_table();
        if ($table === null) {
            return null;
        }
        $nameCol = null;
        foreach (array('name', 'propertyName', 'propertyname', 'title') as $c) {
            if ($this->db->field_exists($c, $table)) {
                $nameCol = $c;
                break;
            }
        }
        $priceCol = null;
        foreach (array('price', 'propertyPriceRange', 'propertypricerange') as $c) {
            if ($this->db->field_exists($c, $table)) {
                $priceCol = $c;
                break;
            }
        }
        if ($nameCol === null || $priceCol === null) {
            return null;
        }
        $categoryCol = null;
        foreach (array('category', 'category_id', 'property_type', 'listing_type') as $c) {
            if ($this->db->field_exists($c, $table)) {
                $categoryCol = $c;
                break;
            }
        }
        $imageCol = null;
        if ($this->db->field_exists('main_image', $table)) {
            $imageCol = 'main_image';
        }
        return array(
            'table' => $table,
            'name_col' => $nameCol,
            'price_col' => $priceCol,
            'category_col' => $categoryCol,
            'image_col' => $imageCol,
            'has_images_json' => $this->db->field_exists('images', $table),
            'has_slug' => $this->db->field_exists('slug', $table),
        );
    }

    /**
     * Prefer joined listing image over wishlist snapshot; parse first URL from images JSON when needed.
     *
     * @param array $rows
     * @param array $propCols
     */
    private function hydrate_wishlist_property_fields($rows, $propCols)
    {
        if (empty($rows) || !$propCols) {
            return;
        }
        foreach ($rows as $row) {
            $thumb = '';
            if (isset($row->property_image_db) && $row->property_image_db !== null && (string) $row->property_image_db !== '') {
                $thumb = (string) $row->property_image_db;
            }
            if ($thumb === '' && isset($row->property_images_json_raw) && $row->property_images_json_raw !== null && (string) $row->property_images_json_raw !== '') {
                $thumb = $this->parse_first_image_from_json($row->property_images_json_raw);
            }
            if ($thumb !== '') {
                $row->property_image = $thumb;
            }
            if (isset($row->property_image_db)) {
                unset($row->property_image_db);
            }
            if (isset($row->property_images_json_raw)) {
                unset($row->property_images_json_raw);
            }
        }
    }

    /**
     * @param mixed $raw JSON string or decoded array (MySQL JSON column)
     */
    private function parse_first_image_from_json($raw)
    {
        if ($raw === null || $raw === '') {
            return '';
        }
        if (is_array($raw)) {
            $decoded = $raw;
        } else {
            $decoded = json_decode((string) $raw, true);
        }
        if (!is_array($decoded) || empty($decoded)) {
            return '';
        }
        $first = $decoded[0];
        if (is_string($first)) {
            return $first;
        }
        if (is_array($first)) {
            if (!empty($first['url'])) {
                return (string) $first['url'];
            }
            if (!empty($first['src'])) {
                return (string) $first['src'];
            }
        }
        return '';
    }
}
