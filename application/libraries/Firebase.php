<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Firebase {

    private $_credentials   = null;
    private $_access_token  = null;
    private $_token_expires = 0;

    public function __construct()
    {
        $cred_file = APPPATH . 'config/firebase_service_account.json';
        if (file_exists($cred_file)) {
            $this->_credentials = json_decode(file_get_contents($cred_file), true);
        }
    }

    // -------------------------------------------------------------------------
    // OAuth2 helpers
    // -------------------------------------------------------------------------

    private function _base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Obtain (or return cached) OAuth2 access token using the service account.
     */
    private function _get_access_token()
    {
        if ($this->_access_token && time() < $this->_token_expires - 60) {
            return $this->_access_token;
        }

        if (empty($this->_credentials)) {
            return null;
        }

        $now     = time();
        $header  = $this->_base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->_base64url_encode(json_encode([
            'iss'   => $this->_credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $this->_credentials['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $base        = $header . '.' . $payload;
        $private_key = openssl_pkey_get_private($this->_credentials['private_key']);
        openssl_sign($base, $signature, $private_key, OPENSSL_ALGO_SHA256);
        $jwt = $base . '.' . $this->_base64url_encode($signature);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_credentials['token_uri']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $token_data = json_decode($response, true);

        if (!empty($token_data['access_token'])) {
            $this->_access_token  = $token_data['access_token'];
            $this->_token_expires = $now + (isset($token_data['expires_in']) ? (int)$token_data['expires_in'] : 3600);
            return $this->_access_token;
        }

        log_message('error', 'Firebase: failed to get access token – ' . $response);
        return null;
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Send FCM push notification to a topic (FCM v1 API).
     *
     * @param string $title  Notification title
     * @param string $body   Notification body
     * @param string $image  Optional image URL
     * @param array  $data   Optional extra data payload (values must be strings)
     * @param string $topic  FCM topic (default: all_users)
     * @param string $video_url Optional full URL to video (passed in `data` for the app; not shown as rich media by FCM)
     * @return string Raw FCM response JSON
     */
    public function send_notification($title, $body, $image = null, $data = array(), $topic = 'all_users', $video_url = null)
    {
        $access_token = $this->_get_access_token();

        if (empty($access_token)) {
            return json_encode(['error' => 'Firebase: could not obtain access token']);
        }

        $notification = ['title' => $title, 'body' => $body];
        if (!empty($image)) {
            $notification['image'] = $image;
        }

        // FCM v1 requires all data values to be strings
        $merged = array_merge(['click_action' => 'FLUTTER_NOTIFICATION_CLICK'], $data);
        if (!empty($image)) {
            $merged['image_url'] = (string) $image;
        }
        if (!empty($video_url)) {
            $merged['video_url'] = (string) $video_url;
        }
        $string_data = array_map('strval', $merged);

        $payload = [
            'message' => [
                'topic'        => $topic,
                'notification' => $notification,
                'data'         => $string_data,
                'android'      => [
                    'notification' => ['click_action' => 'FLUTTER_NOTIFICATION_CLICK'],
                ],
                'apns' => [
                    'payload' => ['aps' => ['category' => 'FLUTTER_NOTIFICATION_CLICK']],
                ],
            ],
        ];

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->_credentials['project_id'] . '/messages:send';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Send FCM push notification to a specific device token (FCM v1 API).
     *
     * @param string $device_token  FCM registration token
     * @param string $title
     * @param string $body
     * @param string $image         Optional image URL
     * @param array  $data          Optional extra data payload
     * @param string $video_url     Optional full URL to video (data payload)
     * @return string Raw FCM response JSON
     */
    public function send_to_token($device_token, $title, $body, $image = null, $data = array(), $video_url = null)
    {
        $access_token = $this->_get_access_token();

        if (empty($access_token)) {
            return json_encode(['error' => 'Firebase: could not obtain access token']);
        }

        $notification = ['title' => $title, 'body' => $body];
        if (!empty($image)) {
            $notification['image'] = $image;
        }

        $merged = array_merge(['click_action' => 'FLUTTER_NOTIFICATION_CLICK'], $data);
        if (!empty($image)) {
            $merged['image_url'] = (string) $image;
        }
        if (!empty($video_url)) {
            $merged['video_url'] = (string) $video_url;
        }
        $string_data = array_map('strval', $merged);

        $payload = [
            'message' => [
                'token'        => $device_token,
                'notification' => $notification,
                'data'         => $string_data,
                'android'      => [
                    'notification' => ['click_action' => 'FLUTTER_NOTIFICATION_CLICK'],
                ],
                'apns' => [
                    'payload' => ['aps' => ['category' => 'FLUTTER_NOTIFICATION_CLICK']],
                ],
            ],
        ];

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->_credentials['project_id'] . '/messages:send';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
