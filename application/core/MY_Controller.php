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
        $this->load->helper('nb');
        $this->session->set_userdata('nb_user_id', (int) $user->id);
        $this->session->set_userdata('nb_user', nb_session_user_array($user));
    }

    protected function require_login()
    {
        $this->load->library('nb_api_token');
        $this->nb_api_token->try_attach_session();
        if (!$this->session->userdata('nb_user_id')) {
            $uri = (string) $this->uri->uri_string();
            if ($uri === 'panel' || strpos($uri, 'panel/') === 0) {
                $this->session->set_userdata('panel_return', current_url());
                redirect(site_url('admin'));
                return;
            }
            if ($uri === 'owner/auth' || strpos($uri, 'owner/auth/') === 0) {
                redirect(base_url() . '?modal=login');
                return;
            }
            if ($uri === 'owner' || strpos($uri, 'owner/') === 0) {
                redirect(site_url('owner/auth') . '?return=' . rawurlencode(current_url()));
                return;
            }
            redirect(base_url() . '?modal=login');
        }
    }

    /**
     * Panel role used for access checks: admin | owner | tenant.
     * Agents (user_type=agent or API role agent) share the owner panel.
     */
    protected function nb_access_role($u = null)
    {
        if ($u === null) {
            $u = $this->nb_user();
        }
        $this->load->helper('nb');
        return nb_access_role($u);
    }

    protected function require_role($roles)
    {
        $this->require_login();
        $roles = (array) $roles;
        $u = $this->nb_user();
        $access = $this->nb_access_role($u);
        $raw = ($u && isset($u['role'])) ? strtolower(trim((string) $u['role'])) : '';
        $ok = in_array($access, $roles, true) || in_array($raw, $roles, true);
        if ($ok) {
            return;
        }
        // Wrong-role links after frontend login should land on the user's own dashboard, not a 403.
        if ($access === 'admin') {
            redirect('panel');
            return;
        }
        if ($access === 'owner') {
            redirect('owner/dashboard');
            return;
        }
        redirect('tenant/dashboard');
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
