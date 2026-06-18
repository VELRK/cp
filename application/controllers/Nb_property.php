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
        if (!$p || !$p->is_active) {
            show_404();
        }
        if ($by_id && !empty($p->slug)) {
            redirect(nb_property_url($p), 'location', 301);
        }

        $id = (int) $p->id;
        $this->Nb_property_model->increment_views($id);
        $p->views = (int) $p->views + 1;

        $data['page_title'] = $p->title . ' | ' . $p->city_name . ' | Coimbatore Properties';
        $data['p'] = $p;
        $data['images'] = array();
        if (!empty($p->images)) {
            $decoded = json_decode($p->images, true);
            if (is_array($decoded)) {
                $data['images'] = $decoded;
            }
        }
        $data['amenities'] = array();
        if (!empty($p->amenities)) {
            $am = json_decode($p->amenities, true);
            if (is_array($am)) {
                foreach ($am as $label) {
                    if ($label === null || $label === '') {
                        continue;
                    }
                    $data['amenities'][] = is_string($label) ? $label : (string) $label;
                }
            }
        }
        $data['similar'] = $this->Nb_property_model->similar(
            $p->city_id,
            $p->property_type,
            $id,
            4
        );

        $nb = $this->session->userdata('nb_user');
        $data['nb_user'] = $nb;
        $data['can_enquire'] = $nb && $nb['status'] === 'approved'
            && in_array($nb['role'], array('owner', 'tenant'), true)
            && (int) $nb['id'] !== (int) $p->owner_id;
        $data['load_maps'] = !empty($this->config->item('google_maps_api_key'));
        $data['cities_footer'] = $this->Nb_city_model->all_active();
        $data['nb_full_footer'] = true;
        $data['nb_page_property'] = true;

        $price_txt = nb_format_listing_price($p->price, $p->listing_type);
        $desc_src = $p->description ? $p->description : ($p->title . ' in ' . $p->locality . ', ' . $p->city_name . '. ' . $price_txt . '. ' . nb_property_type_label($p->property_type));
        $meta_desc = nb_meta_description($desc_src);
        $canonical = nb_property_url($p);
        $og_image = '';
        if (!empty($data['images'][0])) {
            $og_image = base_url($data['images'][0]);
        }
        $data['nb_seo'] = array(
            'description' => $meta_desc,
            'canonical' => $canonical,
            'og_title' => $p->title,
            'og_description' => $meta_desc,
            'og_image' => $og_image,
        );
        $json_ld = array(
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $p->title,
            'description' => $meta_desc,
            'url' => $canonical,
            'sku' => 'nb-prop-' . $id,
            'offers' => array(
                '@type' => 'Offer',
                'priceCurrency' => 'INR',
                'price' => (string) $p->price,
                'availability' => 'https://schema.org/InStock',
                'url' => $canonical,
            ),
        );
        if ($og_image !== '') {
            $json_ld['image'] = $og_image;
        }
        $data['nb_json_ld'] = json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/property/detail', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }
}
