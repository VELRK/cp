<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Live_updates_user extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->library('form_validation');
        $this->load->model('Live_update_model');
        $this->require_login();
        $this->require_approved();
    }

    public function index()
    {
        $uid = (string) $this->session->userdata('nb_user_id');
        $data['page_title'] = 'My live updates';
        $data['rows'] = $this->Live_update_model->get_all_by_user($uid);
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/user/live_updates', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    public function add()
    {
        if ($this->input->method() === 'post' && $this->_save()) {
            $this->session->set_flashdata('nb_ok', 'Live update created.');
            redirect('user/live-updates');
            return;
        }
        $data['page_title'] = 'Add live update';
        $data['row'] = null;
        $data['edit_id'] = 0;
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/user/live_update_form', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    public function edit($id = 0)
    {
        $id = (int) $id;
        $row = $this->Live_update_model->get_by_id($id);
        if (!$row || !$this->_can_access_row($row)) {
            show_404();
        }
        if ($this->input->method() === 'post' && $this->_save($id, $row)) {
            $this->session->set_flashdata('nb_ok', 'Live update updated.');
            redirect('user/live-updates');
            return;
        }
        $data['page_title'] = 'Edit live update';
        $data['row'] = $row;
        $data['edit_id'] = $id;
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/user/live_update_form', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    public function delete($id = 0)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id = (int) $id;
        $row = $this->Live_update_model->get_by_id($id);
        if (!$row || !$this->_can_access_row($row)) {
            show_404();
        }
        $this->Live_update_model->delete($id);
        $this->session->set_flashdata('nb_ok', 'Live update deleted.');
        redirect('user/live-updates');
    }

    private function _save($id = 0, $existing = null)
    {
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

        if ($id > 0) {
            $this->Live_update_model->update($id, $data);
            return true;
        }

        if ($this->Live_update_model->has_user_column()) {
            $data['userId'] = (string) $this->session->userdata('nb_user_id');
        }
        $this->Live_update_model->create($data);
        return true;
    }

    private function _can_access_row($row)
    {
        if (!$this->Live_update_model->has_user_column()) {
            return true;
        }
        $uid = (string) $this->session->userdata('nb_user_id');
        return isset($row->userId) && (string) $row->userId === $uid;
    }
}

