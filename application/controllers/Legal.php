<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Legal extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function terms()
    {
        $data['title'] = 'Terms of Services - Coimbatore Properties';
        $data['page'] = 'legal';

        $this->load->view('header', $data);
        $this->load->view('legal/terms');
        $this->load->view('footer');
    }

    public function privacy()
    {
        $data['title'] = 'Privacy Policy - Coimbatore Properties';
        $data['page'] = 'legal';

        $this->load->view('header', $data);
        $this->load->view('legal/privacy');
        $this->load->view('footer');
    }

    public function cookie()
    {
        $data['title'] = 'Cookie Policy - Coimbatore Properties';
        $data['page'] = 'legal';

        $this->load->view('header', $data);
        $this->load->view('legal/cookie');
        $this->load->view('footer');
    }
}

