<?php
define('BASEPATH', dirname(__DIR__) . '/system/');
$config = array();
require dirname(__DIR__) . '/application/config/api_catalog.php';
echo json_encode($config['api_catalog'], JSON_UNESCAPED_SLASHES);
