<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_home extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->database();
        $this->load->model(array('Nb_city_model', 'Nb_property_model', 'Nb_user_model'));
    }

    public function index()
    {
        $data['load_maps'] = (string) $this->config->item('google_maps_api_key') !== '';
        $data['page_title'] = 'Coimbatore Properties — Find your perfect home';
        $data['featured'] = $this->Nb_property_model->featured(6);
        $data['stats'] = array(
            'properties' => $this->Nb_property_model->count_all_active(),
            'cities' => $this->Nb_city_model->count_all(),
            'users' => $this->Nb_user_model->count_all(),
        );
        $data['cities_footer'] = $this->Nb_city_model->all_active();
        $data['is_landing'] = true;
        $data['home_hero_light'] = true;
        $data['has_maps_key'] = (bool) $this->config->item('google_maps_api_key');
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/home/index', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }
}
