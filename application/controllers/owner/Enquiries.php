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
        $this->require_role('owner');
        $this->require_approved();
    }

    public function index()
    {
        $uid = (int) $this->session->userdata('nb_user_id');
        $this->load->model('Nb_property_model');
        $pids = $this->Nb_property_model->ids_for_owner($uid);
        $data['page_title'] = 'Enquiries';
        $data['owner_nav'] = 'enquiries';
        $data['enquiries'] = $this->Nb_enquiry_model->recent_for_owner_properties($pids, 100);
        $this->load->view('nobroker/owner/panel_header', $data);
        $this->load->view('nobroker/owner/enquiries', $data);
        $this->load->view('nobroker/owner/panel_footer', $data);
    }
}
