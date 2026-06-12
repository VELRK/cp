<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Listings extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->model('Nb_property_model');
        $this->require_login();
        $this->require_role('owner');
        $this->require_approved();
    }

    public function index()
    {
        $uid = (int) $this->session->userdata('nb_user_id');
        $data['page_title'] = 'My listings';
        $data['listings'] = $this->Nb_property_model->for_owner($uid);
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/owner/listings', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }
}
