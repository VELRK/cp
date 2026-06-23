<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_auth extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->model('Nb_user_model');
    }

    public function login()
    {
        $is_ajax = $this->_wants_json();
        
        if ($this->session->userdata('nb_user_id')) {
            if ($is_ajax) {
                return $this->_json_response(array('success' => true, 'message' => 'Already logged in.', 'redirect' => $this->_get_redirect_url_by_role()));
            }
            $this->_redirect_by_role();
            return;
        }

        if ($this->input->method() !== 'post') {
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => 'Invalid request method.'), 405);
            }
            redirect(base_url() . '?modal=login');
            return;
        }

        $lg = trim((string) $this->input->post('login', true));
        if ($lg === '') {
            $legacy = $this->input->post('email', true);
            if (is_string($legacy) && trim($legacy) !== '') {
                $_POST['login'] = $legacy;
            }
        }

        $this->_check_login_rate();
        $this->form_validation->set_rules('login', 'Email or phone', 'required|trim|callback__valid_login_identifier');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if (!$this->form_validation->run()) {
            $err_msg = trim(strip_tags(validation_errors(' ', ' ')));
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => $err_msg), 422);
            }
            $this->session->set_flashdata('nb_err_html', validation_errors('<div class="alert alert-danger rounded-0 mb-0 border-0">', '</div>'));
            redirect(base_url());
            return;
        }

        $login = $this->input->post('login', true);
        $password = $this->input->post('password');
        $user = $this->Nb_user_model->get_by_email_or_phone($login);

        if (!$user || !password_verify($password, $user->password)) {
            $err_msg = 'Invalid email, phone, or password.';
            if ($is_ajax) {
                $this->_bump_login_fail(false); // Pass false to avoid redirect
                return $this->_json_response(array('success' => false, 'message' => $err_msg), 401);
            }
            $this->session->set_flashdata('nb_err', $err_msg);
            $this->_bump_login_fail();
            return;
        }

        if ($user->status !== 'approved') {
            $err_msg = 'Your account is not active. Contact support.';
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => $err_msg), 403);
            }
            $this->session->set_flashdata('nb_err', $err_msg);
            redirect(base_url());
            return;
        }

        $this->session->sess_regenerate(true);
        $this->_set_nb_session($user);
        $this->_reset_login_fail();

        if ($is_ajax) {
            return $this->_json_response(array(
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => $this->_get_redirect_url_by_role()
            ));
        }

        $this->_redirect_by_role();
    }

    public function register()
    {
        $is_ajax = $this->_wants_json();
        if ($this->session->userdata('nb_user_id')) {
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => 'Already logged in.'), 409);
            }
            redirect('');
            return;
        }
        if ($this->input->method() !== 'post') {
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => 'Invalid request method.'), 405);
            }
            redirect(base_url() . '?modal=register');
            return;
        }
        $this->form_validation->set_rules('name', 'Full name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'required|trim|max_length[15]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('password2', 'Confirm', 'required|matches[password]');
        $this->form_validation->set_rules('city_id', 'City', 'integer');
        $this->form_validation->set_rules('user_type', 'User type', 'required|in_list[agent,customer]');
        if (!$this->form_validation->run()) {
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => trim(strip_tags(validation_errors(' ', ' ')))), 422);
            }
            $this->session->set_flashdata('nb_err_html', validation_errors('<div class="alert alert-danger rounded-0 mb-0 border-0">', '</div>'));
            redirect(base_url() . '?modal=register');
            return;
        }
        if (!$this->input->post('terms')) {
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => 'Please accept the terms.'), 422);
            }
            $this->session->set_flashdata('nb_err', 'Please accept the terms.');
            redirect(base_url() . '?modal=register');
            return;
        }
        if ($this->Nb_user_model->get_by_email($this->input->post('email'))) {
            if ($is_ajax) {
                return $this->_json_response(array('success' => false, 'message' => 'Email already registered.'), 409);
            }
            $this->session->set_flashdata('nb_err', 'Email already registered.');
            redirect(base_url() . '?modal=register');
            return;
        }
        $user_type = strtolower(trim((string) $this->input->post('user_type', true)));
        $is_agent = ($user_type === 'agent');
        $aadhar_no = trim((string) $this->input->post('aadhar_no', true));
        $experience_years = $this->input->post('experience_years', true);
        if ($is_agent) {
            if (!preg_match('/^\d{12}$/', preg_replace('/\D+/', '', $aadhar_no))) {
                if ($is_ajax) {
                    return $this->_json_response(array('success' => false, 'message' => 'Enter valid 12-digit Aadhar number for agent registration.'), 422);
                }
                $this->session->set_flashdata('nb_err', 'Enter valid 12-digit Aadhar number for agent registration.');
                redirect(base_url() . '?modal=register');
                return;
            }
            if ($experience_years === '' || !is_numeric($experience_years) || (int) $experience_years < 0 || (int) $experience_years > 60) {
                if ($is_ajax) {
                    return $this->_json_response(array('success' => false, 'message' => 'Enter valid experience in years (0-60).'), 422);
                }
                $this->session->set_flashdata('nb_err', 'Enter valid experience in years (0-60).');
                redirect(base_url() . '?modal=register');
                return;
            }
            if (empty($_FILES['aadhar_file']['name'])) {
                if ($is_ajax) {
                    return $this->_json_response(array('success' => false, 'message' => 'Aadhar file is required for agent registration.'), 422);
                }
                $this->session->set_flashdata('nb_err', 'Aadhar file is required for agent registration.');
                redirect(base_url() . '?modal=register');
                return;
            }
        }
        $aadhar_path = null;
        if ($is_agent && !empty($_FILES['aadhar_file']['name'])) {
            $upload_dir = FCPATH . 'assets/uploads/nb_users/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }
            $cfg = array(
                'upload_path'   => $upload_dir,
                'allowed_types' => 'jpg|jpeg|png|webp|pdf',
                'max_size'      => 5120,
                'encrypt_name'  => true,
            );
            $this->load->library('upload', $cfg);
            if (!$this->upload->do_upload('aadhar_file')) {
                if ($is_ajax) {
                    return $this->_json_response(array('success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))), 422);
                }
                $this->session->set_flashdata('nb_err', strip_tags($this->upload->display_errors('', '')));
                redirect(base_url() . '?modal=register');
                return;
            }
            $u = $this->upload->data();
            $aadhar_path = 'assets/uploads/nb_users/' . $u['file_name'];
        }
        $cid = $this->input->post('city_id');
        $insert = array(
            'name'     => $this->security->xss_clean($this->input->post('name', true)),
            'email'    => $this->input->post('email', true),
            'phone'    => $this->security->xss_clean($this->input->post('phone', true)),
            'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
            'role'     => $is_agent ? 'owner' : 'tenant',
            'status'   => 'approved',
            'city_id'  => $cid ? (int) $cid : null,
            'user_type' => $is_agent ? 'agent' : 'customer',
            'aadhar_no' => $is_agent ? preg_replace('/\D+/', '', $aadhar_no) : null,
            'aadhar_file' => $is_agent ? $aadhar_path : null,
            'experience_years' => $is_agent ? (int) $experience_years : null,
        );
        if ($this->db->field_exists('is_verified', 'nb_users')) {
            $insert['is_verified'] = 1;
        }
        $new_id = $this->Nb_user_model->create($insert);
        $new_user = $this->Nb_user_model->get_by_id($new_id);
        if ($new_user) {
            $this->session->sess_regenerate(true);
            $this->_set_nb_session($new_user);
        }
        if ($is_ajax) {
            return $this->_json_response(array(
                'success' => true,
                'message' => 'Registration successful.',
                'redirect' => $is_agent ? site_url('owner/dashboard') : site_url('tenant/dashboard'),
            ), 200);
        }
        $this->session->set_flashdata('nb_ok', 'Registration successful. Welcome!');
        $this->_redirect_by_role();
    }

    public function pending()
    {
        if (!$this->session->userdata('nb_user_id')) {
            redirect(base_url() . '?modal=login');
            return;
        }
        $u = $this->nb_user();
        if ($u && $u['status'] === 'approved') {
            $this->_redirect_by_role();
            return;
        }
        $data['page_title'] = 'Account pending';
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/auth/pending', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    public function logout()
    {
        $this->load->helper('nb');
        $this->load->library('nb_api_token');
        $token = $this->nb_api_token->read_token_from_request();
        if ($token !== '') {
            $this->load->model('Nb_user_model');
            $user = $this->Nb_user_model->get_by_api_token($token);
            if ($user) {
                $this->Nb_user_model->clear_api_token((int) $user->id);
            }
        }
        nb_clear_api_token_cookie();
        $this->session->unset_userdata(array('nb_user_id', 'nb_user'));
        nb_redirect_path('/');
    }

    private function _set_nb_session($user)
    {
        $this->session->set_userdata('nb_user_id', (int) $user->id);
        $this->session->set_userdata('nb_user', array(
            'id'     => (int) $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'phone'  => isset($user->phone) ? (string) $user->phone : '',
            'role'   => $user->role,
            'status' => $user->status,
        ));
    }

    private function _redirect_by_role()
    {
        redirect($this->_get_redirect_url_by_role());
    }

    private function _get_redirect_url_by_role()
    {
        $u = $this->nb_user();
        if (!$u) {
            return base_url() . '?modal=login';
        }
        if ($u['role'] === 'admin') {
            return 'panel';
        } elseif ($u['role'] === 'owner') {
            return 'owner/dashboard';
        } else {
            return 'tenant/dashboard';
        }
    }

    private function _check_login_rate()
    {
        $f = (int) $this->session->userdata('nb_login_fails');
        if ($f >= 5) {
            $until = $this->session->userdata('nb_login_lock_until');
            if ($until && time() < (int) $until) {
                show_error('Too many login attempts. Try again in 15 minutes.', 429);
            }
            $this->session->unset_userdata(array('nb_login_fails', 'nb_login_lock_until'));
        }
    }

    private function _bump_login_fail($should_redirect = true)
    {
        $f = (int) $this->session->userdata('nb_login_fails') + 1;
        $this->session->set_userdata('nb_login_fails', $f);
        if ($f >= 5) {
            $this->session->set_userdata('nb_login_lock_until', time() + 900);
        }
        if ($should_redirect) {
            redirect(base_url());
        }
    }

    private function _reset_login_fail()
    {
        $this->session->unset_userdata(array('nb_login_fails', 'nb_login_lock_until'));
    }

    private function _wants_json()
    {
        $xrw = strtolower((string) $this->input->server('HTTP_X_REQUESTED_WITH'));
        if ($xrw === 'xmlhttprequest') {
            return true;
        }
        $accept = strtolower((string) $this->input->server('HTTP_ACCEPT'));
        return strpos($accept, 'application/json') !== false;
    }

    private function _json_response($payload, $status = 200)
    {
        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    /**
     * Accept valid email or a phone with at least 10 digits.
     */
    public function _valid_login_identifier($str)
    {
        $str = trim((string) $str);
        if ($str === '') {
            $this->form_validation->set_message('_valid_login_identifier', 'Enter your email or phone number.');
            return false;
        }
        if (strpos($str, '@') !== false) {
            if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
            $this->form_validation->set_message('_valid_login_identifier', 'Please enter a valid email address.');
            return false;
        }
        $digits = preg_replace('/\D+/', '', $str);
        if (strlen($digits) < 10) {
            $this->form_validation->set_message('_valid_login_identifier', 'Please enter a valid phone number (at least 10 digits).');
            return false;
        }
        return true;
    }
}
