<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broker_admin extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->model(array('Nb_user_model', 'Nb_property_model', 'Nb_enquiry_model', 'Nb_city_model', 'Nb_amenity_model', 'Nb_property_type_model', 'Wishlist_model', 'Live_update_model', 'Housing_news_model', 'Banner_model', 'Feedback_model', 'Notification_model', 'Nb_delete_request_model'));
    }

    /**
     * Bridge Next.js/API token login into a PHP session, then open the panel.
     * GET /panel/auth?token=...
     */
    public function auth()
    {
        $this->load->library('nb_api_token');
        $this->load->model('Nb_user_model');
        $token = $this->nb_api_token->read_token_from_request();

        if ($token === '') {
            $this->session->set_flashdata('nb_err', 'Missing login token. Log in on the website first, then click Admin Panel.');
            redirect(site_url() . '?modal=login');
            return;
        }

        if (!$this->db->field_exists('api_token', 'nb_users')) {
            show_error('Server setup incomplete: api_token column is missing. Run database migrations.', 500);
            return;
        }

        $user = $this->Nb_user_model->get_by_api_token($token);
        if (!$user) {
            $this->session->set_flashdata('nb_err', 'Invalid or expired login token. Please log in again, then open Admin Panel from the menu.');
            redirect(site_url() . '?modal=login');
            return;
        }

        $this->set_nb_session_from_user($user);
        nb_set_api_token_cookie($token);

        if ((string) $user->role !== 'admin') {
            show_error('Admin access only. Your account role is: ' . html_escape((string) $user->role), 403);
            return;
        }

        if (isset($user->status) && (string) $user->status !== 'approved') {
            show_error('Your account is not approved yet. Contact support.', 403);
            return;
        }

        redirect('panel');
    }

    /**
     * Dedicated admin login at /admin (email/phone + password → /panel).
     */
    public function admin_login()
    {
        $u = $this->nb_user();
        if ($u && isset($u['role'], $u['status']) && $u['role'] === 'admin' && $u['status'] === 'approved') {
            redirect('panel');
            return;
        }

        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('login', 'Email or phone', 'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if (!$this->form_validation->run()) {
                $this->load->view('nobroker/admin/login', array(
                    'page_title' => 'Admin Login',
                    'error'      => trim(strip_tags(validation_errors(' ', ' '))),
                ));
                return;
            }

            $login = $this->input->post('login', true);
            $password = $this->input->post('password');
            $user = $this->Nb_user_model->get_by_email_or_phone($login);

            if (!$user || !password_verify($password, $user->password)) {
                $this->load->view('nobroker/admin/login', array(
                    'page_title' => 'Admin Login',
                    'error'      => 'Invalid email, phone, or password.',
                ));
                return;
            }

            if ((string) $user->role !== 'admin') {
                $this->load->view('nobroker/admin/login', array(
                    'page_title' => 'Admin Login',
                    'error'      => 'Admin access only. This account is not an administrator.',
                ));
                return;
            }

            if (!isset($user->status) || (string) $user->status !== 'approved') {
                $this->load->view('nobroker/admin/login', array(
                    'page_title' => 'Admin Login',
                    'error'      => 'Your admin account is not approved yet.',
                ));
                return;
            }

            $this->session->sess_regenerate(true);
            $this->set_nb_session_from_user($user);

            if ($this->db->field_exists('api_token', 'nb_users')) {
                $token = bin2hex(random_bytes(32));
                $this->Nb_user_model->set_api_token((int) $user->id, $token);
                nb_set_api_token_cookie($token);
            }

            redirect('panel');
            return;
        }

        $flash = $this->session->flashdata('nb_err');
        $this->load->view('nobroker/admin/login', array(
            'page_title' => 'Admin Login',
            'error'      => is_string($flash) ? $flash : '',
        ));
    }

    public function index()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Admin — Dashboard';
        $data['stats'] = array(
            'users'         => $this->Nb_user_model->count_all(),
            'pending'       => $this->Nb_user_model->count_unverified(),
            'props'         => $this->db->count_all('nb_properties'),
            'props_pending' => $this->Nb_property_model->count_pending_publication(),
            'enq_today'     => $this->Nb_enquiry_model->count_today(),
        );
        $data['pending_users'] = $this->Nb_user_model->admin_list(5, 0, array('verified' => 0));
        $data['recent_enq'] = $this->Nb_enquiry_model->list_admin(array(), 5, 0);
        $data['admin_nav'] = 'dashboard';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/dashboard', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function users()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Users';
        $filters = array(
            'q'      => $this->input->get('q'),
            'role'   => $this->input->get('role'),
            'status' => $this->input->get('status'),
        );
        $data['users'] = $this->Nb_user_model->admin_list(50, 0, $filters);
        $data['filters'] = $filters;
        $data['admin_nav'] = 'users';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/users', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function user_add()
    {
        $this->require_login();
        $this->require_role('admin');
        $this->load->library('form_validation');
        if ($this->input->method() === 'post' && $this->_user_save()) {
            $this->session->set_flashdata('nb_ok', 'User created.');
            redirect('panel/users');
            return;
        }
        $data['page_title'] = 'Add user';
        $data['admin_nav'] = 'users';
        $data['cities'] = $this->Nb_city_model->all_active();
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/user_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function user_edit($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1) { show_404(); }
        $row = $this->Nb_user_model->get_by_id($id);
        if (!$row) { show_404(); }
        $this->load->library('form_validation');
        if ($this->input->method() === 'post' && $this->_user_update($id, $row)) {
            $this->session->set_flashdata('nb_ok', 'User updated.');
            redirect('panel/users');
            return;
        }
        $data['page_title'] = 'Edit user #' . $id;
        $data['edit_id'] = $id;
        $data['edit_row'] = $row;
        $data['admin_nav'] = 'users';
        $data['cities'] = $this->Nb_city_model->all_active();
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/user_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function user_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        if ($this->input->method() !== 'post') { show_404(); }
        $id = (int) $id;
        if ($id < 1) { show_404(); }
        $row = $this->Nb_user_model->get_by_id($id);
        if (!$row) { show_404(); }
        $me = (int) $this->session->userdata('nb_user_id');
        if ($me === $id) {
            $this->session->set_flashdata('nb_err', 'You cannot delete your own account.');
            redirect('panel/users');
            return;
        }
        $this->Nb_user_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'User deleted.');
        redirect('panel/users');
    }

    public function properties()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Properties';
        $data['rows'] = $this->Nb_property_model->admin_list(array(), 100, 0);
        $data['pending_count'] = $this->Nb_property_model->count_pending_publication();
        $data['admin_nav'] = 'properties';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/properties', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function properties_pending()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Property approvals';
        $data['rows'] = $this->Nb_property_model->admin_list(array('is_active' => 0), 100, 0);
        $data['admin_nav'] = 'approvals';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/properties_pending', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function property_add()
    {
        $this->require_login();
        $this->require_role('admin');
        $this->_sync_admin_api_token_cookie();
        $data['page_title'] = 'Add property';
        $data['edit_id'] = 0;
        $data['row'] = null;
        $data['cities'] = $this->Nb_city_model->all_active();
        $data['amenity_options'] = $this->_amenity_options_for_form();
        $data['owners'] = $this->Nb_user_model->owners_for_select();
        $data['is_admin'] = true;
        $data['hide_page_title'] = true;
        $data['load_maps'] = true;
        $data['admin_nav'] = 'properties';
        $data['nb_has_video_url_column'] = $this->db->field_exists('video_url', 'nb_properties');
        $data['admin_property_token'] = $this->_issue_admin_property_token();
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/property_add', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function property_edit($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $this->_sync_admin_api_token_cookie();
        $id = (int) $id;
        if ($id < 1) {
            show_404();
        }
        $row = $this->Nb_property_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        $data['page_title'] = 'Edit property #' . $id;
        $data['edit_id'] = $id;
        $data['row'] = $row;
        $data['cities'] = $this->Nb_city_model->all_active();
        $data['amenity_options'] = $this->_amenity_options_for_form();
        $owners = $this->Nb_user_model->owners_for_select();
        $oid = (int) $row->owner_id;
        $have = array();
        foreach ($owners as $o) {
            $have[(int) $o->id] = true;
        }
        if ($oid > 0 && empty($have[$oid])) {
            $cur = $this->Nb_user_model->get_by_id($oid);
            if ($cur) {
                $owners[] = $cur;
            }
        }
        $data['owners'] = $owners;
        $data['is_admin'] = true;
        $data['hide_page_title'] = true;
        $data['load_maps'] = true;
        $data['admin_nav'] = 'properties';
        $data['nb_has_video_url_column'] = $this->db->field_exists('video_url', 'nb_properties');
        $data['admin_property_token'] = $this->_issue_admin_property_token();
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/property_edit', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function property_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id = (int) $id;
        $row = $this->Nb_property_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        $this->_delete_property_upload_files($row);
        $this->Nb_property_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Property deleted.');
        redirect('panel/properties');
    }

    /** One-time token bound to session for admin property form POST. */
    private function _issue_admin_property_token()
    {
        $token = bin2hex(random_bytes(16));
        $this->session->set_userdata('nb_admin_property_token', $token);
        return $token;
    }

    /** Keep nb_token cookie in sync so panel form POST restores admin session on localhost:3000. */
    private function _sync_admin_api_token_cookie()
    {
        if (!$this->db->field_exists('api_token', 'nb_users')) {
            return;
        }
        $uid = (int) $this->session->userdata('nb_user_id');
        if ($uid < 1) {
            return;
        }
        $row = $this->Nb_user_model->get_by_id($uid);
        if ($row && !empty($row->api_token)) {
            nb_set_api_token_cookie((string) $row->api_token);
        }
    }

    public function enquiries()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Enquiries';
        $data['rows'] = $this->Nb_enquiry_model->list_admin(array(), 100, 0);
        $data['admin_nav'] = 'enquiries';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/enquiries', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function enquiry($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        $row = $this->Nb_enquiry_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        $data['page_title'] = 'Enquiry #' . $id;
        $data['e'] = $row;
        $data['property'] = $this->Nb_property_model->get_by_id($row->property_id);
        $data['tenant'] = $this->Nb_user_model->get_by_id($row->tenant_id);
        $data['admin_nav'] = 'enquiries';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/enquiry_detail', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function cities()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Cities';
        $data['rows'] = $this->Nb_city_model->admin_all();
        $data['admin_nav'] = 'cities';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/cities', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function city_add()
    {
        $this->city_edit(0);
    }

    public function city_edit($id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($this->input->method() === 'post') {
            $this->_city_save();
            return;
        }
        $row = null;
        if ($id > 0) {
            $row = $this->Nb_city_model->get_by_id($id);
            if (!$row) {
                show_404();
            }
        }
        $data['page_title'] = $id > 0 ? 'Edit city' : 'Add city';
        $data['row'] = $row;
        $data['edit_id'] = $id;
        $data['admin_nav'] = 'cities';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/city_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function city_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        if ($this->Nb_city_model->count_references($id) > 0) {
            $this->session->set_flashdata('nb_err', 'This city cannot be deleted because it is used by listings, users, or localities.');
            redirect('panel/cities');
            return;
        }
        $this->Nb_city_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'City deleted.');
        redirect('panel/cities');
    }

    public function amenities()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Amenities';
        $data['rows'] = $this->Nb_amenity_model->admin_all();
        $data['admin_nav'] = 'amenities';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/amenities', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function property_types()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Property types';
        $grouped = $this->Nb_property_type_model->admin_grouped_rows();
        $data['main_rows'] = $grouped['mains'];
        $data['sub_rows'] = $grouped['subs'];
        $data['rows'] = $this->Nb_property_type_model->admin_all();
        $data['admin_nav'] = 'property_types';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/property_types', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function wishlists()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Wishlists';
        $data['rows'] = $this->Wishlist_model->get_all();
        $data['admin_nav'] = 'wishlists';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/wishlists', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function live_updates()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Live updates';
        $data['rows'] = $this->Live_update_model->get_all();
        $data['admin_nav'] = 'live_updates';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/live_updates', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function live_update_edit($id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        $row = $this->Live_update_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        if ($this->input->method() === 'post' && $this->_live_update_save_admin($id, $row)) {
            $this->session->set_flashdata('nb_ok', 'Live update updated.');
            redirect('panel/live-updates');
            return;
        }
        $data['page_title'] = 'Edit live update';
        $data['admin_nav'] = 'live_updates';
        $data['row'] = $row;
        $data['edit_id'] = $id;
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/live_update_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function live_update_delete($id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id = (int) $id;
        $row = $this->Live_update_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        $this->Live_update_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Live update deleted.');
        redirect('panel/live-updates');
    }

    public function housing_news()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Housing news';
        $data['rows'] = $this->Housing_news_model->get_all();
        $data['admin_nav'] = 'housing_news';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/housing_news', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function housing_news_add()
    {
        $this->housing_news_edit(0);
    }

    public function housing_news_edit($id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        $row = null;
        if ($id > 0) {
            $row = $this->Housing_news_model->get_by_id($id);
            if (!$row) {
                show_404();
            }
        }
        if ($this->input->method() === 'post') {
            $title = trim((string) $this->input->post('title'));
            if ($title === '') {
                $this->session->set_flashdata('nb_err', 'Title is required.');
                redirect($id > 0 ? ('panel/housing-news/edit/' . $id) : 'panel/housing-news/add');
                return;
            }
            $category = strtolower(trim((string) $this->input->post('category')));
            if (!in_array($category, array('market', 'tips', 'legal'), true)) {
                $category = 'market';
            }
            $data = array(
                'title' => $title,
                'subtitle' => trim((string) $this->input->post('subtitle')),
                'description' => (string) $this->input->post('description'),
                'authorName' => trim((string) $this->input->post('authorName')),
                'category' => $category,
            );
            $existingImages = array();
            if ($row && isset($row->multiImages)) {
                $existingImages = $this->_decode_housing_news_images($row->multiImages);
            }
            $removedImages = $this->input->post('remove_existing_images');
            if (is_array($removedImages) && !empty($removedImages)) {
                $removedImages = array_map('trim', $removedImages);
                $existingImages = array_values(array_diff($existingImages, $removedImages));
            }
            $uploadedImages = $this->_upload_housing_news_images('multi_images');
            if ($uploadedImages === false) {
                redirect($id > 0 ? ('panel/housing-news/edit/' . $id) : 'panel/housing-news/add');
                return;
            }
            $allImages = array_values(array_unique(array_merge($existingImages, $uploadedImages)));
            $data['multiImages'] = !empty($allImages) ? json_encode($allImages) : null;
            if ($id > 0) {
                $this->Housing_news_model->update($id, $data);
                $this->session->set_flashdata('nb_ok', 'Housing news updated.');
            } else {
                $this->Housing_news_model->create($data);
                $this->session->set_flashdata('nb_ok', 'Housing news created.');
            }
            redirect('panel/housing-news');
            return;
        }

        $data['page_title'] = $id > 0 ? 'Edit housing news' : 'Add housing news';
        $data['row'] = $row;
        $data['multi_images'] = $row && isset($row->multiImages) ? $this->_decode_housing_news_images($row->multiImages) : array();
        $data['edit_id'] = $id;
        $data['admin_nav'] = 'housing_news';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/housing_news_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function housing_news_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->Housing_news_model->get_by_id($id)) {
            show_404();
        }
        $this->Housing_news_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Housing news deleted.');
        redirect('panel/housing-news');
    }

    private function _decode_housing_news_images($raw)
    {
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return array();
        }
        $clean = array();
        foreach ($decoded as $img) {
            if (!is_string($img)) {
                continue;
            }
            $img = trim($img);
            if ($img === '') {
                continue;
            }
            $clean[] = $img;
        }
        return array_values(array_unique($clean));
    }

    private function _upload_housing_news_images($inputName)
    {
        if (empty($_FILES[$inputName]['name']) || !is_array($_FILES[$inputName]['name'])) {
            return array();
        }

        $uploadPath = FCPATH . 'assets/images/housing_news/';
        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0755, true)) {
            $this->session->set_flashdata('nb_err', 'Could not create housing news upload directory.');
            return false;
        }

        $files = $_FILES[$inputName];
        $count = count($files['name']);
        $saved = array();
        $this->load->library('upload');

        for ($i = 0; $i < $count; $i++) {
            if (empty($files['name'][$i])) {
                continue;
            }
            $_FILES['single_hn_image'] = array(
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            );

            $config = array(
                'upload_path' => $uploadPath,
                'allowed_types' => 'gif|jpg|png|jpeg|webp',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('single_hn_image')) {
                $msg = strip_tags($this->upload->display_errors('', ''));
                $this->session->set_flashdata('nb_err', 'Image upload failed: ' . $msg);
                return false;
            }
            $u = $this->upload->data();
            $saved[] = 'assets/images/housing_news/' . $u['file_name'];
        }

        return $saved;
    }

    public function banners()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Site banners';
        $data['rows'] = $this->Banner_model->get_all_for_admin();
        $data['admin_nav'] = 'banners';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/banners', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function banner_add()
    {
        $this->banner_edit(0);
    }

    public function banner_edit($id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($this->input->method() === 'post') {
            $status = strtolower(trim((string) $this->input->post('status')));
            if ($status !== 'active' && $status !== 'inactive') {
                $status = 'inactive';
            }
            $payload = array('status' => $status);

            if (!empty($_FILES['image']['name'])) {
                $uploadPath = FCPATH . 'assets/uploads/site_banners/';
                if (!is_dir($uploadPath)) {
                    @mkdir($uploadPath, 0755, true);
                }
                $config = array(
                    'upload_path' => $uploadPath,
                    'allowed_types' => 'jpg|jpeg|png|webp',
                    'max_size' => 2048,
                    'encrypt_name' => true,
                );
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('image')) {
                    $this->session->set_flashdata('nb_err', trim(strip_tags($this->upload->display_errors('', ''))));
                    redirect($id > 0 ? ('panel/banner/edit/' . $id) : 'panel/banner/add');
                    return;
                }
                $payload['image'] = 'assets/uploads/site_banners/' . $this->upload->data('file_name');
            }

            if ($id > 0) {
                $row = $this->Banner_model->get_by_id($id);
                if (!$row) {
                    show_404();
                }
                if (!empty($payload['image'])) {
                    $old = $this->Banner_model->row_image_path($row);
                    if ($old !== '' && file_exists(FCPATH . ltrim($old, '/'))) {
                        @unlink(FCPATH . ltrim($old, '/'));
                    }
                }
                $this->Banner_model->update($id, $payload);
                $this->session->set_flashdata('nb_ok', 'Banner updated.');
            } else {
                if (empty($payload['image'])) {
                    $this->session->set_flashdata('nb_err', 'Banner image is required.');
                    redirect('panel/banner/add');
                    return;
                }
                $this->Banner_model->create($payload);
                $this->session->set_flashdata('nb_ok', 'Banner created.');
            }
            redirect('panel/banners');
            return;
        }

        $row = null;
        if ($id > 0) {
            $row = $this->Banner_model->get_by_id($id);
            if (!$row) {
                show_404();
            }
        }
        $data['page_title'] = $id > 0 ? 'Edit banner' : 'Add banner';
        $data['row'] = $row;
        $data['edit_id'] = $id;
        $data['admin_nav'] = 'banners';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/banner_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function banner_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        $row = $this->Banner_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        $img = $this->Banner_model->row_image_path($row);
        if ($img !== '' && file_exists(FCPATH . ltrim($img, '/'))) {
            @unlink(FCPATH . ltrim($img, '/'));
        }
        $this->Banner_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Banner deleted.');
        redirect('panel/banners');
    }

    public function banner_toggle($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        $row = $this->Banner_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        $newStatus = ((string) $row->status === 'active') ? 'inactive' : 'active';
        $this->Banner_model->update($id, array('status' => $newStatus));
        $this->session->set_flashdata('nb_ok', 'Banner status updated.');
        redirect('panel/banners');
    }

    public function feedbacks()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Feedback';
        $filterUid = $this->input->get('userId');
        if ($filterUid === null || $filterUid === '') {
            $filterUid = $this->input->get('user_id');
        }
        $filterUid = $filterUid !== null ? trim((string) $filterUid) : '';
        $data['filter_userId'] = $filterUid;
        $data['rows'] = $this->Feedback_model->get_all(
            $filterUid !== '' ? $filterUid : null,
            null,
            0
        );
        $data['feedback_users'] = $this->_feedback_users_map($data['rows']);
        $data['admin_nav'] = 'feedbacks';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/feedbacks', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    /** GET /panel/api-collection — Postman-style API tester (admin layout). */
    public function api_collection()
    {
        $this->require_login();
        $this->require_role('admin');
        $this->load->config('api_catalog');
        $catalog = $this->config->item('api_catalog');
        if (!is_array($catalog)) {
            $catalog = array('groups' => array());
        }
        $data = array(
            'page_title'   => 'API Collection',
            'admin_nav'    => 'api_collection',
            'base_api_url' => rtrim(base_url(), '/'),
            'catalog'      => $catalog,
            'catalog_json' => json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'embed_admin'  => true,
        );
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/developer/api_tester', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    private function _feedback_users_map($rows)
    {
        $map = array();
        if (!is_array($rows) || empty($rows)) {
            return $map;
        }
        foreach ($rows as $r) {
            $uid = isset($r->userId) ? trim((string) $r->userId) : '';
            if ($uid === '' || isset($map[$uid])) {
                continue;
            }
            $user = null;
            if (ctype_digit($uid)) {
                $user = $this->Nb_user_model->get_by_id((int) $uid);
            }
            if (!$user) {
                $user = $this->Nb_user_model->get_by_email_or_phone($uid);
            }
            if ($user) {
                $map[$uid] = array(
                    'name' => isset($user->name) ? (string) $user->name : '',
                    'phone' => isset($user->phone) ? (string) $user->phone : '',
                    'email' => isset($user->email) ? (string) $user->email : '',
                );
            }
        }
        return $map;
    }

    public function notifications()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Notifications';
        $data['admin_nav'] = 'notifications';
        $data['notification_base'] = 'panel';
        $data['notifications'] = $this->Notification_model->get_all_for_admin();
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('admin/notifications/list', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function notification_create()
    {
        $this->require_login();
        $this->require_role('admin');
        if ($this->input->method() === 'post') {
            $data = array(
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => $this->input->post('status') ?: 'active'
            );
            $err = $this->_notification_merge_uploads($data, null);
            if ($err !== null) {
                $this->session->set_flashdata('nb_err', $err);
                redirect('panel/notification/create');
                return;
            }
            $this->Notification_model->create($data);
            $this->load->library('firebase');
            $image_url = !empty($data['image']) ? base_url($data['image']) : null;
            $video_url = (!empty($data['video']) && $this->db->field_exists('video', 'notifications'))
                ? base_url($data['video']) : null;
            $this->firebase->send_notification(
                (string) $data['title'],
                isset($data['description']) ? (string) $data['description'] : '',
                $image_url,
                array('type' => 'notification'),
                'all_users',
                $video_url
            );
            $this->session->set_flashdata('nb_ok', 'Notification sent.');
            redirect('panel/notifications');
            return;
        }
        $data['page_title'] = 'Create notification';
        $data['admin_nav'] = 'notifications';
        $data['notification_base'] = 'panel';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('admin/notifications/create', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function notification_edit($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1) {
            show_404();
        }
        $data['notification'] = $this->Notification_model->get_by_id($id);
        if (!$data['notification']) {
            show_404();
        }
        if ($this->input->method() === 'post') {
            $update_data = array(
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => $this->input->post('status') ?: 'active'
            );
            $err = $this->_notification_merge_uploads($update_data, $data['notification']);
            if ($err !== null) {
                $this->session->set_flashdata('nb_err', $err);
                redirect('panel/notification/edit/' . $id);
                return;
            }
            if (empty($_FILES['image']['name']) && !empty($this->input->post('existing_image'))) {
                $update_data['image'] = $this->input->post('existing_image');
            }
            if ($this->db->field_exists('video', 'notifications')) {
                if (empty($_FILES['video']['name']) && !empty($this->input->post('existing_video'))) {
                    $update_data['video'] = $this->input->post('existing_video');
                }
            }
            $this->Notification_model->update($id, $update_data);
            $this->session->set_flashdata('nb_ok', 'Notification updated.');
            redirect('panel/notifications');
            return;
        }
        $data['page_title'] = 'Edit notification';
        $data['admin_nav'] = 'notifications';
        $data['notification_base'] = 'panel';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('admin/notifications/edit', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function notification_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1) {
            show_404();
        }
        $row = $this->Notification_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        if (!empty($row->image) && file_exists(FCPATH . $row->image)) {
            @unlink(FCPATH . $row->image);
        }
        if (!empty($row->video) && file_exists(FCPATH . $row->video)) {
            @unlink(FCPATH . $row->video);
        }
        $this->Notification_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Notification deleted.');
        redirect('panel/notifications');
    }

    public function notification_toggle($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1) {
            show_404();
        }
        $row = $this->Notification_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        $newStatus = ((string) $row->status === 'active') ? 'inactive' : 'active';
        $this->Notification_model->update($id, array('status' => $newStatus));
        $this->session->set_flashdata('nb_ok', 'Notification status updated.');
        redirect('panel/notifications');
    }

    private function _notification_merge_uploads(&$data, $previous = null)
    {
        if (!empty($_FILES['image']['name'])) {
            $config = array(
                'upload_path' => './assets/images/notifications/',
                'allowed_types' => 'gif|jpg|png|jpeg|webp',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            if (!is_dir($config['upload_path'])) {
                @mkdir($config['upload_path'], 0777, true);
            }
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('image')) {
                return strip_tags($this->upload->display_errors('', ''));
            }
            if ($previous && !empty($previous->image) && file_exists(FCPATH . $previous->image)) {
                @unlink(FCPATH . $previous->image);
            }
            $data['image'] = 'assets/images/notifications/' . $this->upload->data('file_name');
        }

        if (!empty($_FILES['video']['name']) && $this->db->field_exists('video', 'notifications')) {
            $vconfig = array(
                'upload_path' => './assets/videos/notifications/',
                'allowed_types' => 'mp4|webm|mov',
                'max_size' => 30720,
                'encrypt_name' => true,
            );
            if (!is_dir($vconfig['upload_path'])) {
                @mkdir($vconfig['upload_path'], 0777, true);
            }
            if (!isset($this->upload)) {
                $this->load->library('upload', $vconfig);
            } else {
                $this->upload->initialize($vconfig);
            }
            if (!$this->upload->do_upload('video')) {
                return strip_tags($this->upload->display_errors('', ''));
            }
            if ($previous && !empty($previous->video) && file_exists(FCPATH . $previous->video)) {
                @unlink(FCPATH . $previous->video);
            }
            $data['video'] = 'assets/videos/notifications/' . $this->upload->data('file_name');
        }
        return null;
    }

    public function property_type_add()
    {
        $this->property_type_edit(0);
    }

    public function property_type_add_sub($parent_id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        $parent_id = (int) $parent_id;
        $parent = $this->Nb_property_type_model->get_by_id($parent_id);
        if (!$parent || !$this->Nb_property_type_model->is_main_type($parent)) {
            $this->session->set_flashdata('nb_err', 'Select a valid main type first.');
            redirect('panel/property-types');
            return;
        }
        redirect('panel/property-type/add?parent_id=' . $parent_id);
    }

    public function property_type_edit($id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($this->input->method() === 'post') {
            $this->_property_type_save();
            return;
        }
        $row = null;
        if ($id > 0) {
            $row = $this->Nb_property_type_model->get_by_id($id);
            if (!$row) {
                show_404();
            }
        }
        $parent_id = 0;
        if ($id === 0) {
            $parent_id = (int) $this->input->get('parent_id');
        } elseif ($row && !$this->Nb_property_type_model->is_main_type($row)) {
            $parent_id = (int) $row->parent_id;
        }
        $parent_row = null;
        if ($parent_id > 0) {
            $parent_row = $this->Nb_property_type_model->get_by_id($parent_id);
            if (!$parent_row || !$this->Nb_property_type_model->is_main_type($parent_row)) {
                $parent_id = 0;
                $parent_row = null;
            }
        }
        $is_sub = ($id > 0 && $row && !$this->Nb_property_type_model->is_main_type($row)) || ($id === 0 && $parent_id > 0);
        $data['page_title'] = $id > 0
            ? ($is_sub ? 'Edit sub type' : 'Edit main type')
            : ($is_sub ? 'Add sub type' : 'Add main type');
        $data['row'] = $row;
        $data['edit_id'] = $id;
        $data['parent_id'] = $parent_id;
        $data['parent_row'] = $parent_row;
        $data['is_sub_type'] = $is_sub;
        $data['main_types'] = $this->Nb_property_type_model->main_types(false);
        $data['admin_nav'] = 'property_types';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/property_type_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function property_type_toggle($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        $result = $this->Nb_property_type_model->toggle_active($id);
        if ($result === null) {
            if ($this->input->is_ajax_request()) {
                return $this->_panel_json(array('success' => false, 'message' => 'Not found'), 404);
            }
            $this->session->set_flashdata('nb_err', 'Property type not found.');
            redirect('panel/property-types');
            return;
        }
        if ($this->input->is_ajax_request() || $this->input->post('ajax')) {
            return $this->_panel_json(array(
                'success' => true,
                'id' => $id,
                'is_active' => (int) $result['is_active'],
            ));
        }
        $this->session->set_flashdata('nb_ok', 'Property type status updated.');
        redirect('panel/property-types');
    }

    public function property_type_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        $row = $this->Nb_property_type_model->get_by_id($id);
        if (!$row) {
            show_404();
        }
        if ($this->Nb_property_type_model->count_references($row->slug) > 0) {
            $this->session->set_flashdata('nb_err', 'This property type is used by existing listings. Deactivate it instead.');
            redirect('panel/property-types');
            return;
        }
        if ($this->Nb_property_type_model->count_children($id) > 0) {
            $this->session->set_flashdata('nb_err', 'Delete sub types under this main type first.');
            redirect('panel/property-types');
            return;
        }
        $this->Nb_property_type_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Property type deleted.');
        redirect('panel/property-types');
    }

    public function amenity_add()
    {
        $this->amenity_edit(0);
    }

    public function amenity_edit($id = 0)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($this->input->method() === 'post') {
            $this->_amenity_save();
            return;
        }
        $row = null;
        if ($id > 0) {
            $row = $this->Nb_amenity_model->get_by_id($id);
            if (!$row) {
                show_404();
            }
        }
        $data['page_title'] = $id > 0 ? 'Edit amenity' : 'Add amenity';
        $data['row'] = $row;
        $data['edit_id'] = $id;
        $data['admin_nav'] = 'amenities';
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/amenity_form', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function amenity_delete($id = null)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        $this->Nb_amenity_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Amenity deleted. Existing listings may still show the old label in stored data.');
        redirect('panel/amenities');
    }

    private function _amenity_options_for_form()
    {
        if (!$this->db->table_exists('nb_amenities')) {
            return array();
        }
        return $this->Nb_amenity_model->all_active();
    }

    private function _live_update_save_admin($id, $existing)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('title', 'Title', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('platform', 'Platform', 'required|in_list[youtube,instagram,app]');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[cancelled,upcoming,live_started,reschedule]');
        $this->form_validation->set_rules('url', 'URL', 'trim|max_length[500]');
        $this->form_validation->set_rules('image', 'Image URL', 'trim|max_length[500]');
        $this->form_validation->set_rules('liveTime', 'Live time', 'trim');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('nb_err', validation_errors(' ', ' '));
            return false;
        }

        $liveTimeRaw = trim((string) $this->input->post('liveTime', true));
        $liveTime = null;
        if ($liveTimeRaw !== '') {
            $ts = strtotime($liveTimeRaw);
            if ($ts === false) {
                $this->session->set_flashdata('nb_err', 'Invalid live time format.');
                return false;
            }
            $liveTime = date('Y-m-d H:i:s', $ts);
        }

        $data = array(
            'title' => $this->security->xss_clean($this->input->post('title', true)),
            'description' => $this->security->xss_clean($this->input->post('description', true)),
            'platform' => $this->input->post('platform', true),
            'status' => $this->input->post('status', true) ?: 'upcoming',
            'url' => $this->security->xss_clean($this->input->post('url', true)),
            'image' => $this->security->xss_clean($this->input->post('image', true)),
            'liveTime' => $liveTime,
        );

        if (!empty($_FILES['image_file']['name'])) {
            $upload_dir = FCPATH . 'assets/uploads/live_updates/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }
            $cfg = array(
                'upload_path' => $upload_dir,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            $this->load->library('upload', $cfg);
            if (!$this->upload->do_upload('image_file')) {
                $this->session->set_flashdata('nb_err', strip_tags($this->upload->display_errors('', '')));
                return false;
            }
            $u = $this->upload->data();
            $data['image'] = 'assets/uploads/live_updates/' . $u['file_name'];
        }

        if ($this->db->field_exists('updatedAt', 'live_updates')) {
            $data['updatedAt'] = date('Y-m-d H:i:s');
        }
        $this->Live_update_model->update((int) $id, $data);
        return true;
    }

    private function _city_save()
    {
        $id = (int) $this->input->post('city_id');
        $existing = null;
        if ($id > 0) {
            $existing = $this->Nb_city_model->get_by_id($id);
        }
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'City name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('state', 'State', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('sort_order', 'Sort order', 'integer');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('nb_err', validation_errors(' ', ' '));
            redirect($id > 0 ? 'panel/city/edit/' . $id : 'panel/city/add');
            return;
        }
        $data = array(
            'name'       => $this->security->xss_clean($this->input->post('name', true)),
            'state'      => $this->security->xss_clean($this->input->post('state', true)),
            'sort_order' => $this->input->post('sort_order') !== '' ? (int) $this->input->post('sort_order') : 0,
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
        );
        if (!empty($_FILES['image']['name']) && $this->db->field_exists('image', 'nb_cities')) {
            $config = array(
                'upload_path' => './assets/images/city/',
                'allowed_types' => 'gif|jpg|jpeg|png|webp',
                'max_size' => 2048,
                'encrypt_name' => true,
            );
            if (!is_dir($config['upload_path'])) {
                @mkdir($config['upload_path'], 0777, true);
            }
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('image')) {
                $this->session->set_flashdata('nb_err', 'Image upload failed: ' . strip_tags($this->upload->display_errors('', '')));
                redirect($id > 0 ? 'panel/city/edit/' . $id : 'panel/city/add');
                return;
            }
            $uploaded = $this->upload->data();
            $data['image'] = 'assets/images/city/' . $uploaded['file_name'];
            if ($existing && !empty($existing->image) && file_exists(FCPATH . $existing->image)) {
                @unlink(FCPATH . $existing->image);
            }
        }
        if ($id > 0) {
            $this->Nb_city_model->update($id, $data);
            $this->session->set_flashdata('nb_ok', 'City updated.');
        } else {
            $this->Nb_city_model->create($data);
            $this->session->set_flashdata('nb_ok', 'City added.');
        }
        redirect('panel/cities');
    }

    private function _amenity_save()
    {
        $id = (int) $this->input->post('amenity_id');
        $this->load->helper('nb');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[120]');
        $this->form_validation->set_rules('sort_order', 'Sort order', 'integer');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('nb_err', validation_errors(' ', ' '));
            redirect($id > 0 ? 'panel/amenity/edit/' . $id : 'panel/amenity/add');
            return;
        }
        $name = $this->security->xss_clean($this->input->post('name', true));
        $slug_in = trim((string) $this->input->post('slug', true));
        $slug = $slug_in !== '' ? nb_slugify($slug_in) : $this->Nb_amenity_model->unique_slug($name, $id > 0 ? $id : null);
        if ($slug === 'property') {
            $slug = 'amenity';
        }
        $existing = $this->Nb_amenity_model->get_by_slug($slug);
        if ($existing && (int) $existing->id !== $id) {
            $slug = $this->Nb_amenity_model->unique_slug($name . ' ' . $id, $id > 0 ? $id : null);
        }
        $data = array(
            'name'       => $name,
            'slug'       => $slug,
            'sort_order' => $this->input->post('sort_order') !== '' ? (int) $this->input->post('sort_order') : 0,
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
        );
        if ($id > 0) {
            $this->Nb_amenity_model->update($id, $data);
            $this->session->set_flashdata('nb_ok', 'Amenity updated.');
        } else {
            $this->Nb_amenity_model->create($data);
            $this->session->set_flashdata('nb_ok', 'Amenity added.');
        }
        redirect('panel/amenities');
    }

    private function _property_type_save()
    {
        $id = (int) $this->input->post('property_type_id');
        $this->load->helper('nb');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[120]');
        $this->form_validation->set_rules('sort_order', 'Sort order', 'integer');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('nb_err', validation_errors(' ', ' '));
            redirect($id > 0 ? 'panel/property-type/edit/' . $id : 'panel/property-type/add');
            return;
        }
        $name = $this->security->xss_clean($this->input->post('name', true));
        $slug_in = trim((string) $this->input->post('slug', true));
        $slug = $slug_in !== '' ? nb_slugify($slug_in) : nb_slugify($name);
        if ($slug === 'property') {
            $slug = 'property-type';
        }
        $existing = $this->Nb_property_type_model->get_by_slug($slug);
        if ($existing && (int) $existing->id !== $id) {
            $suffix = 2;
            $base = $slug;
            while ($this->Nb_property_type_model->get_by_slug($slug)) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }
        }

        $is_sub = $this->input->post('is_sub_type') ? 1 : 0;
        $parent_id = null;
        if ($is_sub) {
            $parent_id = (int) $this->input->post('parent_id');
            $parent = $this->Nb_property_type_model->get_by_id($parent_id);
            if (!$parent || !$this->Nb_property_type_model->is_main_type($parent)) {
                $this->session->set_flashdata('nb_err', 'Choose a valid main type for this sub type.');
                redirect($id > 0 ? 'panel/property-type/edit/' . $id : 'panel/property-type/add');
                return;
            }
        } elseif ($id > 0) {
            $existing_row = $this->Nb_property_type_model->get_by_id($id);
            if ($existing_row && !$this->Nb_property_type_model->is_main_type($existing_row)) {
                $parent_id = (int) $existing_row->parent_id;
                $is_sub = 1;
            }
        }

        if ($id > 0 && $this->Nb_property_type_model->is_main_type($this->Nb_property_type_model->get_by_id($id)) && $is_sub) {
            $this->session->set_flashdata('nb_err', 'A main type cannot be converted to a sub type while it has sub types.');
            redirect('panel/property-type/edit/' . $id);
            return;
        }

        $data = array(
            'name'       => $name,
            'slug'       => $slug,
            'sort_order' => $this->input->post('sort_order') !== '' ? (int) $this->input->post('sort_order') : 0,
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
        );
        if ($this->Nb_property_type_model->has_parent_column()) {
            $data['parent_id'] = $is_sub ? $parent_id : null;
        }
        if ($id > 0) {
            $this->Nb_property_type_model->update($id, $data);
            $this->session->set_flashdata('nb_ok', 'Property type updated.');
        } else {
            $this->Nb_property_type_model->create($data);
            $this->session->set_flashdata('nb_ok', $is_sub ? 'Sub type added.' : 'Main type added.');
        }
        redirect('panel/property-types');
    }

    /**
     * @return bool true if user was created
     */
    private function _user_save()
    {
        $this->form_validation->set_rules('name', 'Full name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|max_length[200]|callback_user_email_available');
        $this->form_validation->set_rules('phone', 'Phone', 'required|trim|max_length[15]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('password2', 'Confirm password', 'required|matches[password]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[owner,tenant,admin]');
        $this->form_validation->set_rules('city_id', 'City', 'integer');
        if (!$this->form_validation->run()) {
            return false;
        }
        $email = strtolower(trim((string) $this->input->post('email', true)));
        $cid = $this->input->post('city_id');
        $insert = array(
            'name'     => $this->security->xss_clean($this->input->post('name', true)),
            'email'    => $email,
            'phone'    => $this->security->xss_clean($this->input->post('phone', true)),
            'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
            'role'     => $this->input->post('role'),
            'status'   => 'approved',
            'city_id'  => $cid ? (int) $cid : null,
        );
        if ($this->db->field_exists('is_verified', 'nb_users')) {
            $insert['is_verified'] = 1;
        }
        $this->Nb_user_model->create($insert);
        return true;
    }

    /**
     * POST panel JSON — user approve/reject (avoids api/* resolving to controllers/Api.php).
     */
    public function approve_user()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_panel_nb_admin()) {
            return $this->_panel_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $uid = (int) $this->input->post('user_id');
        if ($uid < 1) {
            return $this->_panel_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        $update = array('status' => 'approved');
        if ($this->db->field_exists('is_verified', 'nb_users') && $this->input->post('verified') !== null) {
            $update['is_verified'] = ((int) $this->input->post('verified') === 1) ? 1 : 0;
        }
        $this->Nb_user_model->update($uid, $update);
        return $this->_panel_json(array('success' => true));
    }

    /**
     * POST panel JSON — publish property (is_active = 1).
     */
    public function approve_property()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_panel_nb_admin()) {
            return $this->_panel_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $pid = (int) $this->input->post('property_id');
        if ($pid < 1) {
            return $this->_panel_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        $p = $this->Nb_property_model->get_by_id($pid);
        if (!$p) {
            return $this->_panel_json(array('success' => false, 'message' => 'Not found'), 404);
        }
        if (!$this->db->field_exists('is_active', 'nb_properties')) {
            return $this->_panel_json(array('success' => false, 'message' => 'Not supported'), 500);
        }
        $update = array_merge(array('is_active' => 1), $this->Nb_property_model->slug_publish_patch($p));
        $this->Nb_property_model->update($pid, $update);
        return $this->_panel_json(array('success' => true));
    }

    /**
     * POST panel JSON — update enquiry (admin).
     */
    public function update_enquiry()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_panel_nb_admin()) {
            return $this->_panel_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $eid = (int) $this->input->post('enquiry_id');
        $status = $this->input->post('status');
        $notes = $this->input->post('admin_notes', true);
        if ($eid < 1 || !in_array($status, array('new', 'read', 'responded', 'closed'), true)) {
            return $this->_panel_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        $row = $this->Nb_enquiry_model->get_by_id($eid);
        if (!$row) {
            return $this->_panel_json(array('success' => false, 'message' => 'Not found'), 404);
        }
        $upd = array(
            'status'      => $status,
            'admin_notes' => $notes,
        );
        if (isset($_POST['message'])) {
            $m = trim((string) $this->input->post('message', true));
            if ($m === '') {
                return $this->_panel_json(array('success' => false, 'message' => 'Message cannot be empty'), 400);
            }
            $upd['message'] = $m;
        }
        if (isset($_POST['phone'])) {
            $phone = trim((string) $this->input->post('phone'));
            $upd['phone'] = $this->security->xss_clean(substr($phone, 0, 15));
        }
        if (isset($_POST['email'])) {
            $email = trim((string) $this->input->post('email'));
            $upd['email'] = $this->security->xss_clean(substr($email, 0, 200));
        }
        $this->Nb_enquiry_model->update($eid, $upd);
        return $this->_panel_json(array('success' => true));
    }

    /**
     * POST panel JSON — delete enquiry.
     */
    public function delete_enquiry()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->_panel_nb_admin()) {
            return $this->_panel_json(array('success' => false, 'message' => 'Forbidden'), 403);
        }
        $eid = (int) $this->input->post('enquiry_id');
        if ($eid < 1) {
            return $this->_panel_json(array('success' => false, 'message' => 'Invalid input'), 400);
        }
        if (!$this->Nb_enquiry_model->get_by_id($eid)) {
            return $this->_panel_json(array('success' => false, 'message' => 'Not found'), 404);
        }
        $this->Nb_enquiry_model->delete($eid);
        return $this->_panel_json(array('success' => true));
    }

    private function _panel_nb_admin()
    {
        $u = $this->session->userdata('nb_user');

        return $u && $u['role'] === 'admin' && $u['status'] === 'approved';
    }

    private function _panel_json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    public function user_email_available($email)
    {
        $email = strtolower(trim((string) $email));
        if ($email === '') {
            return true;
        }
        if ($this->Nb_user_model->get_by_email($email)) {
            $this->form_validation->set_message('user_email_available', 'That email is already registered.');
            return false;
        }
        return true;
    }

    private function _user_update($id, $existing)
    {
        $new_email = strtolower(trim((string) $this->input->post('email', true)));
        $email_rules = 'required|valid_email|trim|max_length[200]';
        if ($new_email !== strtolower(trim((string) $existing->email))) {
            $email_rules .= '|callback_user_email_available';
        }
        $this->form_validation->set_rules('name',   'Full name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('email',  'Email',     $email_rules);
        $this->form_validation->set_rules('phone',  'Phone',     'required|trim|max_length[15]');
        $this->form_validation->set_rules('role',   'Role',      'required|in_list[owner,tenant,admin]');
        $this->form_validation->set_rules('status', 'Status',    'required|in_list[pending,approved,rejected]');
        $this->form_validation->set_rules('city_id','City',      'integer');
        $pw = $this->input->post('password');
        if (!empty($pw)) {
            $this->form_validation->set_rules('password',  'Password',         'required|min_length[6]');
            $this->form_validation->set_rules('password2', 'Confirm password', 'required|matches[password]');
        }
        if (!$this->form_validation->run()) {
            return false;
        }
        $cid = $this->input->post('city_id');
        $update = array(
            'name'    => $this->security->xss_clean($this->input->post('name', true)),
            'email'   => $new_email,
            'phone'   => $this->security->xss_clean($this->input->post('phone', true)),
            'role'    => $this->input->post('role'),
            'status'  => $this->input->post('status'),
            'city_id' => $cid ? (int) $cid : null,
        );
        if ($this->db->field_exists('user_type', 'nb_users')) {
            $ut = $this->input->post('user_type');
            if (in_array($ut, array('agent', 'customer'), true)) {
                $update['user_type'] = $ut;
            }
        }
        if (!empty($pw)) {
            $update['password'] = password_hash($pw, PASSWORD_BCRYPT);
        }
        if ($this->db->field_exists('is_verified', 'nb_users')) {
            $update['is_verified'] = $this->input->post('is_verified') ? 1 : 0;
        }
        $this->Nb_user_model->update($id, $update);
        return true;
    }

    public function delete_requests()
    {
        $this->require_login();
        $this->require_role('admin');
        $data['page_title'] = 'Delete Requests';
        $data['admin_nav'] = 'delete_requests';
        $data['requests'] = $this->Nb_delete_request_model->get_all();
        $this->load->view('nobroker/admin/header', $data);
        $this->load->view('nobroker/admin/delete_requests', $data);
        $this->load->view('nobroker/admin/footer', $data);
    }

    public function delete_request_update_status($id)
    {
        $this->require_login();
        $this->require_role('admin');
        $id = (int) $id;
        if ($id < 1 || $this->input->method() !== 'post') {
            show_404();
        }
        $status = $this->input->post('status');
        if (!in_array($status, array('pending', 'reviewed', 'done'), true)) {
            $this->session->set_flashdata('nb_err', 'Invalid status.');
            redirect('panel/delete-requests');
            return;
        }
        $this->Nb_delete_request_model->update_status($id, $status);
        $this->session->set_flashdata('nb_ok', 'Status updated successfully.');
        redirect('panel/delete-requests');
    }

    /** Remove uploaded files for a listing before DB delete. */
    private function _delete_property_upload_files($row)
    {
        $paths = array();
        foreach (array('location_image', 'brochure_url', 'audio_notes_url', 'home_banner_image') as $field) {
            if (!empty($row->$field)) {
                $paths[] = trim((string) $row->$field);
            }
        }
        if (!empty($row->images)) {
            $imgs = json_decode((string) $row->images, true);
            if (is_array($imgs)) {
                foreach ($imgs as $img) {
                    $img = trim((string) $img);
                    if ($img !== '') {
                        $paths[] = $img;
                    }
                }
            }
        }
        foreach ($paths as $rel) {
            if (strpos($rel, 'assets/uploads/nb_properties/') !== 0) {
                continue;
            }
            $full = FCPATH . $rel;
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}
