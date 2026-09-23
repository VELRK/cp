<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Nb_firebase_model extends CI_Model
{
    public function ensure_tables()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        if (!$this->db->table_exists('nb_firebase_settings')) {
            $this->db->query(
                "CREATE TABLE `nb_firebase_settings` (
                    `setting_key` VARCHAR(64) NOT NULL,
                    `setting_value` MEDIUMTEXT NULL DEFAULT NULL,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`setting_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            $this->seed_web_defaults();
        }
    }

    public function setting_keys()
    {
        return array(
            'api_key',
            'auth_domain',
            'project_id',
            'storage_bucket',
            'messaging_sender_id',
            'app_id',
            'measurement_id',
            'vapid_key',
            'service_account_json',
        );
    }

    public function web_defaults()
    {
        return array(
            'api_key' => 'AIzaSyDNaRktCmQ1XmFCTAjLIxO1rbWs0fYKyUg',
            'auth_domain' => 'coimbatore-property.firebaseapp.com',
            'project_id' => 'coimbatore-property',
            'storage_bucket' => 'coimbatore-property.appspot.com',
            'messaging_sender_id' => '649859195062',
            'app_id' => '1:649859195062:web:5f8dc1e875580d978c4617',
            'measurement_id' => 'G-D4BFP43KHN',
            'vapid_key' => '',
            'service_account_json' => '',
        );
    }

    private function seed_web_defaults()
    {
        $now = date('Y-m-d H:i:s');
        foreach ($this->web_defaults() as $key => $value) {
            if ($key === 'vapid_key' || $key === 'service_account_json') {
                continue;
            }
            $this->db->insert('nb_firebase_settings', array(
                'setting_key' => $key,
                'setting_value' => $value,
                'updated_at' => $now,
            ));
        }
    }

    public function get_settings()
    {
        $this->ensure_tables();
        $out = $this->web_defaults();
        $file_json = $this->service_account_from_file();
        if ($file_json !== '') {
            $out['service_account_json'] = $file_json;
        }
        $rows = $this->db->get('nb_firebase_settings')->result();
        foreach ($rows as $row) {
            $key = (string) $row->setting_key;
            if (!array_key_exists($key, $out)) {
                continue;
            }
            if (($key === 'vapid_key' || $key === 'service_account_json')
                && ($row->setting_value === null || $row->setting_value === '')
            ) {
                continue;
            }
            $out[$key] = (string) $row->setting_value;
        }
        return $out;
    }

    public function has_vapid_key()
    {
        $s = $this->get_settings();
        return trim((string) $s['vapid_key']) !== '';
    }

    public function has_service_account()
    {
        $json = $this->get_service_account_json();
        $decoded = json_decode($json, true);
        return is_array($decoded) && !empty($decoded['private_key']);
    }

    public function get_public_config()
    {
        $s = $this->get_settings();
        $config = array(
            'apiKey' => $s['api_key'],
            'authDomain' => $s['auth_domain'],
            'projectId' => $s['project_id'],
            'storageBucket' => $s['storage_bucket'],
            'messagingSenderId' => $s['messaging_sender_id'],
            'appId' => $s['app_id'],
        );
        if ($s['measurement_id'] !== '') {
            $config['measurementId'] = $s['measurement_id'];
        }
        $enabled = $config['apiKey'] !== '' && $config['appId'] !== '' && $config['projectId'] !== '';
        return array(
            'enabled' => $enabled,
            'config' => $config,
            'vapidKey' => $s['vapid_key'],
        );
    }

    public function get_service_account_json()
    {
        $s = $this->get_settings();
        return trim((string) $s['service_account_json']);
    }

    public function get_service_account()
    {
        $decoded = json_decode($this->get_service_account_json(), true);
        return is_array($decoded) ? $decoded : array();
    }

    public function parse_service_account($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return array('ok' => true, 'json' => '');
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['private_key']) || empty($decoded['project_id']) || empty($decoded['client_email'])) {
            return array(
                'ok' => false,
                'error' => 'Service account JSON must include project_id, client_email, and private_key.',
            );
        }
        return array('ok' => true, 'json' => json_encode($decoded));
    }

    public function save_settings(array $data)
    {
        $this->ensure_tables();
        $keys = $this->setting_keys();
        foreach ($keys as $key) {
            if (($key === 'vapid_key' || $key === 'service_account_json')
                && (!isset($data[$key]) || trim((string) $data[$key]) === '')
            ) {
                continue;
            }
            $value = isset($data[$key]) ? trim((string) $data[$key]) : '';
            if ($key === 'service_account_json' && $value !== '') {
                $parsed = $this->parse_service_account($value);
                if (empty($parsed['ok'])) {
                    return $parsed;
                }
                $value = $parsed['json'];
            }
            $exists = $this->db->get_where('nb_firebase_settings', array('setting_key' => $key))->row();
            $row = array(
                'setting_key' => $key,
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            if ($exists) {
                $this->db->where('setting_key', $key)->update('nb_firebase_settings', $row);
            } else {
                $this->db->insert('nb_firebase_settings', $row);
            }
        }
        return array('ok' => true);
    }

    private function service_account_from_file()
    {
        $cred_file = APPPATH . 'config/firebase_service_account.json';
        if (!is_file($cred_file)) {
            return '';
        }
        $raw = @file_get_contents($cred_file);
        if (!is_string($raw) || trim($raw) === '') {
            return '';
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['private_key'])) {
            return '';
        }
        return trim($raw);
    }
}
