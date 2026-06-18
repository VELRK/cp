<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback_user extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->database();
        $this->load->library(array('form_validation', 'upload'));
        $this->load->model('Feedback_model');
        $this->require_login();
        $this->require_approved();
    }

    public function index()
    {
        if ($this->input->method() === 'post' && $this->_save()) {
            $this->session->set_flashdata('nb_ok', 'Thank you — your feedback was submitted.');
            redirect('user/feedback');
            return;
        }
        $uid = (string) $this->session->userdata('nb_user_id');
        $data['page_title'] = 'Feedback';
        $data['rows'] = $this->Feedback_model->get_all($uid, 50, 0);
        $this->load->view('nobroker/layout/header', $data);
        $this->load->view('nobroker/user/feedback', $data);
        $this->load->view('nobroker/layout/footer', $data);
    }

    private function _save()
    {
        $this->form_validation->set_rules('title', 'Subject', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', 'Details', 'trim');
        if (!$this->form_validation->run()) {
            return false;
        }
        $uid = (string) $this->session->userdata('nb_user_id');
        $desc = $this->input->post('description');
        $row = array(
            'userId' => $uid,
            'title' => $this->input->post('title'),
            'description' => ($desc !== null && trim((string) $desc) !== '') ? trim((string) $desc) : null,
        );
        if ($this->db->field_exists('image', 'feedbacks') && !empty($_FILES['image_file']) && !empty($_FILES['image_file']['name'])) {
            $upload_dir = FCPATH . 'assets/uploads/feedbacks/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }
            $cfg = array(
                'upload_path' => $upload_dir,
                'allowed_types' => 'jpg|jpeg|png|webp|gif',
                'max_size' => 5120,
                'encrypt_name' => true,
            );
            $this->load->library('upload', $cfg);
            if (!$this->upload->do_upload('image_file')) {
                $this->session->set_flashdata('nb_err', strip_tags($this->upload->display_errors('', '')));
                return false;
            }
            $u = $this->upload->data();
            $row['image'] = 'assets/uploads/feedbacks/' . $u['file_name'];
        }
        $id = $this->Feedback_model->create($row);
        return (bool) $id;
    }
}
