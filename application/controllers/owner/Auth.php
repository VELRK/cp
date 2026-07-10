<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bridge Next.js / API token login into PHP session for owner panel pages.
 * GET owner/auth?token=...&return=/owner/dashboard
 */
class Auth extends MY_Controller {

    public function index()
    {
        $this->load->library('nb_api_token');
        $this->load->model('Nb_user_model');
        $this->load->helper('nb');

        $return = nb_normalize_return_url($this->input->get('return', true));
        $token = $this->nb_api_token->read_token_from_request();

        if ($this->session->userdata('nb_user_id')) {
            $u = $this->nb_user();
            if ($u && $u['role'] === 'owner' && isset($u['status']) && $u['status'] === 'approved') {
                redirect($return);
                return;
            }
        }

        if ($token === '') {
            redirect(base_url() . '?modal=login&redirect=' . rawurlencode($return));
            return;
        }

        if (!$this->db->field_exists('api_token', 'nb_users')) {
            show_error('Server setup incomplete: api_token column is missing.', 500);
            return;
        }

        $user = $this->Nb_user_model->get_by_api_token($token);
        if (!$user) {
            redirect(base_url() . '?modal=login&redirect=' . rawurlencode($return));
            return;
        }

        if ((string) $user->role !== 'owner') {
            show_error('Owner access only. Your account role is: ' . html_escape((string) $user->role), 403);
            return;
        }

        if ($user->status !== 'approved') {
            show_error('Your account is not active. Contact support.', 403);
            return;
        }

        $this->set_nb_session_from_user($user);
        nb_set_api_token_cookie($token);
        redirect($return);
    }
}
