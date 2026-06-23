<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller for Coimbatore Properties module (session keys: nb_user_id, nb_user).
 */
class MY_Controller extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
    }

    /** @return array|null */
    protected function nb_user()
    {
        return $this->session->userdata('nb_user');
    }

    protected function set_nb_session_from_user($user)
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

    protected function require_login()
    {
        $this->load->library('nb_api_token');
        $this->nb_api_token->try_attach_session();
        if (!$this->session->userdata('nb_user_id')) {
            redirect(base_url() . '?modal=login');
        }
    }

    protected function require_role($roles)
    {
        $this->require_login();
        $roles = (array) $roles;
        $u = $this->nb_user();
        if (!$u || !in_array($u['role'], $roles, true)) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    protected function require_approved()
    {
        $this->require_login();
        $u = $this->nb_user();
        if (!$u) {
            redirect(base_url() . '?modal=login');
            return;
        }
        if (!isset($u['status']) || $u['status'] !== 'approved') {
            show_error('Your account is not active. Contact support.', 403);
        }
    }

    protected function set_nb_flash($type, $message)
    {
        $this->session->set_flashdata('nb_flash', array('type' => $type, 'message' => $message));
    }
}
