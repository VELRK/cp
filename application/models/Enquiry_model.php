<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Legacy `enquiries` + `properties` OR Nobroker `nb_enquiries` + `nb_properties`.
 * When `enquiries` does not exist, all reads/writes use `nb_enquiries` (tenant_id = mobile user id).
 */
class Enquiry_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * True when the old table is absent but nb_enquiries exists (typical Dream Villa Makers DB).
     */
    public function using_nb_enquiries()
    {
        if ($this->db->table_exists('enquiries')) {
            return false;
        }
        return $this->db->table_exists('nb_enquiries');
    }

    /**
     * Normalize nb_enquiries row for Api_mobile::_format_enquiry_schema (expects user_id, name).
     *
     * @param object $row
     * @return object
     */
    protected function map_nb_row_to_legacy_shape($row)
    {
        if (!$row) {
            return $row;
        }
        $row->user_id = isset($row->tenant_id) ? $row->tenant_id : null;
        if (!isset($row->name) || $row->name === null || $row->name === '') {
            $row->name = isset($row->tenant_name) ? $row->tenant_name : '';
        }
        return $row;
    }

    protected function map_nb_rows($rows)
    {
        $out = array();
        foreach ($rows as $r) {
            $out[] = $this->map_nb_row_to_legacy_shape($r);
        }
        return $out;
    }

    public function get_all($status = null)
    {
        if ($this->using_nb_enquiries()) {
            $this->db->select('e.*, p.title AS property_name, u.name AS tenant_name');
            $this->db->from('nb_enquiries e');
            $this->db->join('nb_properties p', 'p.id = e.property_id', 'left');
            $this->db->join('nb_users u', 'u.id = e.tenant_id', 'left');
            if ($status) {
                $this->db->where('e.status', $status);
            }
            $this->db->order_by('e.created_at', 'DESC');
            return $this->map_nb_rows($this->db->get()->result());
        }
        if ($status) {
            $this->db->where('enquiries.status', $status);
        }
        $this->db->select('enquiries.*, properties.name as property_name');
        $this->db->from('enquiries');
        $this->db->join('properties', 'properties.id = enquiries.property_id', 'left');
        $this->db->order_by('enquiries.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        if ($this->using_nb_enquiries()) {
            $this->db->select('e.*, p.title AS property_name, u.name AS tenant_name');
            $this->db->from('nb_enquiries e');
            $this->db->join('nb_properties p', 'p.id = e.property_id', 'left');
            $this->db->join('nb_users u', 'u.id = e.tenant_id', 'left');
            $this->db->where('e.id', (int) $id);
            $row = $this->db->get()->row();
            return $this->map_nb_row_to_legacy_shape($row);
        }
        $this->db->select('enquiries.*, properties.name as property_name');
        $this->db->from('enquiries');
        $this->db->join('properties', 'properties.id = enquiries.property_id', 'left');
        $this->db->where('enquiries.id', $id);
        return $this->db->get()->row();
    }

    public function create($data)
    {
        if ($this->using_nb_enquiries()) {
            $tenant_id = isset($data['user_id']) ? (int) $data['user_id'] : 0;
            $property_id = isset($data['property_id']) ? (int) $data['property_id'] : 0;
            if ($tenant_id < 1 || $property_id < 1 || empty($data['message']) || empty($data['email'])) {
                return false;
            }
            $insert = array(
                'tenant_id'   => $tenant_id,
                'property_id' => $property_id,
                'message'     => $data['message'],
                'phone'       => isset($data['phone']) ? $data['phone'] : '',
                'email'       => $data['email'],
                'status'      => isset($data['status']) ? $data['status'] : 'new',
            );
            if ($this->db->insert('nb_enquiries', $insert)) {
                return (int) $this->db->insert_id();
            }
            return false;
        }

        $allowed_fields = array('property_id', 'user_id', 'name', 'email', 'phone', 'city', 'message', 'status');
        $filtered_data  = array();
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $filtered_data[$field] = $data[$field];
            }
        }

        if (empty($filtered_data['name']) || empty($filtered_data['email'])) {
            return false;
        }

        $result = $this->db->insert('enquiries', $filtered_data);

        if ($result) {
            return $this->db->insert_id();
        }
        $error = $this->db->error();
        if (!empty($error['message'])) {
            log_message('error', 'Enquiry insert failed: ' . $error['message']);
        } else {
            log_message('error', 'Enquiry insert failed: Unknown database error');
        }
        return false;
    }

    public function update($id, $data)
    {
        if ($this->using_nb_enquiries()) {
            $this->db->where('id', (int) $id);
            return $this->db->update('nb_enquiries', $data);
        }
        $this->db->where('id', $id);
        return $this->db->update('enquiries', $data);
    }

    public function delete($id)
    {
        if ($this->using_nb_enquiries()) {
            $this->db->where('id', (int) $id);
            return $this->db->delete('nb_enquiries');
        }
        $this->db->where('id', $id);
        return $this->db->delete('enquiries');
    }

    public function count_new()
    {
        if ($this->using_nb_enquiries()) {
            return (int) $this->db->where('status', 'new')->count_all_results('nb_enquiries');
        }
        return $this->db->where('status', 'new')->count_all_results('enquiries');
    }

    /**
     * Get enquiries by user ID (legacy: enquiries.user_id; nb: nb_enquiries.tenant_id)
     *
     * @param int|string $user_id
     * @param string|null $status Optional status filter
     * @return array
     */
    public function get_by_userid($user_id, $status = null)
    {
        if ($this->using_nb_enquiries()) {
            $this->db->select('e.*, p.title AS property_name, p.images AS property_images, u.name AS tenant_name');
            $this->db->from('nb_enquiries e');
            $this->db->join('nb_properties p', 'p.id = e.property_id', 'left');
            $this->db->join('nb_users u', 'u.id = e.tenant_id', 'left');
            $this->db->where('e.tenant_id', (int) $user_id);
            if ($status) {
                $this->db->where('e.status', $status);
            }
            $this->db->order_by('e.created_at', 'DESC');
            return $this->map_nb_rows($this->db->get()->result());
        }
        $this->db->select('enquiries.*, properties.name as property_name, properties.main_image as property_image');
        $this->db->from('enquiries');
        $this->db->join('properties', 'properties.id = enquiries.property_id', 'left');
        $this->db->where('enquiries.user_id', $user_id);

        if ($status) {
            $this->db->where('enquiries.status', $status);
        }

        $this->db->order_by('enquiries.created_at', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Get enquiries by customer ID (legacy: users table + email/phone match; nb: tenant_id = id)
     *
     * @param int $customer_id User ID
     * @param string|null $status Optional status filter
     * @return array List of enquiries
     */
    public function get_by_customer_id($customer_id, $status = null)
    {
        if ($this->using_nb_enquiries()) {
            return $this->get_by_userid((int) $customer_id, $status);
        }

        if (!class_exists('User_model')) {
            $this->load->model('User_model');
        }
        $user = $this->User_model->get_by_id($customer_id);

        if (!$user) {
            return array();
        }

        $this->db->select('enquiries.*, properties.name as property_name, properties.main_image as property_image');
        $this->db->from('enquiries');
        $this->db->join('properties', 'properties.id = enquiries.property_id', 'left');

        $has_email = !empty($user->email);
        $phone = isset($user->phonenumber) ? $user->phonenumber : (isset($user->phone) ? $user->phone : '');
        $has_phone = !empty($phone);

        if ($has_email || $has_phone) {
            $this->db->group_start();
            if ($has_email) {
                $this->db->where('enquiries.email', $user->email);
            }
            if ($has_phone) {
                if ($has_email) {
                    $this->db->or_where('enquiries.phone', $phone);
                } else {
                    $this->db->where('enquiries.phone', $phone);
                }
            }
            $this->db->group_end();
        } else {
            return array();
        }

        if ($status) {
            $this->db->where('enquiries.status', $status);
        }

        $this->db->order_by('enquiries.created_at', 'DESC');

        return $this->db->get()->result();
    }
}
