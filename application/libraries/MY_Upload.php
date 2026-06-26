<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Applies shared upload size limits from application/config/upload_limits.php.
 * Legacy 2 MB / 5 MB caps in controllers are raised to default_max_kb automatically.
 */
class MY_Upload extends CI_Upload
{
    public function initialize(array $config = array(), $reset = TRUE)
    {
        $CI =& get_instance();
        $CI->config->load('upload_limits', TRUE);
        $default_kb = (int) $CI->config->item('default_max_kb', 'upload_limits');
        if ($default_kb < 1) {
            $default_kb = 15360;
        }
        $legacy = $CI->config->item('legacy_max_kb', 'upload_limits');
        if (!is_array($legacy)) {
            $legacy = array(2048, 5120);
        }

        $max = isset($config['max_size']) ? (int) $config['max_size'] : 0;
        if ($max === 0 || in_array($max, $legacy, true)) {
            $config['max_size'] = $default_kb;
        }

        return parent::initialize($config, $reset);
    }
}
