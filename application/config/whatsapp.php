<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| WhatsApp OTP Configuration (Syncr / WAAdmin)
|--------------------------------------------------------------------------
*/
$config['whatsapp'] = array(

    /*
     | Provider: syncr
     | API endpoint for sending WhatsApp messages via WAAdmin
     */
    'provider'         => 'syncr',
    'api_url'          => 'https://waadmin.syncr.in/api/send-message',

    /*
     | Your WhatsApp sender number (with country code, no +)
     */
    'from_number'      => '919790919412',

    /*
     | API key / token issued by Syncr dashboard
     */
    'api_key'          => '',          // <-- fill in your Syncr API key

    /*
     | OTP message template.
     | Use {otp} as placeholder — it will be replaced with the actual OTP.
     */
    'otp_template'     => 'Your DVM verification OTP is: {otp}. Valid for 1 minute. Do not share it with anyone.',

    /*
     | development_mode = true  → OTP is returned in the API response (no WhatsApp sent)
     | development_mode = false → OTP is sent via WhatsApp (production)
     */
    'development_mode' => false,
);
