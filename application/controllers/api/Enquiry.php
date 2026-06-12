<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Enquiry extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model(array('Nb_enquiry_model', 'Nb_property_model'));
        $this->load->library('session');
        $this->load->library('email');
    }

    public function send()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->session->userdata('nb_user_id')) {
            return $this->_json(array('success' => false, 'message' => 'Login required'), 401);
        }
        $u = $this->session->userdata('nb_user');
        if (!$u || $u['status'] !== 'approved') {
            return $this->_json(array('success' => false, 'message' => 'Your account must be approved to send enquiries'), 403);
        }
        if (!in_array($u['role'], array('owner', 'tenant'), true)) {
            return $this->_json(array('success' => false, 'message' => 'This action is not available for your account type'), 403);
        }
        $pid = (int) $this->input->post('property_id');
        $message = trim((string) $this->input->post('message', true));
        $phone = trim((string) $this->input->post('phone', true));
        $email = trim((string) $this->input->post('email', true));
        if ($pid < 1 || $message === '' || $phone === '' || $email === '') {
            return $this->_json(array('success' => false, 'message' => 'Missing fields'), 400);
        }
        $prop = $this->Nb_property_model->get_by_id($pid);
        if (!$prop) {
            return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
        }
        if (empty($prop->is_active)) {
            return $this->_json(array('success' => false, 'message' => 'This listing is not available'), 404);
        }
        if ((int) $u['id'] === (int) $prop->owner_id) {
            return $this->_json(array('success' => false, 'message' => 'You cannot send an enquiry on your own listing'), 403);
        }
        $this->Nb_enquiry_model->create(array(
            'tenant_id'   => (int) $u['id'],
            'property_id' => $pid,
            'message'     => $this->security->xss_clean($message),
            'phone'       => $this->security->xss_clean($phone),
            'email'       => $this->security->xss_clean($email),
            'status'      => 'new',
        ));
        $this->_notify_admin_email($prop, $u, $message, $phone, $email);
        return $this->_json(array('success' => true, 'message' => 'Enquiry sent. We\'ve routed it to the listing owner; they may contact you on your phone or email.'));
    }

    private function _notify_admin_email($prop, $tenant, $message, $phone, $email)
    {
        $admin = $this->config->item('nb_admin_email');
        if (empty($admin)) {
            return;
        }
        $this->email->from('noreply@localhost', 'Dream Villa Makers');
        $this->email->to($admin);
        $this->email->subject('New property enquiry: ' . $prop->title);
        $this->email->message(
            "From: {$tenant['name']} ({$email}) {$phone}\nProperty #{$prop->id}: {$prop->title}\n\n{$message}"
        );
        @$this->email->send();
    }

    private function _json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}
