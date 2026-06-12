<?php
// Temporary probe — delete after debugging
$_SERVER['REQUEST_URI'] = '/property/owner/dashboard';
$_SERVER['SCRIPT_NAME'] = '/property/index.php';
$uri = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
$uri = isset($uri['path']) ? $uri['path'] : '';
if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
    $uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
} elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
    $uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
}
echo "stripped uri: " . trim($uri, '/') . "\n";
