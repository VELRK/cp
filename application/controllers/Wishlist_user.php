<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wishlist_user extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form', 'nb'));
        $this->load->database();
        $this->load->model('Wishlist_model');
        $this->require_login();
        $this->require_approved();
    }

    public function index()
    {
        $uid = (string) $this->session->userdata('nb_user_id');
        $data['page_title'] = 'My wishlist';
        $data['rows'] = $this->Wishlist_model->get_by_user($uid);
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/user/wishlist', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    public function remove($property_id = 0)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $uid = (string) $this->session->userdata('nb_user_id');
        $pid = (int) $property_id;
        if ($pid > 0) {
            $this->Wishlist_model->remove_from_wishlist($uid, $pid);
            $this->session->set_flashdata('nb_ok', 'Removed from wishlist.');
        }
        redirect('user/wishlist');
    }
}

