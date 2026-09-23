<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Firebase {

    private $_credentials   = null;
    private $_access_token  = null;
    private $_token_expires = 0;

    public function __construct()
    {
        $this->_load_credentials();
    }

    private function _load_credentials()
    {
        $CI =& get_instance();
        if (isset($CI->db) && $CI->db) {
            $CI->load->model('Nb_firebase_model');
            $decoded = $CI->Nb_firebase_model->get_service_account();
            if (is_array($decoded) && !empty($decoded['private_key'])) {
                $this->_credentials = $decoded;
                return;
            }
        }
        $cred_file = APPPATH . 'config/firebase_service_account.json';
        if (file_exists($cred_file)) {
            $this->_credentials = json_decode(file_get_contents($cred_file), true);
        }
    }

    public function project_id()
    {
        return !empty($this->_credentials['project_id']) ? (string) $this->_credentials['project_id'] : '';
    }

    public function is_ready()
    {
        return is_array($this->_credentials) && !empty($this->_credentials['private_key']) && $this->project_id() !== '';
    }

    private function _base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function _get_access_token()
    {
        if ($this->_access_token && time() < $this->_token_expires - 60) {
            return $this->_access_token;
        }

        if (empty($this->_credentials) || empty($this->_credentials['private_key'])) {
            return null;
        }

        $now     = time();
        $header  = $this->_base64url_encode(json_encode(array('alg' => 'RS256', 'typ' => 'JWT')));
        $payload = $this->_base64url_encode(json_encode(array(
            'iss'   => $this->_credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging https://www.googleapis.com/auth/cloud-platform',
            'aud'   => $this->_credentials['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600,
        )));

        $base        = $header . '.' . $payload;
        $private_key = openssl_pkey_get_private($this->_credentials['private_key']);
        if (!$private_key) {
            log_message('error', 'Firebase: invalid service account private key');
            return null;
        }
        openssl_sign($base, $signature, $private_key, OPENSSL_ALGO_SHA256);
        $jwt = $base . '.' . $this->_base64url_encode($signature);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_credentials['token_uri']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        )));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $token_data = json_decode($response, true);

        if (!empty($token_data['access_token'])) {
            $this->_access_token  = $token_data['access_token'];
            $this->_token_expires = $now + (isset($token_data['expires_in']) ? (int) $token_data['expires_in'] : 3600);
            return $this->_access_token;
        }

        log_message('error', 'Firebase: failed to get access token – ' . $response);
        return null;
    }

    public function send_notification($title, $body, $image = null, $data = array(), $topic = 'all_users', $video_url = null)
    {
        $topic = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $topic);
        if ($topic === '') {
            $topic = 'all_users';
        }
        $message = $this->_message_body($title, $body, $image, $data, $video_url);
        $message['topic'] = $topic;
        return $this->_post_message($message);
    }

    public function send_to_token($device_token, $title, $body, $image = null, $data = array(), $video_url = null)
    {
        $device_token = trim((string) $device_token);
        if ($device_token === '') {
            return json_encode(array('error' => 'Empty device token'));
        }
        $message = $this->_message_body($title, $body, $image, $data, $video_url);
        $message['token'] = $device_token;
        return $this->_post_message($message);
    }

    /**
     * @param string[] $tokens
     * @return array{sent:int,failed:int,errors:string[]}
     */
    public function send_to_tokens($tokens, $title, $body, $image = null, $data = array(), $video_url = null)
    {
        $sent = 0;
        $failed = 0;
        $errors = array();
        $seen = array();
        foreach ((array) $tokens as $token) {
            $token = trim((string) $token);
            if ($token === '' || isset($seen[$token])) {
                continue;
            }
            $seen[$token] = true;
            $raw = $this->send_to_token($token, $title, $body, $image, $data, $video_url);
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded['name'])) {
                $sent++;
            } else {
                $failed++;
                if (count($errors) < 8) {
                    $msg = 'send failed';
                    if (is_array($decoded) && isset($decoded['error']['message'])) {
                        $msg = (string) $decoded['error']['message'];
                    } elseif (is_array($decoded) && isset($decoded['error']) && is_string($decoded['error'])) {
                        $msg = $decoded['error'];
                    }
                    $errors[] = $msg;
                }
            }
        }
        return array('sent' => $sent, 'failed' => $failed, 'errors' => $errors);
    }

    /**
     * @param string[] $tokens
     */
    public function subscribe_tokens_to_topic($tokens, $topic)
    {
        $topic = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $topic);
        $clean = array();
        foreach ((array) $tokens as $t) {
            $t = trim((string) $t);
            if ($t !== '') {
                $clean[] = $t;
            }
        }
        $clean = array_values(array_unique($clean));
        if ($topic === '' || empty($clean)) {
            return false;
        }
        $access_token = $this->_get_access_token();
        if (empty($access_token)) {
            return false;
        }
        $ok = true;
        foreach (array_chunk($clean, 500) as $chunk) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://iid.googleapis.com/iid/v1:batchAdd');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $access_token,
                'access_token_auth: true',
                'Content-Type: application/json',
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                'to' => '/topics/' . $topic,
                'registration_tokens' => $chunk,
            )));
            $result = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code >= 400) {
                log_message('error', 'Firebase topic subscribe failed (' . $topic . '): ' . $result);
                $ok = false;
            }
        }
        return $ok;
    }

    private function _message_body($title, $body, $image, $data, $video_url)
    {
        $notification = array('title' => (string) $title, 'body' => (string) $body);
        if (!empty($image)) {
            $notification['image'] = (string) $image;
        }
        $merged = array_merge(array('click_action' => 'FLUTTER_NOTIFICATION_CLICK'), is_array($data) ? $data : array());
        if (!empty($image)) {
            $merged['image_url'] = (string) $image;
        }
        if (!empty($video_url)) {
            $merged['video_url'] = (string) $video_url;
        }
        $string_data = array();
        foreach ($merged as $k => $v) {
            $string_data[(string) $k] = (string) $v;
        }
        $link = function_exists('base_url') ? rtrim(base_url(), '/') . '/' : '/';
        $icon = function_exists('base_url') ? base_url('assets/img/nb-placeholder-property.svg') : '';
        $web_notification = $notification;
        if ($icon !== '') {
            $web_notification['icon'] = $icon;
        }
        return array(
            'notification' => $notification,
            'data' => $string_data,
            'android' => array(
                'priority' => 'HIGH',
                'notification' => array('click_action' => 'FLUTTER_NOTIFICATION_CLICK'),
            ),
            'apns' => array(
                'payload' => array('aps' => array(
                    'sound' => 'default',
                    'badge' => 1,
                    'category' => 'FLUTTER_NOTIFICATION_CLICK',
                )),
            ),
            'webpush' => array(
                'headers' => array('TTL' => '86400', 'Urgency' => 'high'),
                'notification' => $web_notification,
                'fcm_options' => array('link' => $link),
            ),
        );
    }

    private function _post_message(array $message)
    {
        $access_token = $this->_get_access_token();
        if (empty($access_token) || empty($this->_credentials['project_id'])) {
            return json_encode(array(
                'error' => 'Firebase: could not obtain access token. Paste the coimbatore-property service account JSON in Settings → Firebase.',
            ));
        }
        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->_credentials['project_id'] . '/messages:send';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('message' => $message)));
        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($result === false || $errno) {
            return json_encode(array('error' => 'Firebase HTTP error'));
        }
        return $result;
    }
}
