<?php
header('Content-Type: text/plain; charset=UTF-8');
$keys = array('REQUEST_URI', 'SCRIPT_NAME', 'PATH_INFO', 'SCRIPT_FILENAME', 'DOCUMENT_ROOT');
foreach ($keys as $k) {
    echo $k . '=' . (isset($_SERVER[$k]) ? $_SERVER[$k] : '') . "\n";
}
