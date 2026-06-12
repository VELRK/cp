<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Attach nb_user session from Authorization: Bearer ... or X-Api-Token for mobile APIs.
 */
class Nb_api_token {

    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * If session has no nb user, try Bearer token and populate session for this request.
     */
    public function try_attach_session()
    {
        if ($this->CI->session->userdata('nb_user_id')) {
            return;
        }
        $token = $this->read_token_from_request();
        if ($token === '') {
            return;
        }
        $this->CI->load->model('Nb_user_model');
        if (!$this->CI->db->field_exists('api_token', 'nb_users')) {
            return;
        }
        $user = $this->CI->Nb_user_model->get_by_api_token($token);
        if (!$user) {
            return;
        }
        $this->CI->session->set_userdata('nb_user_id', (int) $user->id);
        $this->CI->session->set_userdata('nb_user', array(
            'id'     => (int) $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'phone'  => isset($user->phone) ? (string) $user->phone : '',
            'role'   => $user->role,
            'status' => $user->status,
        ));
    }

    /** @return string */
    public function read_token_from_request()
    {
        $hdr = $this->CI->input->server('HTTP_AUTHORIZATION');
        if (is_string($hdr) && preg_match('/Bearer\s+(\S+)/i', $hdr, $m)) {
            return trim($m[1]);
        }
        $x = $this->CI->input->server('HTTP_X_API_TOKEN');
        if (is_string($x) && $x !== '') {
            return trim($x);
        }
        $x = $this->CI->input->get_request_header('X-Api-Token', true);
        if (is_string($x) && $x !== '') {
            return trim($x);
        }
        return '';
    }
}
