<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| WhatsApp OTP Configuration (AskEva)
|--------------------------------------------------------------------------
*/
$config['whatsapp'] = array(

    /*
     | Provider: askeva | syncr | waadmin
     | AskEva endpoint for WhatsApp template messages
     */
    'provider'         => 'askeva',
    'api_url'          => 'https://backend.askeva.io/v1/message/send-message',

    /*
     | Your WhatsApp sender number (with country code, no +)
     */
    'from_number'      => '919790919412',

    /*
     | API token from AskEva dashboard (?token=...)
     */
    'api_key'          => '',

    /*
     | WhatsApp template name registered in AskEva
     */
    'otp_template'     => 'otp_verification',

    /*
     | OTP length (4 digits)
     */
    'otp_length'       => 4,

    /*
     | OTP validity in seconds
     */
    'otp_ttl_seconds'  => 300,

    /*
     | development_mode = true  → OTP returned in API response (no WhatsApp sent)
     | development_mode = false → OTP sent via WhatsApp (production)
     */
    'development_mode' => true,
);
