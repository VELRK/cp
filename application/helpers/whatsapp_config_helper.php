<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Normalize whatsapp config from CodeIgniter section (flat or legacy nested).
 *
 * @param CI_Controller|CI_Model $ci
 * @return array
 */
function nb_whatsapp_config($ci)
{
    $ci->config->load('whatsapp', TRUE);

    $cfg = $ci->config->item('whatsapp');
    if (!is_array($cfg)) {
        $cfg = array();
    }

    // Legacy files used $config['whatsapp'] = array(...) inside whatsapp.php.
    if (isset($cfg['whatsapp']) && is_array($cfg['whatsapp']) && !isset($cfg['api_key'])) {
        $cfg = $cfg['whatsapp'];
    }

    $keys = array(
        'provider',
        'api_url',
        'from_number',
        'api_key',
        'otp_template',
        'otp_template_language',
        'otp_length',
        'otp_ttl_seconds',
        'development_mode',
    );
    foreach ($keys as $key) {
        if (!array_key_exists($key, $cfg) || $cfg[$key] === '' || $cfg[$key] === null) {
            $from_section = $ci->config->item($key, 'whatsapp');
            if ($from_section !== null && $from_section !== '') {
                $cfg[$key] = $from_section;
            }
        }
    }

    if (empty($cfg['api_key'])) {
        $from_env = getenv('ASKEVA_API_KEY');
        if ($from_env !== false && $from_env !== '') {
            $cfg['api_key'] = $from_env;
        } elseif (!empty($_SERVER['ASKEVA_API_KEY'])) {
            $cfg['api_key'] = (string) $_SERVER['ASKEVA_API_KEY'];
        }
    }

    $local_whatsapp = APPPATH . 'config/whatsapp.local.php';
    if (empty($cfg['api_key']) && is_file($local_whatsapp)) {
        $from_local = include $local_whatsapp;
        if (is_string($from_local) && $from_local !== '') {
            $cfg['api_key'] = $from_local;
        } elseif (is_array($from_local) && !empty($from_local['api_key'])) {
            $cfg['api_key'] = (string) $from_local['api_key'];
            foreach ($from_local as $k => $v) {
                if ($v !== '' && $v !== null) {
                    $cfg[$k] = $v;
                }
            }
        }
    }

    return $cfg;
}
