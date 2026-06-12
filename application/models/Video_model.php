<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Video_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($status = null)
    {
        $this->db->select('videos.*');
        $this->db->from('videos');
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        // Order by index_no DESC, then by createdAt DESC
        $this->db->order_by('index_no', 'DESC');
        $this->db->order_by('createdAt', 'DESC');
        
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('videos', array('id' => $id))->row();
    }

    public function create($data)
    {
        // Set timestamp
        if (!isset($data['createdAt'])) {
            $data['createdAt'] = date('Y-m-d H:i:s');
        }
        
        // Set default index_no if not provided
        if (!isset($data['index_no']) || $data['index_no'] === null) {
            $maxIndex = $this->db->select_max('index_no')->get('videos')->row()->index_no;
            $data['index_no'] = ($maxIndex !== null) ? $maxIndex + 1 : 1;
        }
        
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }
        
        $this->db->insert('videos', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('videos', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('videos');
    }

    public function update_order($id, $index)
    {
        $this->db->where('id', $id);
        return $this->db->update('videos', array('index_no' => (int)$index));
    }
    
    public function update_orders($orders)
    {
        // Update multiple videos' index_no values
        // $orders should be an array of ['id' => index_value]
        if (empty($orders) || !is_array($orders)) {
            log_message('error', 'Video update_orders: Invalid orders data');
            return false;
        }
        
        $this->db->trans_start();
        $success = true;
        
        foreach ($orders as $id => $index) {
            // Handle both string and numeric IDs
            $id = (int)$id;
            $indexValue = (int)$index;
            
            if ($id <= 0) {
                log_message('error', 'Video update_orders: Invalid ID: ' . var_export($id, true));
                $success = false;
                continue;
            }
            
            if ($indexValue < 0) {
                log_message('error', 'Video update_orders: Invalid index value: ' . $indexValue);
                $success = false;
                continue;
            }
            
            $this->db->where('id', $id);
            $update_result = $this->db->update('videos', array('index_no' => $indexValue));
            
            if (!$update_result) {
                $db_error = $this->db->error();
                log_message('error', 'Video update_orders: Failed to update video ID ' . $id . ': ' . json_encode($db_error));
                $success = false;
            } else {
                log_message('debug', 'Video update_orders: Successfully updated video ID ' . $id . ' to index ' . $indexValue);
            }
        }
        
        $this->db->trans_complete();
        
        if (!$this->db->trans_status()) {
            log_message('error', 'Video update_orders: Transaction failed');
            return false;
        }
        
        return $success;
    }
}
