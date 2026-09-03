<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Amenities master list API (nb_amenities).
 *
 * GET  /api/amenities        — active amenities for property forms & filters
 * POST /api/amenities/toggle — admin: toggle is_active (panel session)
 */
class Api_amenities extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'nb'));
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Nb_amenity_model');
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

    /** @return array<int, array<string, mixed>> */
    private function _format_rows($rows)
    {
        $items = array();
        foreach ($rows as $r) {
            $items[] = array(
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'slug' => (string) $r->slug,
                'sort_order' => (int) $r->sort_order,
                'is_active' => (int) $r->is_active,
            );
        }
        return $items;
    }

    /** GET — active amenities sorted for dropdowns / checkboxes. */
    public function index()
    {
        if ($this->input->method() !== 'get') {
            return $this->_json(array('success' => false, 'message' => 'GET only'), 405);
        }
        if (!$this->Nb_amenity_model->table_exists()) {
            return $this->_json(array(
                'success' => true,
                'total' => 0,
                'items' => array(),
                'amenities' => array(),
            ));
        }
        $rows = $this->Nb_amenity_model->all_active();
        $items = $this->_format_rows($rows);
        return $this->_json(array(
            'success' => true,
            'total' => count($items),
            'items' => $items,
            'amenities' => array_map(function ($row) {
                return $row['name'];
            }, $items),
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
        if (!$this->Nb_amenity_model->table_exists()) {
            return $this->_json(array('success' => false, 'message' => 'Amenities table not found'), 503);
        }
        $id = (int) $this->input->post('id');
        if ($id < 1) {
            $id = (int) $this->input->get('id');
        }
        if ($id < 1) {
            return $this->_json(array('success' => false, 'message' => 'Invalid id'), 400);
        }
        $result = $this->Nb_amenity_model->toggle_active($id);
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
