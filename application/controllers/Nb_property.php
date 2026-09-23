<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_property extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form', 'nb'));
        $this->load->database();
        $this->load->model(array('Nb_property_model', 'Nb_enquiry_model', 'Nb_city_model'));
        $this->load->library('session');
    }

    /**
     * @param string|null $segment Numeric id or URL slug
     */
    public function view($segment = null)
    {
        if ($segment === null || $segment === '') {
            show_404();
            return;
        }
        $segment = rawurldecode((string) $segment);
        if ($segment === 'owner' || $segment === 'tenant') {
            redirect(site_url($segment . '/dashboard'), 'location', 302);
            return;
        }

        $p = null;
        $by_id = false;
        if (ctype_digit($segment)) {
            $by_id = true;
            $p = $this->Nb_property_model->get_by_id((int) $segment);
        } else {
            $p = $this->Nb_property_model->get_by_slug($segment);
        }
        if (!$p || empty($p->is_active)) {
            show_404();
            return;
        }
        if ($by_id && !empty($p->slug) && (string) $p->slug !== $segment) {
            redirect(nb_property_url($p), 'location', 301);
            return;
        }

        $this->Nb_property_model->increment_views((int) $p->id);

        $next_html = nb_next_property_html_path($segment);
        if ($next_html !== '') {
            $this->output->set_content_type('text/html', 'utf-8');
            $this->output->set_output(file_get_contents($next_html));
            return;
        }

        $next_dev = nb_next_dev_property_url(isset($p->slug) && $p->slug !== '' ? $p->slug : $segment);
        if ($next_dev !== '') {
            redirect($next_dev, 'location', 302);
            return;
        }

        show_404();
    }
}
