<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create($data)
    {
        $this->db->insert('feedbacks', $data);
        return (int) $this->db->insert_id();
    }

    public function get_by_id($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            return null;
        }
        return $this->db->get_where('feedbacks', array('id' => $id))->row();
    }

    public function get_all($userId = null, $limit = null, $offset = 0)
    {
        if (!empty($userId)) {
            $this->db->where('userId', $userId);
        }
        $this->db->order_by('createdAt', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('feedbacks')->result();
    }

    public function count_all($userId = null)
    {
        if (!empty($userId)) {
            $this->db->where('userId', $userId);
        }
        return (int) $this->db->count_all_results('feedbacks');
    }
}

