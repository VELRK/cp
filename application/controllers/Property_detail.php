<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Property_detail extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'nb'));
        $this->load->database();
        $this->load->model('Nb_property_model');
    }

    public function index($propertyIdOrSlug = null)
    {
        if ($propertyIdOrSlug === null || $propertyIdOrSlug === '') {
            show_404();
            return;
        }
        $segment = rawurldecode((string) $propertyIdOrSlug);
        $p = null;
        if (ctype_digit($segment)) {
            $p = $this->Nb_property_model->get_by_id((int) $segment);
        } else {
            $p = $this->Nb_property_model->get_by_slug($segment);
            if (!$p) {
                $p = $this->Nb_property_model->get_by_id((int) $segment);
            }
        }
        if ($p && !empty($p->is_active)) {
            redirect(nb_property_url($p), 'location', 301);
            return;
        }
        redirect(site_url('property/' . rawurlencode($segment) . '/'), 'location', 301);
    }
}
