<?php
define('ENVIRONMENT', 'development');
$system_path = 'system';
$application_folder = 'application';
if (($_temp = realpath($system_path)) !== FALSE) {
	$system_path = $_temp . '/';
} else {
	$system_path = rtrim($system_path, '/').'/';
}
define('BASEPATH', str_replace('\\', '/', $system_path));
define('APPPATH', $application_folder.'/');
define('VIEWPATH', $application_folder.'/views/');
require_once BASEPATH.'core/CodeIgniter.php';
