<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cities extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Nb_city_model');
    }

    public function index()
    {
        $rows = $this->Nb_city_model->all_active();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id'    => (int) $r->id, 
                'name'  => $r->name, 
                'state' => $r->state,
                'image' => (isset($r->image) && $r->image) ? (preg_match('#^https?://#i', $r->image) ? $r->image : base_url($r->image)) : null
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }
}
