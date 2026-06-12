<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Location_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($status = null, $city_id = null)
    {
        if ($status) {
            $this->db->where('locations.status', $status);
        }
        if ($city_id) {
            $this->db->where('locations.city_id', $city_id);
        }
        $this->db->select('locations.*, cities.name as city_name');
        $this->db->from('locations');
        $this->db->join('cities', 'cities.id = locations.city_id', 'left');
        $this->db->order_by('locations.`order`', 'ASC');
        $this->db->order_by('locations.name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('locations.*, cities.name as city_name');
        $this->db->from('locations');
        $this->db->join('cities', 'cities.id = locations.city_id', 'left');
        $this->db->where('locations.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_city($city_id, $status = 'active')
    {
        $this->db->where('city_id', $city_id);
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('`order`', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get('locations')->result();
    }

    public function create($data)
    {
        // Remove image field if column doesn't exist (for backward compatibility)
        if (isset($data['image'])) {
            // Check if image column exists
            $fields = $this->db->list_fields('locations');
            if (!in_array('image', $fields)) {
                unset($data['image']);
            }
        }
        
        // Set default order if not provided and column exists
        $fields = $this->db->list_fields('locations');
        if (in_array('order', $fields) && !isset($data['order'])) {
            $this->db->select_max('order');
            $maxOrder = $this->db->get('locations')->row();
            $data['order'] = ($maxOrder && $maxOrder->order !== null) ? (int)$maxOrder->order + 1 : 1;
        }
        
        $this->db->insert('locations', $data);
        $insert_id = $this->db->insert_id();
        
        if ($this->db->affected_rows() > 0) {
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        // Remove image field if column doesn't exist (for backward compatibility)
        if (isset($data['image'])) {
            // Check if image column exists
            $fields = $this->db->list_fields('locations');
            if (!in_array('image', $fields)) {
                unset($data['image']);
            }
        }
        
        $this->db->where('id', $id);
        $this->db->update('locations', $data);
        return $this->db->affected_rows() >= 0; // Returns true even if no changes (0 rows affected)
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('locations');
    }

    public function get_locations_with_property_count($limit = 0)
    {
        // Get location names, images, and property counts from database
        // Join locations table to get images, count active properties grouped by location
        $this->db->select('properties.location as location, locations.image as image, COUNT(properties.id) as property_count');
        $this->db->from('properties');
        $this->db->join('locations', 'locations.name = properties.location', 'inner');
        $this->db->where('properties.status', 'active');
        $this->db->group_by('properties.location');
        $this->db->order_by('property_count', 'DESC');
        if ($limit > 0) {
           $this->db->limit($limit);
        }
        return $this->db->get()->result();
    }

    public function get_all_with_property_count($status = 'active', $city_id = null)
    {
        if (!$this->db->table_exists('locations')) {
            return array();
        }
        if (!$this->db->table_exists('properties')) {
            return array();
        }
        // Get all locations with actual property counts from properties table
        $this->db->select('locations.*, cities.name as city_name');
        $this->db->select('IFNULL((SELECT COUNT(*) FROM properties 
                           WHERE properties.location = locations.name 
                           AND (cities.name IS NULL OR properties.city = cities.name)
                           AND properties.status = "active"), 0) as property_count', FALSE);
        $this->db->from('locations');
        $this->db->join('cities', 'cities.id = locations.city_id', 'left');
        if ($status) {
            $this->db->where('locations.status', $status);
        }
        if ($city_id) {
            $this->db->where('locations.city_id', (int)$city_id);
        }
        $this->db->order_by('locations.`order`', 'ASC');
        $this->db->order_by('property_count', 'DESC');
        $this->db->order_by('locations.name', 'ASC');
        return $this->db->get()->result();
    }
    
    public function update_order($id, $order)
    {
        // Check if order column exists
        $fields = $this->db->list_fields('locations');
        if (!in_array('order', $fields)) {
            return false; // Column doesn't exist
        }
        
        $this->db->where('id', $id);
        $this->db->update('locations', array('order' => (int)$order));
        return $this->db->affected_rows() >= 0;
    }
    
    public function update_orders($orders)
    {
        // Update multiple locations' order values
        // $orders should be an array of ['id' => order_value]
        if (empty($orders) || !is_array($orders)) {
            log_message('error', 'Location update_orders: Invalid orders data');
            return false;
        }
        
        // Check if order column exists
        $fields = $this->db->list_fields('locations');
        if (!in_array('order', $fields)) {
            log_message('error', 'Location update_orders: Order column does not exist');
            return false; // Column doesn't exist
        }
        
        $this->db->trans_start();
        $success = true;
        
        foreach ($orders as $id => $order) {
            // Handle both string and numeric IDs
            $id = (int)$id;
            $orderValue = (int)$order;
            
            if ($id <= 0) {
                log_message('error', 'Location update_orders: Invalid ID: ' . var_export($id, true));
                $success = false;
                continue;
            }
            
            if ($orderValue < 0) {
                log_message('error', 'Location update_orders: Invalid order value: ' . $orderValue);
                $success = false;
                continue;
            }
            
            $this->db->where('id', $id);
            $update_result = $this->db->update('locations', array('order' => $orderValue));
            
            if (!$update_result) {
                $db_error = $this->db->error();
                log_message('error', 'Location update_orders: Failed to update location ID ' . $id . ': ' . json_encode($db_error));
                $success = false;
            } else {
                log_message('debug', 'Location update_orders: Successfully updated location ID ' . $id . ' to order ' . $orderValue);
            }
        }
        
        $this->db->trans_complete();
        
        if (!$this->db->trans_status()) {
            log_message('error', 'Location update_orders: Transaction failed');
            return false;
        }
        
        return $success;
    }
}

