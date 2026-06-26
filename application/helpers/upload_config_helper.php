<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('nb_upload_max_kb')) {
    function nb_upload_max_kb($kind = 'image')
    {
        $CI =& get_instance();
        $CI->config->load('upload_limits', TRUE);
        if ($kind === 'document' || $kind === 'video' || $kind === 'audio') {
            $kb = (int) $CI->config->item('document_max_kb', 'upload_limits');
            return $kb > 0 ? $kb : 30720;
        }
        $kb = (int) $CI->config->item('default_max_kb', 'upload_limits');
        return $kb > 0 ? $kb : 15360;
    }
}

if (!function_exists('nb_upload_max_bytes')) {
    function nb_upload_max_bytes($kind = 'image')
    {
        return nb_upload_max_kb($kind) * 1024;
    }
}

if (!function_exists('nb_upload_max_label')) {
    function nb_upload_max_label($kind = 'image')
    {
        $kb = nb_upload_max_kb($kind);
        if ($kb >= 1024 && $kb % 1024 === 0) {
            return ((int) ($kb / 1024)) . ' MB';
        }
        return round($kb / 1024, 1) . ' MB';
    }
}
