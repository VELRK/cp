<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Upload size limits (kilobytes — CodeIgniter Upload library uses KB)
|--------------------------------------------------------------------------
| default_max_kb: standard images (property photos, banners, profile, etc.)
| document_max_kb: PDFs, audio, video attachments
| legacy_max_kb: old caps replaced automatically by MY_Upload
*/
$config['default_max_kb']   = 15360; // 15 MB
$config['document_max_kb']  = 30720; // 30 MB
$config['legacy_max_kb']    = array(2048, 5120);
