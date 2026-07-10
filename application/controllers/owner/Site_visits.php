<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site_visits extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form', 'nb'));
        $this->load->database();
        $this->load->model(array('Site_visit_model', 'Nb_property_model'));
        $this->require_login();
        $this->require_role('owner');
        $this->require_approved();
    }

    public function index()
    {
        $uid = (int) $this->session->userdata('nb_user_id');
        $status = trim((string) $this->input->get('status'));
        $allowed = array('pending', 'confirmed', 'cancelled', 'completed');
        $rows = $this->Site_visit_model->for_property_owner($uid, null, 200, 0);
        if ($status !== '' && in_array($status, $allowed, true)) {
            $rows = array_values(array_filter($rows, function ($r) use ($status) {
                return isset($r->status) && $r->status === $status;
            }));
        }

        $data['page_title'] = 'Site visit schedule';
        $data['owner_nav'] = 'site_visits';
        $data['visits'] = $rows;
        $data['status_filter'] = $status;
        $data['pending_visits'] = $this->Site_visit_model->count_for_owner($uid, 'pending');
        $data['stats'] = array(
            'total' => $this->Site_visit_model->count_for_owner($uid),
            'pending' => $data['pending_visits'],
            'confirmed' => $this->Site_visit_model->count_for_owner($uid, 'confirmed'),
            'cancelled' => $this->Site_visit_model->count_for_owner($uid, 'cancelled'),
            'completed' => $this->Site_visit_model->count_for_owner($uid, 'completed'),
        );
        $this->load->view('nobroker/owner/panel_header', $data);
        $this->load->view('nobroker/owner/site_visits', $data);
        $this->load->view('nobroker/owner/panel_footer', $data);
    }

    /** POST — owner approves / rejects / completes a visit on their property. */
    public function update()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $uid = (int) $this->session->userdata('nb_user_id');
        $id = (int) $this->input->post('site_visit_id');
        $status = trim((string) $this->input->post('status'));
        $notes = trim((string) $this->input->post('notes', true));
        $allowed = array('pending', 'confirmed', 'cancelled', 'completed');
        $is_ajax = $this->input->is_ajax_request()
            || stripos((string) $this->input->server('HTTP_ACCEPT'), 'application/json') !== false;

        if ($id < 1 || !in_array($status, $allowed, true)) {
            if ($is_ajax) {
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode(array('success' => false, 'message' => 'Invalid input')));
                return;
            }
            $this->session->set_flashdata('nb_err', 'Invalid request.');
            redirect('owner/site-visits');
            return;
        }
        if (!$this->Site_visit_model->owner_can_manage($id, $uid)) {
            if ($is_ajax) {
                $this->output->set_content_type('application/json');
                $this->output->set_status_header(403);
                $this->output->set_output(json_encode(array('success' => false, 'message' => 'Not allowed for this visit.')));
                return;
            }
            show_error('Forbidden', 403);
            return;
        }
        $upd = array('status' => $status);
        if ($this->db->field_exists('notes', 'nb_site_visits') && $notes !== '') {
            $upd['notes'] = $notes;
        }
        $this->Site_visit_model->update($id, $upd);

        $messages = array(
            'confirmed' => 'Site visit approved.',
            'cancelled' => 'Site visit rejected.',
            'completed' => 'Site visit marked completed.',
            'pending' => 'Site visit set back to pending.',
        );
        $msg = isset($messages[$status]) ? $messages[$status] : 'Site visit updated.';

        if ($is_ajax) {
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(array('success' => true, 'message' => $msg, 'status' => $status)));
            return;
        }
        $this->session->set_flashdata('nb_ok', $msg);
        redirect('owner/site-visits');
    }
}
