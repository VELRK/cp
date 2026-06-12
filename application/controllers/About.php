<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class About extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        $data['title'] = 'About Us - Real Estate';
        $data['page'] = 'about';
        
        $this->load->view('header', $data);
        $this->load->view('about');
        $this->load->view('footer');
    }
}
