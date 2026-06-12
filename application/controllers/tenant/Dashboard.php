<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->model('Nb_enquiry_model');
        $this->require_login();
        $this->require_role('tenant');
        $this->require_approved();
    }

    public function dashboard()
    {
        $this->index();
    }

    public function index()
    {
        $uid = (int) $this->session->userdata('nb_user_id');
        $data['page_title'] = 'Tenant dashboard';
        $data['enquiries'] = $this->Nb_enquiry_model->list_for_tenant($uid, 20);
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/tenant/dashboard', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }
}
