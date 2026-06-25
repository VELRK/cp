<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Postman-style API tester for web & mobile developers.
 * Public page — no admin login required.
 */
class Api_developer extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'nb'));
    }

    /** GET /developer — interactive API playground */
    public function index()
    {
        $this->load->config('api_catalog');
        $catalog = $this->config->item('api_catalog');
        if (!is_array($catalog)) {
            $catalog = array('groups' => array());
        }

        $data = array(
            'page_title' => 'API Collection',
            'base_api_url' => rtrim(base_url(), '/'),
            'catalog' => $catalog,
            'catalog_json' => json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );

        $this->load->view('nobroker/developer/api_tester', $data);
    }

    /** GET /developer/catalog — raw JSON catalog (for scripts) */
    public function catalog()
    {
        $this->load->config('api_catalog');
        $catalog = $this->config->item('api_catalog');
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
