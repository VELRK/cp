<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blog_detail extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        $data['title'] = 'Blog Detail - Real Estate';
        $data['page'] = 'blog_detail';
        
        $this->load->view('header', $data);
        $this->load->view('blog_detail');
        $this->load->view('footer');
    }
}
