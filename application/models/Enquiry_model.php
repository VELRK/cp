<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Legacy `enquiries` + `properties` OR Nobroker `nb_enquiries` + `nb_properties`.
 * When `enquiries` does not exist, all reads/writes use `nb_enquiries` (tenant_id = mobile user id).
 */
class Enquiry_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * True when the old table is absent but nb_enquiries exists (typical Coimbatore Properties DB).
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
        $this->db->select('enquiries.*, enquiries.propertyName as property_name, enquiries.userName as tenant_name, enquiries.propertyId as property_id');
        $this->db->from('enquiries');
        $this->db->order_by('enquiries.createdAt', 'DESC');
        $rows = $this->db->get()->result();

        // Map to expected structure so the admin panel can render it correctly
        foreach ($rows as $row) {
            $row->name = $row->userName;
            $row->email = $row->userEmail;
            $row->phone = $row->userPhone;
            $row->created_at = $row->createdAt;
        }

        return $rows;
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
        $this->db->select('enquiries.*, enquiries.propertyName as property_name, enquiries.propertyId as property_id, enquiries.userName as tenant_name');
        $this->db->from('enquiries');
        $this->db->where('enquiries.id', $id);
        $row = $this->db->get()->row();
        if ($row) {
            $row->name = $row->userName;
            $row->email = $row->userEmail;
            $row->phone = $row->userPhone;
            $row->created_at = $row->createdAt;
        }
        return $row;
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
                'tenant_id' => $tenant_id,
                'property_id' => $property_id,
                'message' => $data['message'],
                'phone' => isset($data['phone']) ? $data['phone'] : '',
                'email' => $data['email'],
                'status' => isset($data['status']) ? $data['status'] : 'new',
            );
            if ($this->db->insert('nb_enquiries', $insert)) {
                return (int) $this->db->insert_id();
            }
            return false;
        }

        $filtered_data = array(
            'propertyId' => isset($data['property_id']) ? $data['property_id'] : (isset($data['propertyId']) ? $data['propertyId'] : null),
            'userId' => isset($data['user_id']) ? $data['user_id'] : (isset($data['userId']) ? $data['userId'] : null),
            'userName' => isset($data['name']) ? $data['name'] : (isset($data['userName']) ? $data['userName'] : ''),
            'userEmail' => isset($data['email']) ? $data['email'] : (isset($data['userEmail']) ? $data['userEmail'] : ''),
            'userPhone' => isset($data['phone']) ? $data['phone'] : (isset($data['userPhone']) ? $data['userPhone'] : ''),
            'city' => isset($data['city']) ? $data['city'] : null,
            'message' => isset($data['message']) ? $data['message'] : '',
            'status' => isset($data['status']) ? $data['status'] : 'new',
            'createdAt' => date('Y-m-d H:i:s')
        );

        if (empty($filtered_data['userName']) || empty($filtered_data['userEmail'])) {
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
        $this->db->select('enquiries.*, enquiries.propertyName as property_name, enquiries.propertyId as property_id');
        $this->db->from('enquiries');
        $this->db->where('enquiries.userId', $user_id);

        if ($status) {
            $this->db->where('enquiries.status', $status);
        }

        $this->db->order_by('enquiries.createdAt', 'DESC');

        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $row->name = $row->userName;
            $row->email = $row->userEmail;
            $row->phone = $row->userPhone;
            $row->created_at = $row->createdAt;
        }
        return $rows;
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

        $this->db->select('enquiries.*, enquiries.propertyName as property_name, enquiries.propertyId as property_id');
        $this->db->from('enquiries');

        $has_email = !empty($user->email);
        $phone = isset($user->phonenumber) ? $user->phonenumber : (isset($user->phone) ? $user->phone : '');
        $has_phone = !empty($phone);

        if ($has_email || $has_phone) {
            $this->db->group_start();
            if ($has_email) {
                $this->db->where('enquiries.userEmail', $user->email);
            }
            if ($has_phone) {
                if ($has_email) {
                    $this->db->or_where('enquiries.userPhone', $phone);
                } else {
                    $this->db->where('enquiries.userPhone', $phone);
                }
            }
            $this->db->group_end();
        } else {
            return array();
        }

        if ($status) {
            $this->db->where('enquiries.status', $status);
        }

        $this->db->order_by('enquiries.createdAt', 'DESC');

        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $row->name = $row->userName;
            $row->email = $row->userEmail;
            $row->phone = $row->userPhone;
            $row->created_at = $row->createdAt;
        }
        return $rows;
    }
}
