<?php
/**
 * Test admin property save through Next.js proxy (localhost:3000).
 * Usage: php tools/test_admin_property_save_proxy.php [property_id]
 */
$base = 'http://localhost:3000';
$propertyId = isset($argv[1]) ? (int) $argv[1] : 28;
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nb_admin_cookies_3000.txt';

function http_request($url, $opts = array())
{
    $ch = curl_init($url);
    $defaults = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
    );
    curl_setopt_array($ch, $defaults);
    foreach ($opts as $k => $v) {
        curl_setopt($ch, $k, $v);
    }
    $raw = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        throw new RuntimeException('curl error: ' . $err);
    }
    $headerSize = (int) $info['header_size'];
    return array(
        'code' => (int) $info['http_code'],
        'headers' => substr($raw, 0, $headerSize),
        'body' => substr($raw, $headerSize),
    );
}

echo "=== Proxy test via {$base} property #{$propertyId} ===\n\n";

echo "1) Login\n";
$login = http_request($base . '/api/nb/login', array(
    CURLOPT_POST => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Accept: application/json'),
    CURLOPT_POSTFIELDS => json_encode(array(
        'login' => 'admin@nobroker.com',
        'password' => 'Admin@123',
    )),
));
echo "HTTP {$login['code']}\n";
$loginJson = json_decode($login['body'], true);
if (empty($loginJson['success'])) {
    echo $login['body'] . "\n";
    exit(1);
}

echo "2) Load edit form\n";
$edit = http_request($base . '/panel/property/edit/' . $propertyId, array(
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
));
echo "HTTP {$edit['code']}\n";
if ($edit['code'] !== 200) {
    echo substr($edit['body'], 0, 500) . "\n";
    exit(1);
}
if (preg_match('/<form[^>]+action="([^"]+)"/i', $edit['body'], $fm)) {
    echo "Form action: {$fm[1]}\n";
    if (strpos($fm[1], ':3000') === false && strpos($fm[1], '/panel/property/save') === false) {
        echo "WARN: form action may leave localhost:3000 (session cookie will not be sent)\n";
    }
}
if (!preg_match('/name="admin_property_token"\s+value="([a-f0-9]+)"/', $edit['body'], $m)) {
    echo "Could not find admin_property_token\n";
    exit(1);
}
$token = $m[1];

if (!preg_match('/name="owner_id"[^>]*>.*?<option[^>]*value="(\d+)"[^>]*selected/s', $edit['body'], $om)) {
    if (!preg_match('/name="owner_id"[^>]*>.*?<option value="(\d+)"/s', $edit['body'], $om)) {
        echo "Could not find owner_id\n";
        exit(1);
    }
}
$ownerId = (int) $om[1];

echo "3) POST save (flags OFF except is_active)\n";
$postFields = array(
    'admin_save' => '1',
    'admin_property_token' => $token,
    'property_id' => (string) $propertyId,
    'image_action' => 'replace',
    'cover_index' => '0',
    'owner_id' => (string) $ownerId,
    'is_active' => '1',
    'is_featured' => '0',
    'tags_best_rate_localities' => '0',
    'tags_high_growth_localities' => '0',
    'is_recommended' => '0',
    'is_newly_launched' => '0',
    'is_verified_property' => '0',
    'is_premium' => '0',
    'title' => 'Proxy test ' . date('H:i:s'),
    'property_type' => 'apartment',
    'listing_type' => 'rent',
    'price' => '45000',
    'address' => 'Block B, Sunrise Apartments, 100ft Road',
    'locality' => 'Koramangala',
    'city_id' => '1',
    'description' => 'proxy test',
    'bedrooms' => '3',
    'bathrooms' => '2',
    'area_sqft' => '1200',
);

$formAction = isset($fm[1]) ? $fm[1] : ($base . '/panel/property/save');
if (strpos($formAction, 'http') !== 0) {
    $formAction = rtrim($base, '/') . $formAction;
}
$save = http_request($formAction, array(
    CURLOPT_POST => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_POSTFIELDS => $postFields,
));
echo "POST URL: {$formAction}\n";
echo "HTTP {$save['code']}\n";
if (preg_match('/Location:\s*(.+)/i', $save['headers'], $loc)) {
    echo 'Redirect: ' . trim($loc[1]) . "\n";
}
if ($save['code'] >= 400) {
    echo substr($save['body'], 0, 800) . "\n";
    exit(1);
}

echo "4) DB check\n";
$pdo = new PDO('mysql:host=127.0.0.1;dbname=property;charset=utf8mb4', 'root', '');
$row = $pdo->query("SELECT id, title, is_featured, is_recommended, is_newly_launched, is_verified_property, is_premium, tags_best_rate_localities, tags_high_growth_localities FROM nb_properties WHERE id = {$propertyId}")->fetch(PDO::FETCH_ASSOC);
print_r($row);

$allOff = $row && (int) $row['is_featured'] === 0
    && (int) $row['is_recommended'] === 0
    && (int) $row['is_newly_launched'] === 0
    && (int) $row['is_verified_property'] === 0
    && (int) $row['is_premium'] === 0;

echo $allOff ? "\nPASS: admin flags saved via proxy\n" : "\nFAIL: flags not saved correctly via proxy\n";
exit($allOff ? 0 : 1);
