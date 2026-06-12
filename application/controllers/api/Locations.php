<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Locations extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Nb_locality_model');
    }

    public function index()
    {
        $city_id = (int) $this->input->get('city_id');
        if ($city_id < 1) {
            $this->output->set_status_header(400);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('error' => 'city_id required')));
            return;
        }
        $rows = $this->Nb_locality_model->by_city($city_id);
        $out = array();
        foreach ($rows as $r) {
            $out[] = array('id' => (int) $r->id, 'name' => $r->name);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }
}
