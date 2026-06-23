<?php
/**
 * CLI test: admin property save with listing flags.
 * Usage: php tools/test_admin_property_save.php [property_id]
 */
$base = 'http://127.0.0.1:8080/cp/index.php';
$propertyId = isset($argv[1]) ? (int) $argv[1] : 28;
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nb_admin_cookies.txt';

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
        'redirect' => isset($info['redirect_url']) ? (string) $info['redirect_url'] : '',
    );
}

echo "=== 1) Login as admin ===\n";
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
echo "Login OK\n";

echo "\n=== 2) Load edit form for property #{$propertyId} ===\n";
$edit = http_request($base . '/panel/property/edit/' . $propertyId, array(
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
));
echo "HTTP {$edit['code']}\n";
if ($edit['code'] !== 200) {
    echo substr($edit['body'], 0, 500) . "\n";
    exit(1);
}
if (!preg_match('/name="admin_property_token"\s+value="([a-f0-9]+)"/', $edit['body'], $m)) {
    echo "Could not find admin_property_token in form HTML\n";
    exit(1);
}
$token = $m[1];
echo "Token: {$token}\n";

if (!preg_match('/name="owner_id"[^>]*>.*?<option[^>]*value="(\d+)"[^>]*selected/', $edit['body'], $om)) {
    if (!preg_match('/name="owner_id"[^>]*>.*?<option value="(\d+)"/s', $edit['body'], $om)) {
        echo "Could not find owner_id\n";
        exit(1);
    }
}
$ownerId = (int) $om[1];
echo "Owner ID: {$ownerId}\n";

echo "\n=== 3) POST save with all flags ON ===\n";
$postFields = array(
    'admin_save' => '1',
    'admin_property_token' => $token,
    'property_id' => (string) $propertyId,
    'image_action' => 'replace',
    'cover_index' => '0',
    'owner_id' => (string) $ownerId,
    'is_active' => '1',
    'is_featured' => '1',
    'tags_best_rate_localities' => '1',
    'tags_high_growth_localities' => '1',
    'is_recommended' => '1',
    'is_newly_launched' => '1',
    'is_verified_property' => '1',
    'is_premium' => '1',
    'title' => 'Curl test flags ' . date('H:i:s'),
    'property_type' => 'apartment',
    'listing_type' => 'rent',
    'price' => '45000',
    'address' => 'Block B, Sunrise Apartments, 100ft Road',
    'locality' => 'Koramangala',
    'city_id' => '1',
    'description' => 'curl test',
    'bedrooms' => '3',
    'bathrooms' => '2',
    'area_sqft' => '1200',
);

$save = http_request($base . '/panel/property/save', array(
    CURLOPT_POST => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_POSTFIELDS => $postFields,
));
echo "HTTP {$save['code']}\n";
if (preg_match('/Location:\s*(.+)/i', $save['headers'], $loc)) {
    echo 'Redirect: ' . trim($loc[1]) . "\n";
}
if ($save['code'] >= 400) {
    echo substr($save['body'], 0, 800) . "\n";
    exit(1);
}

echo "\n=== 4) DB check ===\n";
$pdo = new PDO('mysql:host=127.0.0.1;dbname=property;charset=utf8mb4', 'root', '');
$row = $pdo->query("SELECT id, title, is_featured, is_recommended, is_newly_launched, is_verified_property, is_premium, tags_best_rate_localities, tags_high_growth_localities FROM nb_properties WHERE id = {$propertyId}")->fetch(PDO::FETCH_ASSOC);
print_r($row);

$allOn = $row && (int) $row['is_featured'] === 1
    && (int) $row['is_recommended'] === 1
    && (int) $row['is_newly_launched'] === 1
    && (int) $row['is_verified_property'] === 1
    && (int) $row['is_premium'] === 1
    && (int) $row['tags_best_rate_localities'] === 1
    && (int) $row['tags_high_growth_localities'] === 1;

echo $allOn ? "\nPASS: all flags saved as 1\n" : "\nFAIL: flags not all 1 in DB\n";
exit($allOn ? 0 : 1);
