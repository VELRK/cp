<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends CI_Controller {
    public function index() {
        $data['title'] = 'Register';
        $data['page'] = 'register';
        $this->load->view('header', $data);
        $this->load->view('register', $data);
        $this->load->view('footer', $data);
    }
}
