<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_delete_request_model extends CI_Model {

    protected $table = 'nb_delete_requests';

    public function create($user_id, $reason)
    {
        $this->db->insert($this->table, array(
            'user_id' => (int) $user_id,
            'reason'  => $reason,
        ));
        return $this->db->insert_id();
    }

    public function get_all()
    {
        return $this->db
            ->select('r.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone')
            ->from($this->table . ' r')
            ->join('nb_users u', 'u.id = r.user_id', 'left')
            ->order_by('r.created_at', 'DESC')
            ->get()
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db
            ->select('r.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone')
            ->from($this->table . ' r')
            ->join('nb_users u', 'u.id = r.user_id', 'left')
            ->where('r.id', (int) $id)
            ->get()
            ->row();
    }

    public function update_status($id, $status)
    {
        $this->db->where('id', (int) $id)->update($this->table, array('status' => $status));
    }

    public function already_requested($user_id)
    {
        return $this->db->where(array('user_id' => (int) $user_id, 'status' => 'pending'))
                        ->count_all_results($this->table) > 0;
    }
}
