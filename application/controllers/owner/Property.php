<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Property extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->model(array('Nb_property_model', 'Nb_city_model', 'Nb_amenity_model'));
        $this->require_login();
        $this->require_role('owner');
        $this->require_approved();
    }

    public function add()
    {
        $data['load_maps'] = true;
        $data['page_title'] = 'Add property';
        $data['edit_id'] = null;
        $data['row'] = null;
        $data['cities'] = $this->Nb_city_model->all_active();
        $data['amenity_options'] = $this->_amenity_options_for_form();
        $data['nb_has_video_url_column'] = $this->db->field_exists('video_url', 'nb_properties');
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/owner/property_form', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    public function edit($id = null)
    {
        $data['load_maps'] = true;
        $id = (int) $id;
        $uid = (int) $this->session->userdata('nb_user_id');
        $row = $this->Nb_property_model->get_by_id($id);
        if (!$row || (int) $row->owner_id !== $uid) {
            show_404();
        }
        $data['page_title'] = 'Edit property';
        $data['edit_id'] = $id;
        $data['row'] = $row;
        $data['cities'] = $this->Nb_city_model->all_active();
        $data['amenity_options'] = $this->_amenity_options_for_form();
        $data['nb_has_video_url_column'] = $this->db->field_exists('video_url', 'nb_properties');
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/owner/property_form', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    private function _amenity_options_for_form()
    {
        if (!$this->db->table_exists('nb_amenities')) {
            return array();
        }
        return $this->Nb_amenity_model->all_active();
    }
}
