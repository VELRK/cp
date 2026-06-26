<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| WhatsApp OTP Configuration (AskEva)
|--------------------------------------------------------------------------
| Loaded with $this->config->load('whatsapp', TRUE) — keys are flat (CI section).
| Override via ASKEVA_API_KEY in .env or application/config/whatsapp.local.php
*/
$askeva_api_key = getenv('ASKEVA_API_KEY');
if ($askeva_api_key === false || $askeva_api_key === '') {
    $askeva_api_key = isset($_SERVER['ASKEVA_API_KEY']) ? (string) $_SERVER['ASKEVA_API_KEY'] : '';
}

$whatsapp_local_overrides = array();
$local_whatsapp = APPPATH . 'config/whatsapp.local.php';
if (is_file($local_whatsapp)) {
    $from_local = include $local_whatsapp;
    if (is_string($from_local) && $from_local !== '') {
        $askeva_api_key = $from_local;
    } elseif (is_array($from_local)) {
        $whatsapp_local_overrides = $from_local;
        if (!empty($from_local['api_key'])) {
            $askeva_api_key = (string) $from_local['api_key'];
        }
    }
}

if ($askeva_api_key === '') {
    $askeva_api_key = '5c9fbbe16cbd3ec293504d7d4d758e1adf160554f488609ef64df040d05f2176e44afba64867f635ae34fa48c296203707809db18d5b13e2609176cf18642f10';
}

$config['provider']         = 'askeva';
$config['api_url']          = 'https://backend.askeva.io/v1/message/send-message';
$config['from_number']      = '919790919412';
$config['api_key']          = $askeva_api_key;
$config['otp_template']          = 'authentication';
$config['otp_template_language'] = 'en';
$config['otp_length']       = 4;
$config['otp_ttl_seconds']  = 300;
$config['development_mode'] = ($askeva_api_key === '');

if (!empty($whatsapp_local_overrides)) {
    $config = array_merge($config, $whatsapp_local_overrides);
}
