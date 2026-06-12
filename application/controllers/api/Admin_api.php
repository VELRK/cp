<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_api extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Nb_user_model');
        $this->load->model('Nb_enquiry_model');
        $this->load->library(array('session', 'email', 'security'));
    }

    public function approve_user()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_nb_admin()) {
            return $this->_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $uid = (int) $this->input->post('user_id');
        if ($uid < 1) {
            return $this->_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        $update = array('status' => 'approved');
        if ($this->db->field_exists('is_verified', 'nb_users') && $this->input->post('verified') !== null) {
            $update['is_verified'] = ((int) $this->input->post('verified') === 1) ? 1 : 0;
        }
        $this->Nb_user_model->update($uid, $update);
        return $this->_json(array('success' => true));
    }

    public function update_enquiry()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_nb_admin()) {
            return $this->_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $eid = (int) $this->input->post('enquiry_id');
        $status = $this->input->post('status');
        $notes = $this->input->post('admin_notes', true);
        if ($eid < 1 || !in_array($status, array('new', 'read', 'responded', 'closed'), true)) {
            return $this->_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        $row = $this->Nb_enquiry_model->get_by_id($eid);
        if (!$row) {
            return $this->_json(array('success' => false, 'message' => 'Not found'), 404);
        }
        $upd = array(
            'status'      => $status,
            'admin_notes' => $notes,
        );
        if (isset($_POST['message'])) {
            $m = trim((string) $this->input->post('message', true));
            if ($m === '') {
                return $this->_json(array('success' => false, 'message' => 'Message cannot be empty'), 400);
            }
            $upd['message'] = $m;
        }
        if (isset($_POST['phone'])) {
            $phone = trim((string) $this->input->post('phone'));
            $upd['phone'] = $this->security->xss_clean(substr($phone, 0, 15));
        }
        if (isset($_POST['email'])) {
            $email = trim((string) $this->input->post('email'));
            $upd['email'] = $this->security->xss_clean(substr($email, 0, 200));
        }
        $this->Nb_enquiry_model->update($eid, $upd);
        return $this->_json(array('success' => true));
    }

    public function delete_enquiry()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_nb_admin()) {
            return $this->_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $eid = (int) $this->input->post('enquiry_id');
        if ($eid < 1) {
            return $this->_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        if (!$this->Nb_enquiry_model->get_by_id($eid)) {
            return $this->_json(array('success' => false, 'message' => 'Not found'), 404);
        }
        $this->Nb_enquiry_model->delete($eid);
        return $this->_json(array('success' => true));
    }

    public function approve_property()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_nb_admin()) {
            return $this->_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $this->load->model('Nb_property_model');
        $pid = (int) $this->input->post('property_id');
        if ($pid < 1) {
            return $this->_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        $p = $this->Nb_property_model->get_by_id($pid);
        if (!$p) {
            return $this->_json(array('success' => false, 'message' => 'Not found'), 404);
        }
        if (!$this->db->field_exists('is_active', 'nb_properties')) {
            return $this->_json(array('success' => false, 'message' => 'Not supported'), 500);
        }
        $update = array_merge(array('is_active' => 1), $this->Nb_property_model->slug_publish_patch($p));
        $this->Nb_property_model->update($pid, $update);
        return $this->_json(array('success' => true));
    }

    private function _nb_admin()
    {
        $u = $this->session->userdata('nb_user');
        return $u && $u['role'] === 'admin' && $u['status'] === 'approved';
    }

    private function _json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}
