<?php
/**
 * Simulates browser: login, load edit form, parse HTML fields, POST with toggles ON (duplicate names).
 * Usage: php tools/test_browser_form_save.php [property_id]
 */
$base = 'http://localhost:3000';
$propertyId = isset($argv[1]) ? (int) $argv[1] : 28;
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nb_browser_form_cookies.txt';

function http($url, $opts = array())
{
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 120,
    ));
    foreach ($opts as $k => $v) {
        curl_setopt($ch, $k, $v);
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        throw new RuntimeException('curl: ' . curl_error($ch));
    }
    $info = curl_getinfo($ch);
    curl_close($ch);
    $hs = (int) $info['header_size'];
    return array(
        'code' => (int) $info['http_code'],
        'headers' => substr($raw, 0, $hs),
        'body' => substr($raw, $hs),
    );
}

function parse_form_fields($html)
{
    $fields = array();
    if (!preg_match('/<form[^>]+action="([^"]*)"[^>]*method="post"/i', $html, $fm)) {
        throw new RuntimeException('No POST form found');
    }
    $action = html_entity_decode($fm[1], ENT_QUOTES, 'UTF-8');

    if (preg_match_all('/<input[^>]+>/i', $html, $inputs)) {
        foreach ($inputs[0] as $tag) {
            if (!preg_match('/name="([^"]+)"/i', $tag, $nm)) {
                continue;
            }
            $name = $nm[1];
            if (preg_match('/type="(checkbox|radio)"/i', $tag)) {
                if (preg_match('/\schecked(?:|\s|>)/i', $tag)) {
                    if (!isset($fields[$name])) {
                        $fields[$name] = array();
                    }
                    if (!is_array($fields[$name])) {
                        $fields[$name] = array($fields[$name]);
                    }
                    $fields[$name][] = preg_match('/value="([^"]*)"/i', $tag, $vm) ? $vm[1] : '1';
                }
                continue;
            }
            if (preg_match('/type="(submit|button|file)"/i', $tag)) {
                continue;
            }
            $val = preg_match('/value="([^"]*)"/i', $tag, $vm) ? html_entity_decode($vm[1], ENT_QUOTES, 'UTF-8') : '';
            $fields[$name] = $val;
        }
    }

    if (preg_match_all('/<select[^>]+name="([^"]+)"[^>]*>(.*?)<\/select>/is', $html, $selects, PREG_SET_ORDER)) {
        foreach ($selects as $sel) {
            $name = $sel[1];
            if (preg_match('/<option[^>]+selected[^>]*value="([^"]*)"/i', $sel[2], $om)
                || preg_match('/<option[^>]+value="([^"]*)"[^>]*selected/i', $sel[2], $om)) {
                $fields[$name] = html_entity_decode($om[1], ENT_QUOTES, 'UTF-8');
            }
        }
    }

    if (preg_match_all('/<textarea[^>]+name="([^"]+)"[^>]*>(.*?)<\/textarea>/is', $html, $tas, PREG_SET_ORDER)) {
        foreach ($tas as $ta) {
            $fields[$ta[1]] = html_entity_decode(trim($ta[2]), ENT_QUOTES, 'UTF-8');
        }
    }

    return array('action' => $action, 'fields' => $fields);
}

function curl_post_fields(array $fields)
{
    $out = array();
    foreach ($fields as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $item) {
                $out[] = rawurlencode($k) . '=' . rawurlencode($item);
            }
        } else {
            $out[] = rawurlencode($k) . '=' . rawurlencode($v);
        }
    }
    return implode('&', $out);
}

echo "=== Browser-style save test property #{$propertyId} via {$base} ===\n\n";

$login = http($base . '/api/nb/login', array(
    CURLOPT_POST => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    CURLOPT_POSTFIELDS => json_encode(array('login' => 'admin@nobroker.com', 'password' => 'Admin@123')),
));
echo "Login HTTP {$login['code']}\n";
if (strpos($login['headers'], 'nb_token') === false) {
    echo "WARN: nb_token cookie not in login response headers\n";
}

$edit = http($base . '/panel/property/edit/' . $propertyId, array(
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
));
echo "Edit form HTTP {$edit['code']}\n";
if ($edit['code'] !== 200) {
    echo substr($edit['body'], 0, 400) . "\n";
    exit(1);
}

$form = parse_form_fields($edit['body']);
echo 'Form action: ' . $form['action'] . "\n";

$fields = $form['fields'];
$flags = array('is_active', 'is_featured', 'tags_best_rate_localities', 'tags_high_growth_localities',
    'is_recommended', 'is_newly_launched', 'is_verified_property', 'is_premium');
foreach ($flags as $f) {
    $fields[$f] = array('0', '1');
}

$postUrl = $form['action'];
if (strpos($postUrl, 'http') !== 0) {
    $postUrl = rtrim($base, '/') . $postUrl;
}

$save = http($postUrl, array(
    CURLOPT_POST => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
    CURLOPT_POSTFIELDS => curl_post_fields($fields),
));
echo "Save HTTP {$save['code']}\n";
if (preg_match('/Location:\s*(.+)/i', $save['headers'], $loc)) {
    echo 'Redirect: ' . trim($loc[1]) . "\n";
}
if (stripos($save['body'], 'Admin login required') !== false) {
    echo "FAIL: admin auth rejected on save\n";
    exit(1);
}
if ($save['code'] >= 400) {
    echo substr($save['body'], 0, 600) . "\n";
    exit(1);
}

$pdo = new PDO('mysql:host=127.0.0.1;dbname=property;charset=utf8mb4', 'root', '');
$row = $pdo->query("SELECT is_featured, is_recommended, is_verified_property, is_premium FROM nb_properties WHERE id = {$propertyId}")->fetch(PDO::FETCH_ASSOC);
print_r($row);

$reload = http($base . '/panel/property/edit/' . $propertyId, array(
    CURLOPT_COOKIEFILE => $cookieFile,
));
$checked = 0;
foreach ($flags as $f) {
    if ($f === 'is_active') {
        continue;
    }
    if (preg_match('/name="' . preg_quote($f, '/') . '"[^>]*checked/i', $reload['body'])) {
        $checked++;
    }
}
echo "Checked toggles in reloaded HTML: {$checked}/" . (count($flags) - 1) . "\n";
$ok = $row && (int) $row['is_featured'] === 1 && (int) $row['is_recommended'] === 1;
echo $ok ? "PASS\n" : "FAIL\n";
exit($ok ? 0 : 1);
