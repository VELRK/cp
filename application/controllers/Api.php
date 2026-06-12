<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Enquiry_model');
        $this->load->library('form_validation');
    }

    /**
     * Store property enquiry
     * POST /api/enquiry/store
     */
    public function enquiry_store()
    {
        // Enable CORS if needed
        header('Content-Type: application/json');

        // Set validation rules
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'required|trim');
        $this->form_validation->set_rules('city', 'City', 'required|trim');
        $this->form_validation->set_rules('message', 'Message', 'trim');
        $this->form_validation->set_rules('property_id', 'Property ID', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->form_validation->error_array()
                )));
            return;
        }

        // Optional email — validate format only if provided
        $email_raw = trim($this->input->post('email'));
        if (!empty($email_raw) && !filter_var($email_raw, FILTER_VALIDATE_EMAIL)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Please enter a valid email address.'
                )));
            return;
        }

        // Prepare data - don't set created_at as it's auto-set by database
        $data = array(
            'property_id' => $this->input->post('property_id') ? (int) $this->input->post('property_id') : null,
            'user_id' => $this->input->post('user_id') ? (int) $this->input->post('user_id') : null,
            'name' => trim($this->input->post('name')),
            'email' => $email_raw ?: null,
            'phone' => $this->input->post('phone') ? trim($this->input->post('phone')) : null,
            'city' => $this->input->post('city') ? trim($this->input->post('city')) : null,
            'message' => $this->input->post('message') ? trim($this->input->post('message')) : null,
            'status' => 'new'
        );

        // Save enquiry
        try {
            $id = $this->Enquiry_model->create($data);

            if ($id) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => true,
                        'message' => 'Enquiry submitted successfully!',
                        'id' => $id
                    )));
            } else {
                // Get database error
                $db_error = $this->db->error();
                log_message('error', 'Enquiry save failed: ' . print_r($db_error, true));

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => false,
                        'message' => 'Failed to save enquiry. Please try again.',
                        'debug' => ENVIRONMENT === 'development' ? $db_error : null
                    )));
            }
        } catch (Exception $e) {
            log_message('error', 'Enquiry save exception: ' . $e->getMessage());

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'An error occurred while saving your enquiry. Please try again.',
                    'debug' => ENVIRONMENT === 'development' ? $e->getMessage() : null
                )));
        }
    }

    /**
     * GET /api/cities — active cities for property search (JSON array).
     *
     * This must live on this controller: URIs like api/cities are resolved to
     * controllers/Api.php first (same name as the api/ folder), so
     * controllers/api/Cities.php is never reached without a conflicting route.
     */
    public function cities()
    {
        $this->load->database();
        $this->load->model('Nb_city_model');
        $rows = $this->Nb_city_model->all_active();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id' => (int) $r->id,
                'name' => $r->name,
                'state' => $r->state,
                'image' => (isset($r->image) && $r->image) ? (preg_match('#^https?://#i', $r->image) ? $r->image : base_url($r->image)) : null,
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    /**
     * GET /api/blogs — active blogs (JSON array)
     */
    public function blogs($id = null)
    {
        $this->load->database();
        $this->load->model('Blog_model');
        if ($id === null) {
            $id = $this->input->get('id');
        }

        if ($id !== null && $id !== '') {
            $r = $this->Blog_model->get_by_id($id);
            if (!$r || $r->status !== 'active') {
                $this->output
                    ->set_status_header(404)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('success' => false, 'message' => 'Blog not found')));
                return;
            }
            $gallery = array();
            if ($r->gallery) {
                $gallery_decoded = json_decode($r->gallery, true);
                if (is_array($gallery_decoded)) {
                    foreach ($gallery_decoded as $g_img) {
                        $gallery[] = preg_match('#^https?://#i', $g_img) ? $g_img : base_url($g_img);
                    }
                }
            }
            $out = array(
                'id'          => (int) $r->id,
                'name'        => $r->name,
                'author'      => $r->author,
                'date'        => $r->date,
                'short_notes' => $r->short_notes,
                'description' => $r->description,
                'gallery'     => $gallery,
                'image'       => count($gallery) > 0 ? $gallery[0] : null
            );
            $this->output->set_content_type('application/json')->set_output(json_encode($out));
            return;
        }

        $rows = $this->Blog_model->get_all('active');
        $out = array();
        foreach ($rows as $r) {
            $gallery = array();
            if ($r->gallery) {
                $gallery_decoded = json_decode($r->gallery, true);
                if (is_array($gallery_decoded)) {
                    foreach ($gallery_decoded as $g_img) {
                        $gallery[] = preg_match('#^https?://#i', $g_img) ? $g_img : base_url($g_img);
                    }
                }
            }
            $out[] = array(
                'id' => (int) $r->id,
                'name' => $r->name,
                'author' => $r->author,
                'date' => $r->date,
                'short_notes' => $r->short_notes,
                'description' => $r->description,
                'gallery' => $gallery,
                'image' => count($gallery) > 0 ? $gallery[0] : null
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    /**
     * POST /api/enquiry/send — tenant enquiry (same URL as controllers/api/Enquiry.php
     * but must be here because URI api/* resolves to this class first).
     */
    public function enquiry($action = null)
    {
        if ($action !== 'send') {
            show_404();
        }
        $this->load->database();
        $this->load->model(array('Nb_enquiry_model', 'Nb_property_model'));
        $this->load->library('session');
        $this->load->library('Nb_api_token');
        $this->nb_api_token->try_attach_session();
        $this->load->library('email');

        if ($this->input->method() !== 'post') {
            show_404();
        }
        $ct = (string) $this->input->server('CONTENT_TYPE');
        if (stripos($ct, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $j = json_decode($raw, true);
            if (is_array($j)) {
                foreach (array('property_id', 'message', 'phone', 'email') as $k) {
                    if (array_key_exists($k, $j)) {
                        $_POST[$k] = $j[$k];
                    }
                }
            }
        }
        if (!$this->session->userdata('nb_user_id')) {
            return $this->_nb_json(array('success' => false, 'message' => 'Login required'), 401);
        }
        $u = $this->session->userdata('nb_user');
        if (!$u || $u['status'] !== 'approved') {
            return $this->_nb_json(array('success' => false, 'message' => 'Your account must be approved to send enquiries'), 403);
        }
        if (!in_array($u['role'], array('owner', 'tenant'), true)) {
            return $this->_nb_json(array('success' => false, 'message' => 'This action is not available for your account type'), 403);
        }
        $pid = (int) $this->input->post('property_id');
        $message = trim((string) $this->input->post('message', true));
        $phone = trim((string) $this->input->post('phone', true));
        $email = trim((string) $this->input->post('email', true));
        if ($pid < 1 || $message === '' || $phone === '' || $email === '') {
            return $this->_nb_json(array('success' => false, 'message' => 'Missing fields'), 400);
        }
        $prop = $this->Nb_property_model->get_by_id($pid);
        if (!$prop) {
            return $this->_nb_json(array('success' => false, 'message' => 'Property not found'), 404);
        }
        if (empty($prop->is_active)) {
            return $this->_nb_json(array('success' => false, 'message' => 'This listing is not available'), 404);
        }
        if ((int) $u['id'] === (int) $prop->owner_id) {
            return $this->_nb_json(array('success' => false, 'message' => 'You cannot send an enquiry on your own listing'), 403);
        }
        $this->Nb_enquiry_model->create(array(
            'tenant_id' => (int) $u['id'],
            'property_id' => $pid,
            'message' => $this->security->xss_clean($message),
            'phone' => $this->security->xss_clean($phone),
            'email' => $this->security->xss_clean($email),
            'status' => 'new',
        ));
        $this->_nb_notify_admin_email($prop, $u, $message, $phone, $email);
        return $this->_nb_json(array('success' => true, 'message' => 'Enquiry sent. We\'ve routed it to the listing owner; they may contact you on your phone or email.'));
    }

    private function _nb_json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    private function _nb_notify_admin_email($prop, $tenant, $message, $phone, $email)
    {
        $admin = $this->config->item('nb_admin_email');
        if (empty($admin)) {
            return;
        }
        $this->email->from('noreply@localhost', 'Dream Villa Makers');
        $this->email->to($admin);
        $this->email->subject('New property enquiry: ' . $prop->title);
        $this->email->message(
            "From: {$tenant['name']} ({$email}) {$phone}\nProperty #{$prop->id}: {$prop->title}\n\n{$message}"
        );
        @$this->email->send();
    }
}

