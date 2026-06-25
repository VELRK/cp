<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Web frontend JSON API (mirrors Next.js app/api/* for PHP-only hosting).
 * Auth: Authorization: Bearer <token> or X-Api-Token header.
 */
class Api_web extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'nb'));
        $this->load->database();
        $this->load->library('session');
        $this->load->library('upload');
        $this->load->model(array('Nb_user_model', 'Nb_property_model', 'Feedback_model'));
        $this->output->set_content_type('application/json');
        $this->_cors();
    }

    private function _cors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Token, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) === 'OPTIONS') {
            $this->output->set_output('');
            exit;
        }
    }

    private function _json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_output(json_encode($data));
    }

    /** @return object|null */
    private function _auth_user($required_role = null)
    {
        $this->load->library('nb_api_token');
        $token = $this->nb_api_token->read_token_from_request();
        if ($token === '') {
            return null;
        }
        $user = $this->Nb_user_model->get_by_api_token($token);
        if (!$user) {
            return null;
        }
        if ($required_role !== null && (string) $user->role !== (string) $required_role) {
            return null;
        }
        return $user;
    }

    private function _public_image_url($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        return $path[0] === '/' ? $path : '/' . $path;
    }

    /** GET|POST /api/feedback */
    public function feedback()
    {
        $user = $this->_auth_user();
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
        }

        if ($this->input->method() === 'get') {
            $rows = $this->Feedback_model->get_all((string) (int) $user->id, 100, 0);
            return $this->_json(array('success' => true, 'feedbacks' => $rows));
        }

        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'GET or POST only'), 405);
        }

        $title = trim((string) $this->input->post('title'));
        $description = trim((string) $this->input->post('description'));
        $name = trim((string) $this->input->post('name'));
        if ($name === '') {
            $name = (string) $user->name;
        }

        if ($title === '') {
            return $this->_json(array('success' => false, 'message' => 'Subject/title is required'), 400);
        }

        $row = array(
            'userId' => (string) (int) $user->id,
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'name' => $name !== '' ? $name : null,
            'createdAt' => date('Y-m-d H:i:s'),
        );

        if ($this->db->field_exists('image', 'feedbacks') && !empty($_FILES['image_file']) && !empty($_FILES['image_file']['name'])) {
            $upload_dir = FCPATH . 'assets/uploads/feedbacks/';
            if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
                return $this->_json(array('success' => false, 'message' => 'Could not prepare upload directory'), 500);
            }
            if (!empty($_FILES['image_file']['error']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                return $this->_json(array('success' => false, 'message' => 'Image upload failed: file could not be received'), 400);
            }
            $cfg = array(
                'upload_path' => $upload_dir,
                'allowed_types' => 'jpg|jpeg|png|webp|gif',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            $this->upload->initialize($cfg);
            if (!$this->upload->do_upload('image_file')) {
                return $this->_json(array('success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))), 400);
            }
            $u = $this->upload->data();
            $row['image'] = 'assets/uploads/feedbacks/' . $u['file_name'];
        }

        $id = $this->Feedback_model->create($row);
        if ($id <= 0) {
            return $this->_json(array('success' => false, 'message' => 'Could not save feedback'), 500);
        }

        return $this->_json(array('success' => true, 'message' => 'Feedback submitted successfully!'));
    }

    /** GET /api/owner/dashboard */
    public function owner_dashboard()
    {
        $user = $this->_auth_user('owner');
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
        }

        $listings = $this->Nb_property_model->for_owner_all((int) $user->id);
        $total_listings = count($listings);
        $active_listings = 0;
        $total_views = 0;
        $pids = array();
        foreach ($listings as $l) {
            $pids[] = (int) $l->id;
            if ((int) $l->is_active === 1) {
                $active_listings++;
            }
            $total_views += (int) ($l->views ?? 0);
        }

        $enquiry_count = 0;
        $recent_enquiries = array();
        if (!empty($pids)) {
            $this->db->where_in('property_id', $pids);
            $enquiry_count = (int) $this->db->count_all_results('nb_enquiries');

            $this->db->select('e.*, p.title AS property_title, u.name AS tenant_name');
            $this->db->from('nb_enquiries e');
            $this->db->join('nb_properties p', 'p.id = e.property_id');
            $this->db->join('nb_users u', 'u.id = e.tenant_id');
            $this->db->where_in('e.property_id', $pids);
            $this->db->order_by('e.created_at', 'DESC');
            $this->db->limit(5);
            $recent_enquiries = $this->db->get()->result();
        }

        return $this->_json(array(
            'success' => true,
            'stats' => array(
                'total_listings' => $total_listings,
                'active_listings' => $active_listings,
                'total_views' => $total_views,
                'enquiry_count' => $enquiry_count,
            ),
            'recent_enquiries' => $recent_enquiries,
        ));
    }

    /** GET /api/owner/listings */
    public function owner_listings()
    {
        $user = $this->_auth_user('owner');
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
        }

        $rows = $this->Nb_property_model->for_owner_all((int) $user->id);
        $listings = array();
        foreach ($rows as $p) {
            $images_list = array();
            if (!empty($p->images)) {
                $decoded = json_decode($p->images, true);
                if (is_array($decoded)) {
                    $images_list = $decoded;
                }
            }
            $image_urls = array();
            foreach ($images_list as $img) {
                $url = $this->_public_image_url($img);
                if ($url !== '') {
                    $image_urls[] = $url;
                }
            }
            $item = (array) $p;
            $item['images'] = $images_list;
            $item['image_urls'] = $image_urls;
            $item['thumbnail_url'] = !empty($image_urls) ? $image_urls[0] : null;
            $listings[] = $item;
        }

        return $this->_json(array('success' => true, 'listings' => $listings));
    }

    /** GET /api/owner/enquiries */
    public function owner_enquiries()
    {
        $user = $this->_auth_user('owner');
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
        }

        $this->db->select('e.*, p.title AS property_title, u.name AS tenant_name');
        $this->db->from('nb_enquiries e');
        $this->db->join('nb_properties p', 'p.id = e.property_id');
        $this->db->join('nb_users u', 'u.id = e.tenant_id');
        $this->db->where('p.owner_id', (int) $user->id);
        $this->db->order_by('e.created_at', 'DESC');
        $rows = $this->db->get()->result();

        return $this->_json(array('success' => true, 'enquiries' => $rows));
    }

    /** GET /api/tenant/enquiries */
    public function tenant_enquiries()
    {
        $user = $this->_auth_user();
        if (!$user) {
            return $this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
        }

        $this->db->select('e.*, p.title AS property_title, c.name AS city_name');
        $this->db->from('nb_enquiries e');
        $this->db->join('nb_properties p', 'p.id = e.property_id');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->where('e.tenant_id', (int) $user->id);
        $this->db->order_by('e.created_at', 'DESC');
        $rows = $this->db->get()->result();

        return $this->_json(array('success' => true, 'enquiries' => $rows));
    }

    /** GET /api/properties/{idOrSlug} */
    public function property($id_or_slug = '')
    {
        $id_or_slug = trim((string) $id_or_slug);
        if ($id_or_slug === '') {
            return $this->_json(array('success' => false, 'message' => 'Property id or slug required'), 400);
        }

        $this->db->select('p.*, c.name AS city_name, u.name AS owner_name, u.email AS owner_email, u.phone AS owner_phone, u.user_type AS owner_user_type');
        $this->db->from('nb_properties p');
        $this->db->join('nb_cities c', 'c.id = p.city_id', 'left');
        $this->db->join('nb_users u', 'u.id = p.owner_id', 'left');
        if (ctype_digit($id_or_slug)) {
            $this->db->where('p.id', (int) $id_or_slug);
        } else {
            $this->db->where('p.slug', $id_or_slug);
        }
        $prop = $this->db->get()->row();
        if (!$prop) {
            return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
        }
        if (empty($prop->is_active)) {
            return $this->_json(array('success' => false, 'message' => 'Property not found'), 404);
        }

        $images_list = array();
        if (!empty($prop->images)) {
            $decoded = json_decode($prop->images, true);
            if (is_array($decoded)) {
                $images_list = $decoded;
            }
        }
        $image_urls = array();
        foreach ($images_list as $img) {
            $url = $this->_public_image_url($img);
            if ($url !== '') {
                $image_urls[] = $url;
            }
        }

        $amenities_list = array();
        if (!empty($prop->amenities)) {
            $decoded = json_decode($prop->amenities, true);
            if (is_array($decoded)) {
                $amenities_list = $decoded;
            }
        }

        $property = (array) $prop;
        $property['images'] = $images_list;
        $property['image_urls'] = $image_urls;
        $property['thumbnail_url'] = !empty($image_urls) ? $image_urls[0] : null;
        $property['amenities'] = $amenities_list;

        return $this->_json(array('success' => true, 'property' => $property));
    }
}
