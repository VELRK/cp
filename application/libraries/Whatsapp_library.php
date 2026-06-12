<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WhatsApp Library
 * Supports multiple WhatsApp API providers
 */
class Whatsapp_library {

    private $provider;
    private $api_key;
    private $api_url;
    private $from_number;
    private $ci;

    public function __construct($config = array())
    {
        $this->ci =& get_instance();

        // Load config from config file
        $this->ci->config->load('whatsapp', TRUE);
        $config = $this->ci->config->item('whatsapp') ?: $config;

        $this->provider    = 'syncr';
        $this->api_key     = 'd43fa5ae1f774ab727fb03376b2de1343efc9f42153f4905902bb4b705109b2cd27114922345bf8a0a99042892bd439f7a990a58dec4f2fc6b82cc8255e1af9e';
        $this->api_url     = 'https://waadmin.syncr.in/v1/message/send-message';
        $this->from_number = '919342012030';

        if (!empty($this->api_key)) {
            $masked_key = substr($this->api_key, 0, 8) . '...' . substr($this->api_key, -8);
            log_message('debug', 'Whatsapp_library initialized - Provider: ' . $this->provider . ', API Key: ' . $masked_key . ' (length: ' . strlen($this->api_key) . ')');
        } else {
            log_message('error', 'Whatsapp_library initialized - API Key is empty!');
        }
    }

    /**
     * Get current configuration status (for debugging)
     */
    public function get_config_status()
    {
        return array(
            'provider'      => $this->provider,
            'api_key_set'   => !empty($this->api_key) && $this->api_key !== 'YOUR_SYNCR_TOKEN_HERE',
            'api_key_length'=> strlen($this->api_key),
            'api_url'       => $this->api_url,
            'from_number'   => $this->from_number
        );
    }

    /**
     * Get cURL command for testing (development mode)
     */
    public function get_curl_command($phone, $otp)
    {
        if ($this->provider === 'syncr' || $this->provider === 'waadmin') {
            $phone_number = str_replace('+', '', $phone);
            $base_url     = !empty($this->api_url) ? $this->api_url : 'https://waadmin.syncr.in/v1/message/send-message';
            $url          = $base_url . '?token=' . urlencode($this->api_key);

            $data = array(
                'to'       => $phone_number,
                'type'     => 'template',
                'template' => array(
                    'language'   => array('policy' => 'deterministic', 'code' => 'en'),
                    'name'       => 'otp_verification',
                    'components' => array(
                        array(
                            'type'       => 'body',
                            'parameters' => array(array('type' => 'text', 'text' => $otp))
                        ),
                        array(
                            'type'       => 'button',
                            'sub_type'   => 'url',
                            'index'      => '0',
                            'parameters' => array(array('type' => 'text', 'text' => $otp))
                        )
                    )
                )
            );

            $json_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $curl_cmd  = "curl --location '" . $url . "' \\\n";
            $curl_cmd .= "  --header 'Content-Type: application/json' \\\n";
            $curl_cmd .= "  --data '" . str_replace("'", "'\\''", $json_data) . "'";

            return $curl_cmd;
        }

        return "cURL command not available for provider: " . $this->provider;
    }

    /**
     * Send OTP via WhatsApp
     */
    public function send_otp($phone, $otp)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        $message = "Your OTP for login is: *{$otp}*. This OTP is valid for 1 minute. Do not share this OTP with anyone.";

