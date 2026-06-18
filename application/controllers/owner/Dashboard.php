<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->model(array('Nb_property_model', 'Nb_enquiry_model'));
        $this->require_login();
        $this->require_role('owner');
        $this->require_approved();
    }

    /**
     * Some server/URI setups resolve owner/dashboard as method "dashboard" on this class.
     */
    public function dashboard()
    {
        $this->index();
    }

    public function index()
    {
        $uid = (int) $this->session->userdata('nb_user_id');
        $list = $this->Nb_property_model->for_owner($uid);
        $pids = $this->Nb_property_model->ids_for_owner($uid);
        $data['page_title'] = 'Owner dashboard';
        $data['listings'] = $list;
        $data['total_listings'] = count($list);
        $data['active_listings'] = count(array_filter($list, function ($r) { return (int) $r->is_active === 1; }));
        $data['total_views'] = array_sum(array_map(function ($r) { return (int) $r->views; }, $list));
        $data['enquiry_count'] = $this->Nb_enquiry_model->count_for_owner_property_ids($pids);
        $data['recent_enquiries'] = $this->Nb_enquiry_model->recent_for_owner_properties($pids, 5);
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/owner/dashboard', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }
}
