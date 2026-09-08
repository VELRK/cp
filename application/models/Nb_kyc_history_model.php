<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_kyc_history_model extends CI_Model
{
    protected $table = 'nb_kyc_history';

    /**
     * @param int $user_id
     * @param string $action
     * @param string $from_status
     * @param string $to_status
     * @param string $comment
     * @param object|array|null $actor
     * @param array|null $meta
     * @return int
     */
    public function insert_event($user_id, $action, $from_status, $to_status, $comment = '', $actor = null, $meta = null)
    {
        $actor_id = null;
        $actor_name = '';
        $actor_role = '';
        if ($actor === null) {
            if (!isset($this->session)) {
                $this->load->library('session');
            }
            $sess = $this->session->userdata('nb_user');
            if (is_array($sess)) {
                $actor = $sess;
            }
        }
        if ($actor) {
            $row = is_array($actor) ? $actor : (array) $actor;
            $actor_id = isset($row['id']) ? (int) $row['id'] : null;
            if ($actor_id < 1) {
                $actor_id = null;
            }
            $actor_name = isset($row['name']) ? trim((string) $row['name']) : '';
            $actor_role = isset($row['role']) ? strtolower(trim((string) $row['role'])) : '';
            $user_type = isset($row['user_type']) ? strtolower(trim((string) $row['user_type'])) : '';
            if ($user_type === 'agent') {
                $actor_role = 'agent';
            }
        }

        $this->db->insert($this->table, array(
            'user_id' => (int) $user_id,
            'actor_id' => $actor_id,
            'actor_name' => $actor_name !== '' ? $actor_name : null,
            'actor_role' => $actor_role !== '' ? $actor_role : null,
            'action' => substr((string) $action, 0, 40),
            'from_status' => $from_status !== '' && $from_status !== null ? substr((string) $from_status, 0, 20) : null,
            'to_status' => substr((string) $to_status, 0, 20),
            'comment' => trim((string) $comment) !== '' ? trim((string) $comment) : null,
            'meta' => is_array($meta) ? json_encode($meta) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return (int) $this->db->insert_id();
    }

    /**
     * Newest first.
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function for_user($user_id, $limit = 200)
    {
        if (!$this->db->table_exists($this->table)) {
            return array();
        }
        $limit = (int) $limit;
        if ($limit < 1) {
            $limit = 200;
        }
        $this->db->where('user_id', (int) $user_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        $rows = $this->db->get($this->table)->result_array();
        $out = array();
        $labels = array(
            'submitted' => 'KYC submitted',
            'resubmitted' => 'KYC resubmitted',
            'approved' => 'KYC approved',
            'rejected' => 'KYC rejected',
            'comment_updated' => 'Rejection comment updated',
        );
        foreach ($rows as $row) {
            $action = isset($row['action']) ? (string) $row['action'] : '';
            $row['action_label'] = isset($labels[$action]) ? $labels[$action] : ucfirst(str_replace('_', ' ', $action));
            if (!empty($row['meta']) && is_string($row['meta'])) {
                $decoded = json_decode($row['meta'], true);
                $row['meta'] = is_array($decoded) ? $decoded : null;
            } else {
                $row['meta'] = null;
            }
            $out[] = nb_api_add_datetime_display($row, array('created_at'));
        }
        return $out;
    }
}