        switch ($this->provider) {
            case 'gupshup':
                return $this->send_via_gupshup($phone, $message);
            case 'twilio':
                return $this->send_via_twilio($phone, $message);
            case 'wati':
                return $this->send_via_wati($phone, $message);
            case '360dialog':
                return $this->send_via_360dialog($phone, $message);
            case 'messagebird':
                return $this->send_via_messagebird($phone, $message);
            case 'syncr':
            case 'waadmin':
                return $this->send_via_syncr($phone, $otp);
            default:
                return $this->send_via_gupshup($phone, $message);
        }
    }

    // -------------------------------------------------------------------------

    private function send_via_gupshup($phone, $message)
    {
        $url = !empty($this->api_url) ? $this->api_url : 'https://api.gupshup.io/sm/api/v1/msg';

        $data = array(
            'channel'     => 'whatsapp',
            'source'      => $this->from_number,
            'destination' => $phone,
            'message'     => $message,
            'src.name'    => 'DVM'
        );

        $url_with_key = $url . '?apikey=' . urlencode($this->api_key);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_with_key);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error= curl_error($ch);
        curl_close($ch);

        log_message('debug', 'Gupshup Response: ' . $response . ' | HTTP Code: ' . $http_code);

        if ($curl_error) {
            return array('success' => false, 'message' => 'CURL Error: ' . $curl_error);
        }

        if ($http_code == 200 || $http_code == 202) {
            $rd = json_decode($response, true);
            if (isset($rd['status']) && $rd['status'] == 'error') {
                return array('success' => false, 'message' => 'Gupshup Error: ' . ($rd['message'] ?? $response));
            }
            return array('success' => true, 'message' => 'OTP sent successfully');
        }

        $rd = json_decode($response, true);
        $error_msg = $rd['message'] ?? ($rd['error'] ?? $response);
        return array('success' => false, 'message' => 'Failed to send OTP: ' . $error_msg . ' (HTTP ' . $http_code . ')');
    }

    private function send_via_twilio($phone, $message)
    {
        $account_sid = $this->api_key;
        $auth_token  = $this->api_url;
        $from        = 'whatsapp:' . $this->from_number;
        $url         = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('From' => $from, 'To' => 'whatsapp:' . $phone, 'Body' => $message)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $account_sid . ':' . $auth_token);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code == 200 || $http_code == 201)
            ? array('success' => true,  'message' => 'OTP sent successfully')
            : array('success' => false, 'message' => 'Failed to send OTP: ' . $response);
    }

    private function send_via_wati($phone, $message)
    {
        $url = $this->api_url ?: 'https://api.wati.io/v1/sendSessionMessage/' . $phone;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('messageText' => $message)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->api_key, 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code == 200 || $http_code == 201)
            ? array('success' => true,  'message' => 'OTP sent successfully')
            : array('success' => false, 'message' => 'Failed to send OTP: ' . $response);
    }

    private function send_via_360dialog($phone, $message)
    {
        $url  = $this->api_url ?: 'https://waba-api.360dialog.io/v1/messages';
        $data = array('to' => $phone, 'type' => 'text', 'text' => array('body' => $message));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('D360-API-KEY: ' . $this->api_key, 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code == 200 || $http_code == 201)
            ? array('success' => true,  'message' => 'OTP sent successfully')
            : array('success' => false, 'message' => 'Failed to send OTP: ' . $response);
    }

    private function send_via_messagebird($phone, $message)
    {
        $data = array('recipients' => $phone, 'originator' => $this->from_number, 'body' => $message, 'type' => 'text');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://rest.messagebird.com/messages');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: AccessKey ' . $this->api_key, 'Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code == 200 || $http_code == 201)
            ? array('success' => true,  'message' => 'OTP sent successfully')
            : array('success' => false, 'message' => 'Failed to send OTP: ' . $response);
    }

    private function send_via_syncr($phone, $otp)
    {
        if (empty($this->api_key) || trim($this->api_key) === '' || $this->api_key === 'YOUR_SYNCR_TOKEN_HERE') {
            log_message('error', 'Syncr/WAAdmin API key is not configured.');
            return array('success' => false, 'message' => 'WhatsApp API key is not configured. Please set your Syncr token in application/config/whatsapp.php');
        }

        $token_preview = substr($this->api_key, 0, 8) . '...' . substr($this->api_key, -8);
        log_message('debug', 'Syncr/WAAdmin: Token (length: ' . strlen($this->api_key) . ', preview: ' . $token_preview . ')');

        $base_url     = !empty($this->api_url) ? $this->api_url : 'https://waadmin.syncr.in/v1/message/send-message';
        $phone_number = str_replace('+', '', $phone);

        $data = array(
            'to'       => $phone_number,
            'type'     => 'template',
            'template' => array(
                'language'   => array('policy' => 'deterministic', 'code' => 'en'),
                'name'       => 'otp_verification',
                'components' => array(
                    array(
                        'type'       => 'body',
                        'parameters' => array(array('type' => 'text', 'text' => $otp))
                    ),
                    array(
                        'type'       => 'button',
                        'sub_type'   => 'url',
                        'index'      => '0',
                        'parameters' => array(array('type' => 'text', 'text' => $otp))
                    )
                )
            )
        );

        $url = $base_url . '?token=' . urlencode($this->api_key);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        $masked_url = preg_replace('/token=[^&]+/', 'token=***', $url);
        log_message('debug', 'Syncr/WAAdmin Request URL: ' . $masked_url);
        log_message('debug', 'Syncr/WAAdmin Request Data: ' . json_encode($data));
        log_message('debug', 'Syncr/WAAdmin Response: ' . $response . ' | HTTP Code: ' . $http_code);

        if ($curl_error) {
            log_message('error', 'Syncr/WAAdmin CURL Error: ' . $curl_error);
            return array('success' => false, 'message' => 'CURL Error: ' . $curl_error);
        }

        $rd = json_decode($response, true);

        if ($http_code >= 200 && $http_code < 300) {
            if (isset($rd['status']) && in_array($rd['status'], array('success', 'sent', 'accepted'))) {
                return array('success' => true, 'message' => 'OTP sent successfully');
            } elseif (isset($rd['message']) && (stripos($rd['message'], 'success') !== false || stripos($rd['message'], 'sent') !== false)) {
                return array('success' => true, 'message' => 'OTP sent successfully');
            } elseif (empty($rd) || !isset($rd['error'])) {
                return array('success' => true, 'message' => 'OTP sent successfully');
            } else {
                $error_msg = $rd['error'] ?? ($rd['message'] ?? 'Unknown error');
                return array('success' => false, 'message' => 'API Error: ' . $error_msg);
            }
        }

        $error_msg = 'HTTP ' . $http_code;
        if (isset($rd['message']))      { $error_msg = $rd['message']; }
        elseif (isset($rd['error']))    { $error_msg = is_array($rd['error'])  ? json_encode($rd['error'])  : $rd['error']; }
        elseif (isset($rd['errors']))   { $error_msg = is_array($rd['errors']) ? implode(', ', $rd['errors']) : $rd['errors']; }
        elseif (!empty($response))      { $error_msg = $response; }

        log_message('error', 'Syncr/WAAdmin API Error: ' . $error_msg . ' | HTTP Code: ' . $http_code);
        return array('success' => false, 'message' => $error_msg);
    }
}
