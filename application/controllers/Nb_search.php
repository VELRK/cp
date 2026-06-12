<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_search extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->database();
        $this->load->model(array('Nb_city_model', 'Nb_property_model'));
    }

    /**
     * Read search filters from GET (shared by index + more).
     *
     * @return array<string, mixed>
     */
    private function _read_filters()
    {
        $rk = $this->input->get('radius_km');
        $radius_km = ($rk !== null && $rk !== '') ? max(1, min(100, (float) $rk)) : 15;

        return array(
            'city_id'       => $this->input->get('city_id'),
            'property_type' => $this->input->get('property_type'),
            'listing_type'  => $this->input->get('listing_type'),
            'min_price'     => $this->input->get('min_price'),
            'max_price'     => $this->input->get('max_price'),
            'bedrooms'      => $this->input->get('bedrooms'),
            'locality_q'    => $this->input->get('q'),
            'sort'          => $this->input->get('sort') ?: 'new',
            'lat'           => $this->input->get('lat'),
            'lng'           => $this->input->get('lng'),
            'radius_km'     => $radius_km,
        );
    }

    public function index()
    {
        $per = 12;
        $filters = $this->_read_filters();

        $total = $this->Nb_property_model->count_search($filters);
        $results = $this->Nb_property_model->search($filters, $per, 0);

        $data['page_title'] = 'Search properties';
        $data['filters'] = $filters;
        $data['results'] = $results;
        $data['total'] = $total;
        $data['per_page'] = $per;
        $data['cities'] = $this->Nb_city_model->all_active();
        $data['cities_footer'] = $data['cities'];
        $data['nb_full_footer'] = true;
        $data['nb_page_search'] = true;
        $gmaps_key = $this->config->item('google_maps_api_key');
        $data['has_maps_key'] = !empty($gmaps_key);
        $data['load_maps'] = $data['has_maps_key'];

        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/search/index', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    /**
     * JSON: next page of result cards for infinite scroll (GET, same params as search + page>=2).
     */
    public function more()
    {
        $per = 12;
        $page = max(2, (int) $this->input->get('page'));
        $filters = $this->_read_filters();
        $total = $this->Nb_property_model->count_search($filters);
        $offset = ($page - 1) * $per;

        $this->output->set_content_type('application/json');

        if ($total === 0 || $offset >= $total) {
            $this->output->set_output(json_encode(array(
                'ok'        => true,
                'html'      => '',
                'has_more'  => false,
                'showing_to'=> 0,
                'total'     => (int) $total,
            )));
            return;
        }

        $results = $this->Nb_property_model->search($filters, $per, $offset);
        $html = '';
        foreach ($results as $p) {
            $html .= $this->load->view('nobroker/_property_card', array('p' => $p), true);
        }

        $showing_to = min($offset + count($results), $total);
        $has_more = $showing_to < $total;

        $this->output->set_output(json_encode(array(
            'ok'         => true,
            'html'       => $html,
            'has_more'   => $has_more,
            'showing_to' => (int) $showing_to,
            'total'      => (int) $total,
            'next_page'  => $has_more ? ($page + 1) : null,
        )));
    }
}
