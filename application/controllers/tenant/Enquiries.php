<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Enquiries extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->model('Nb_enquiry_model');
        $this->require_login();
        $this->require_role(array('tenant', 'owner'));
        $this->require_approved();
    }

    public function index()
    {
        $uid = (int) $this->session->userdata('nb_user_id');
        $data['page_title'] = 'My enquiries';
        $data['enquiries'] = $this->Nb_enquiry_model->list_for_tenant($uid, 100);
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/tenant/enquiries', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }
}
