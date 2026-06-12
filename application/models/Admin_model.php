<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function login($username, $password)
    {
        $username = trim((string) $username);
        $password = (string) $password;

        // Legacy table
        if ($this->db->table_exists('admin')) {
            $this->db->where('username', $username);
            $admin = $this->db->get('admin')->row();
            if ($admin && isset($admin->password) && $this->password_matches($password, $admin->password)) {
                return $admin;
            }
        }

        // Current shared users table
        if ($this->db->table_exists('users')) {
            $this->db->where('username', $username);
            $this->db->where('user_type', 'admin');
            $admin = $this->db->get('users')->row();
            if ($admin) {
                $stored_hash = isset($admin->password_hash) ? (string) $admin->password_hash : '';
                $stored_plain = isset($admin->password) ? (string) $admin->password : '';
                if (($stored_hash !== '' && $this->password_matches($password, $stored_hash)) || ($stored_plain !== '' && $this->password_matches($password, $stored_plain))) {
                    if (!isset($admin->username) || $admin->username === null || $admin->username === '') {
                        $admin->username = (isset($admin->email) && $admin->email) ? $admin->email : 'admin';
                    }
                    return $admin;
                }
            }
        }

        // Fallback to nb_users admin account (email or phone in username field)
        if ($this->db->table_exists('nb_users')) {
            $this->db->group_start();
            $this->db->where('email', $username);
            $this->db->or_where('phone', $username);
            $this->db->group_end();
            $this->db->where('role', 'admin');
            $admin = $this->db->get('nb_users')->row();
            if ($admin && isset($admin->password) && $this->password_matches($password, $admin->password)) {
                if (!isset($admin->username) || $admin->username === null || $admin->username === '') {
                    $admin->username = (isset($admin->email) && $admin->email) ? $admin->email : 'admin';
                }
                return $admin;
            }
        }
        return false;
    }

    public function get_by_id($id)
    {
        if ($this->db->table_exists('admin')) {
            return $this->db->get_where('admin', array('id' => $id))->row();
        }
        if ($this->db->table_exists('users')) {
            return $this->db->get_where('users', array('id' => $id, 'user_type' => 'admin'))->row();
        }
        if ($this->db->table_exists('nb_users')) {
            return $this->db->get_where('nb_users', array('id' => $id, 'role' => 'admin'))->row();
        }
        return null;
    }

    public function update_password($id, $password)
    {
        if ($this->db->table_exists('admin')) {
            $this->db->where('id', $id);
            return $this->db->update('admin', array('password' => $password));
        }
        if ($this->db->table_exists('users')) {
            $this->db->where('id', $id);
            return $this->db->update('users', array('password_hash' => password_hash($password, PASSWORD_BCRYPT)));
        }
        if ($this->db->table_exists('nb_users')) {
            $this->db->where('id', $id);
            $this->db->where('role', 'admin');
            return $this->db->update('nb_users', array('password' => password_hash($password, PASSWORD_BCRYPT)));
        }
        return false;
    }

    private function password_matches($plain, $stored)
    {
        $stored = (string) $stored;
        if ($stored === '') {
            return false;
        }
        if (password_verify($plain, $stored)) {
            return true;
        }
        return hash_equals($stored, (string) $plain);
    }
}

