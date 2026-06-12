<?php
// One-off: simulate CI URI strip (delete after use)
$_SERVER['REQUEST_URI'] = '/property/owner/dashboard';
$_SERVER['SCRIPT_NAME'] = '/property/index.php';
$uri = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
$path = isset($uri['path']) ? $uri['path'] : '';
if (isset($_SERVER['SCRIPT_NAME'][0])) {
    if (strpos($path, $_SERVER['SCRIPT_NAME']) === 0) {
        $path = (string) substr($path, strlen($_SERVER['SCRIPT_NAME']));
    } elseif (strpos($path, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
        $path = (string) substr($path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
    }
}
echo trim($path, '/'), "\n";
