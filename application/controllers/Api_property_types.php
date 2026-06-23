<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Property types API (main + sub types from nb_property_types).
 *
 * GET  /api/property-types           — active types grouped for frontend
 * GET  /api/property-types/flat      — active flat list (dropdowns)
 * POST /api/property-types/toggle    — admin: toggle is_active (panel session)
 */
class Api_property_types extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'nb'));
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Nb_property_type_model');
        $this->output->set_content_type('application/json');
        $this->_cors();
    }

    private function _cors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Token, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
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

    private function _require_admin()
    {
        $u = $this->session->userdata('nb_user');
        if (!$u || !isset($u['role']) || $u['role'] !== 'admin' || (isset($u['status']) && $u['status'] !== 'approved')) {
            $this->_json(array('success' => false, 'message' => 'Forbidden'), 403);
            return false;
        }
        return true;
    }

    /** GET — grouped main types with sub_types[]. */
    public function index()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        if (!$this->Nb_property_type_model->table_exists()) {
            return $this->_json(array(
                'success' => true,
                'total' => 0,
                'items' => array(),
                'flat' => array(),
            ));
        }
        $grouped = $this->Nb_property_type_model->active_grouped();
        $flat = $this->Nb_property_type_model->active_flat();
        return $this->_json(array(
            'success' => true,
            'total' => count($flat),
            'items' => $grouped,
            'flat' => $flat,
        ));
    }

    /** GET — flat active list only. */
    public function flat()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        $flat = $this->Nb_property_type_model->active_flat();
        return $this->_json(array(
            'success' => true,
            'total' => count($flat),
            'items' => $flat,
        ));
    }

    /** POST — toggle is_active (admin panel AJAX). Body: id */
    public function toggle()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('success' => false, 'message' => 'POST only'), 405);
        }
        if (!$this->_require_admin()) {
            return;
        }
        $id = (int) $this->input->post('id');
        if ($id < 1) {
            $id = (int) $this->input->get('id');
        }
        if ($id < 1) {
            return $this->_json(array('success' => false, 'message' => 'Invalid id'), 400);
        }
        $result = $this->Nb_property_type_model->toggle_active($id);
        if ($result === null) {
            return $this->_json(array('success' => false, 'message' => 'Not found'), 404);
        }
        return $this->_json(array(
            'success' => true,
            'id' => $id,
            'is_active' => (int) $result['is_active'],
            'message' => $result['is_active'] ? 'Activated' : 'Deactivated',
        ));
    }
}
