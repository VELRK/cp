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
    private $otp_template;
    private $otp_template_language;
    private $approved_templates_cache;
    private $ci;

    public function __construct($config = array())
    {
        $this->ci =& get_instance();

        $this->ci->load->helper('whatsapp_config');
        $file_config = nb_whatsapp_config($this->ci);
        $config = array_merge($file_config, $config);

        $this->provider     = isset($config['provider']) ? (string) $config['provider'] : 'askeva';
        $this->api_key      = isset($config['api_key']) ? (string) $config['api_key'] : '';
        $this->api_url      = isset($config['api_url']) ? (string) $config['api_url'] : 'https://backend.askeva.io/v1/message/send-message';
        $this->from_number  = isset($config['from_number']) ? (string) $config['from_number'] : '';
        $this->otp_template         = isset($config['otp_template']) ? (string) $config['otp_template'] : 'authentication';
        $this->otp_template_language = isset($config['otp_template_language']) ? (string) $config['otp_template_language'] : 'en';
        $this->approved_templates_cache = null;

        if (!empty($this->api_key)) {
            $masked_key = substr($this->api_key, 0, 8) . '...' . substr($this->api_key, -8);
            log_message('debug', 'Whatsapp_library initialized - Provider: ' . $this->provider . ', API Key: ' . $masked_key . ' (length: ' . strlen($this->api_key) . ')');
        } else {
            log_message('debug', 'Whatsapp_library initialized - API Key is empty (development mode expected).');
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
        if (in_array($this->provider, array('syncr', 'waadmin', 'askeva'), true)) {
            $phone_number = str_replace('+', '', $phone);
            $base_url     = !empty($this->api_url) ? $this->api_url : 'https://backend.askeva.io/v1/message/send-message';
            $url          = $base_url . '?token=' . urlencode($this->api_key);

            $template_def = $this->_find_approved_template($this->otp_template);
            if (!$template_def) {
                return '# Template "' . $this->otp_template . '" is not approved on AskEva. Create it in WhatsApp Manager first.';
            }

            $data = $this->_build_template_request_data($phone_number, $otp, $template_def);
            if (isset($data['error'])) {
                return '# ' . $data['error'];
            }

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
            case 'askeva':
                return $this->send_via_template_api($phone, $otp);
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

    private function send_via_template_api($phone, $otp)
    {
        if (empty($this->api_key) || trim($this->api_key) === '' || $this->api_key === 'YOUR_SYNCR_TOKEN_HERE') {
            log_message('error', 'WhatsApp API key is not configured.');
            return array('success' => false, 'message' => 'WhatsApp API key is not configured. Set api_key in application/config/whatsapp.php');
        }

        $template_def = $this->_find_approved_template($this->otp_template);
        if (!$template_def) {
            $msg = 'WhatsApp template "' . $this->otp_template . '" is not approved on your AskEva account. '
                . 'Create an AUTHENTICATION template with this name in AskEva/WhatsApp Manager and wait for Meta approval.';
            log_message('error', $msg);
            return array('success' => false, 'message' => $msg);
        }

        $token_preview = substr($this->api_key, 0, 8) . '...' . substr($this->api_key, -8);
        log_message('debug', 'WhatsApp template API: Token (length: ' . strlen($this->api_key) . ', preview: ' . $token_preview . ')');

        $base_url     = !empty($this->api_url) ? $this->api_url : 'https://backend.askeva.io/v1/message/send-message';
        $phone_number = str_replace('+', '', $phone);

        $data = $this->_build_template_request_data($phone_number, $otp, $template_def);
        if (isset($data['error'])) {
            return array('success' => false, 'message' => $data['error']);
        }

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
        log_message('debug', 'WhatsApp template API Request URL: ' . $masked_url);
        log_message('debug', 'WhatsApp template API Request Data: ' . json_encode($data));
        log_message('debug', 'WhatsApp template API Response: ' . $response . ' | HTTP Code: ' . $http_code);

        if ($curl_error) {
            log_message('error', 'WhatsApp template API CURL Error: ' . $curl_error);
            return array('success' => false, 'message' => 'CURL Error: ' . $curl_error);
        }

        $rd = json_decode($response, true);

        if ($http_code >= 200 && $http_code < 300) {
            if (isset($rd['messages']) && is_array($rd['messages']) && !empty($rd['messages'])) {
                return array('success' => true, 'message' => 'OTP sent successfully');
            }
            if (isset($rd['status']) && in_array($rd['status'], array('success', 'sent', 'accepted'), true)) {
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

        log_message('error', 'WhatsApp template API Error: ' . $error_msg . ' | HTTP Code: ' . $http_code);
        return array('success' => false, 'message' => $error_msg);
    }

    private function _templates_api_url()
    {
        $base = !empty($this->api_url) ? $this->api_url : 'https://backend.askeva.io/v1/message/send-message';
        return preg_replace('#/message/send-message$#', '/templates', $base);
    }

    private function _fetch_approved_templates()
    {
        if (is_array($this->approved_templates_cache)) {
            return $this->approved_templates_cache;
        }

        $url = $this->_templates_api_url() . '?token=' . urlencode($this->api_key);
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 20,
        ));
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->approved_templates_cache = array();
        if ($http_code >= 200 && $http_code < 300) {
            $rd = json_decode($response, true);
            if (isset($rd['data']) && is_array($rd['data'])) {
                $this->approved_templates_cache = $rd['data'];
            }
        }

        return $this->approved_templates_cache;
    }

    private function _find_approved_template($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        foreach ($this->_fetch_approved_templates() as $template) {
            if (!is_array($template)) {
                continue;
            }
            if (isset($template['name'], $template['status'])
                && strcasecmp((string) $template['name'], $name) === 0
                && strtoupper((string) $template['status']) === 'APPROVED') {
                return $template;
            }
        }

        return null;
    }

    private function _body_variable_count($text)
    {
        if (!is_string($text) || $text === '') {
            return 0;
        }
        if (!preg_match_all('/\{\{(\d+)\}\}/', $text, $matches)) {
            return 0;
        }
        $max = 0;
        foreach ($matches[1] as $num) {
            $max = max($max, (int) $num);
        }
        return $max;
    }

    private function _build_template_request_data($phone_number, $otp, array $template_def)
    {
        $language = !empty($template_def['language']) ? (string) $template_def['language'] : $this->otp_template_language;
        $components = array();

        foreach ($template_def['components'] ?? array() as $component) {
            if (!is_array($component)) {
                continue;
            }
            $type = strtoupper((string) ($component['type'] ?? ''));

            if ($type === 'BODY') {
                $var_count = $this->_body_variable_count($component['text'] ?? '');
                if ($var_count < 1) {
                    $var_count = 1;
                }
                $parameters = array();
                for ($i = 1; $i <= $var_count; $i++) {
                    $parameters[] = array(
                        'type' => 'text',
                        'text' => ($i === 1) ? (string) $otp : (string) $otp,
                    );
                }
                $components[] = array(
                    'type'       => 'body',
                    'parameters' => $parameters,
                );
            }
        }

        if (empty($components)) {
            $components[] = array(
                'type'       => 'body',
                'parameters' => array(array('type' => 'text', 'text' => (string) $otp)),
            );
        }

        return array(
            'to'   => $phone_number,
            'type' => 'template',
            'template' => array(
                'language'   => array('policy' => 'deterministic', 'code' => $language),
                'name'       => (string) $template_def['name'],
                'components' => $components,
            ),
        );
    }
}
