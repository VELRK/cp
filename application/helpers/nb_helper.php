<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * URL-safe slug from a title (ASCII; good for SEO paths).
 */
function nb_slugify($text)
{
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    if ($text === '') {
        return 'property';
    }
    return substr($text, 0, 200);
}

/**
 * Upgrade http:// image URLs to https:// to avoid mixed-content on HTTPS pages.
 * Relative URLs and https:// are unchanged.
 *
 * @param string $url Absolute or relative URL
 * @return string
 */
function nb_upgrade_http_image_url($url)
{
    $url = (string) $url;
    if ($url === '' || strpos($url, 'http://') !== 0) {
        return $url;
    }
    return str_replace('http://', 'https://', $url);
}

/**
 * Public URL for uploads/ or assets/ paths (handles /cp subdirectory on production).
 *
 * @param string|null $path Relative path e.g. uploads/profiles/x.jpg
 * @return string|null
 */
function nb_public_asset_url($path)
{
    if ($path === null) {
        return null;
    }
    $path = trim((string) $path);
    if ($path === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $path)) {
        return nb_fix_cp_asset_url($path);
    }
    $CI =& get_instance();
    return nb_fix_cp_asset_url($CI->config->base_url($path));
}

/**
 * Ensure city image file exists under web-served assets/ path (migrate legacy public/assets copies).
 *
 * @param string|null $path DB value e.g. assets/images/city/foo.png
 * @return string|null Canonical relative path or null when empty
 */
function nb_ensure_city_image_on_disk($path)
{
    if ($path === null) {
        return null;
    }
    $path = trim((string) $path);
    if ($path === '') {
        return null;
    }
    $path = preg_replace('#^public/#', '', $path);
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $canonical = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($canonical)) {
        return $path;
    }

    $legacy = FCPATH . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($legacy)) {
        $dir = dirname($canonical);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (@copy($legacy, $canonical)) {
            return $path;
        }
    }

    return $path;
}

/** Public URL for nb_cities.image (handles /cp/ prefix and legacy storage). */
function nb_city_image_url($path)
{
    $path = nb_ensure_city_image_on_disk($path);
    if ($path === null || $path === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $path)) {
        return nb_fix_cp_asset_url($path);
    }
    return nb_public_asset_url($path);
}

/** Ensure assets/images/city exists for uploads. */
function nb_city_image_upload_dir()
{
    $rel = 'assets/images/city/';
    $abs = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (!is_dir($abs)) {
        @mkdir($abs, 0755, true);
    }
    return array('relative' => $rel, 'absolute' => $abs);
}

/** Decode housing_news.multiImages JSON into relative file paths. */
function nb_decode_housing_news_images($raw)
{
    if (!is_string($raw) || trim($raw) === '') {
        return array();
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return array();
    }
    $clean = array();
    foreach ($decoded as $img) {
        if (!is_string($img)) {
            continue;
        }
        $img = trim($img);
        if ($img !== '') {
            $clean[] = $img;
        }
    }
    return array_values(array_unique($clean));
}

/** Absolute URLs for housing news gallery paths. */
function nb_housing_news_image_urls($raw)
{
    $urls = array();
    foreach (nb_decode_housing_news_images($raw) as $img) {
        $url = nb_public_asset_url($img);
        if ($url !== null && $url !== '') {
            $urls[] = $url;
        }
    }
    return $urls;
}

/**
 * Map housing_news row to blog/article shape for /api/blogs and /api/mobile/blogs.
 *
 * @param object $row housing_news DB row
 * @return array|null
 */
function nb_housing_news_to_blog($row)
{
    if (!$row) {
        return null;
    }
    $gallery = nb_housing_news_image_urls(isset($row->multiImages) ? $row->multiImages : null);
    $createdAt = isset($row->createdAt) ? (string) $row->createdAt : '';
    $date = '';
    $publishedAt = null;
    if ($createdAt !== '') {
        $ts = strtotime($createdAt);
        if ($ts) {
            $date = date('Y-m-d', $ts);
            $publishedAt = date(DATE_ATOM, $ts);
        } else {
            $date = $createdAt;
            $publishedAt = $createdAt;
        }
    }
    $title = isset($row->title) ? (string) $row->title : '';
    $subtitle = isset($row->subtitle) && $row->subtitle !== null ? (string) $row->subtitle : '';
    $description = isset($row->description) && $row->description !== null ? (string) $row->description : '';
    $excerpt = $subtitle;
    if ($excerpt === '' && $description !== '') {
        $plain = trim(strip_tags($description));
        $excerpt = strlen($plain) > 200 ? substr($plain, 0, 200) . '…' : $plain;
    }
    $author = isset($row->authorName) && $row->authorName !== null ? (string) $row->authorName : '';

    return array(
        'id' => isset($row->id) ? (int) $row->id : 0,
        'title' => $title,
        'name' => $title,
        'subtitle' => $subtitle,
        'author' => $author,
        'authorName' => $author,
        'date' => $date,
        'publishedAt' => $publishedAt,
        'createdAt' => $createdAt !== '' ? $createdAt : null,
        'excerpt' => $excerpt,
        'short_notes' => $subtitle,
        'description' => $description,
        'content' => $description,
        'category' => isset($row->category) ? (string) $row->category : 'market',
        'slug' => $title !== '' ? url_title($title, '-', true) : '',
        'gallery' => $gallery,
        'multiImages' => $gallery,
        'image' => count($gallery) > 0 ? $gallery[0] : null,
    );
}

/** Ensure asset URLs include /cp/ when the app is deployed under that folder. */
function nb_fix_cp_asset_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return $url;
    }
    $url = nb_upgrade_http_image_url($url);
    if (preg_match('#^https?://[^/]+/(uploads|assets)/#i', $url)
        && stripos($url, '/cp/uploads/') === false
        && stripos($url, '/cp/assets/') === false) {
        $url = preg_replace('#^(https?://[^/]+)/(?=uploads|assets)#i', '$1/cp/', $url);
    }
    return $url;
}

/**
 * Canonical public URL for a listing: always `/property/{slug}` when slug is set.
 *
 * SEO: Prefer a non-empty `slug` on every row (see Nb_property_model::unique_slug on save,
 * and migration `004_nb_properties_slug.sql` for backfill). If `slug` is empty (legacy data),
 * the path falls back to `/property/{id}` — Nb_property::view still resolves via get_by_id.
 *
 * @param object $p Row with at least `id`; `slug` recommended
 */
function nb_property_url($p)
{
    $CI =& get_instance();
    $slug = isset($p->slug) ? trim((string) $p->slug) : '';
    if ($slug !== '') {
        $seg = $slug;
    } else {
        $seg = (string) (int) $p->id;
    }
    return $CI->config->site_url('property/' . rawurlencode($seg));
}

/**
 * Meta description: plain text, single line, max length for SEO snippets.
 */
function nb_meta_description($text, $max = 158)
{
    $t = preg_replace('/\s+/u', ' ', strip_tags((string) $text));
    $t = trim($t);
    $len = function_exists('mb_strlen') ? mb_strlen($t, 'UTF-8') : strlen($t);
    if ($len <= $max) {
        return $t;
    }
    $cut = function_exists('mb_substr') ? mb_substr($t, 0, $max - 1, 'UTF-8') : substr($t, 0, $max - 1);
    return rtrim($cut) . '…';
}

/**
 * Human label for nb_properties.property_type (VARCHAR slug).
 */
function nb_property_type_label($type)
{
    $map = nb_property_types_map();
    $k = (string) $type;
    return isset($map[$k]) ? $map[$k] : ucwords(str_replace('_', ' ', $k));
}

/** @return array<string,string> slug => label */
/**
 * Format listing price for India (rent per month vs sale in Lacs/Cr).
 */
function nb_format_listing_price($price, $listing_type)
{
    $p = (float) $price;
    if ($listing_type === 'rent') {
        return '₹' . number_format($p, 0, '.', ',') . ' / month';
    }
    if ($p >= 10000000) {
        return '₹' . rtrim(rtrim(number_format($p / 10000000, 2, '.', ''), '0'), '.') . ' Cr';
    }
    if ($p >= 100000) {
        return '₹' . rtrim(rtrim(number_format($p / 100000, 2, '.', ''), '0'), '.') . ' Lacs';
    }
    return '₹' . number_format($p, 0, '.', ',');
}

/** @return array<string,string> slug => label */
/**
 * Move chosen image to index 0 (cover / main photo for cards & carousel).
 *
 * @param array $paths List of storage paths
 * @param int   $idx   0-based index of image to use as cover
 * @return array
 */
function nb_reorder_cover(array $paths, $idx)
{
    $paths = array_values(array_filter($paths));
    $n = count($paths);
    if ($n < 2) {
        return $paths;
    }
    $idx = max(0, min($n - 1, (int) $idx));
    if ($idx === 0) {
        return $paths;
    }
    $picked = $paths[$idx];
    unset($paths[$idx]);
    return array_merge(array($picked), array_values($paths));
}

/**
 * @return string|null Sanitized http(s) URL or null
 */
function nb_sanitize_video_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }
    if (strlen($url) > 512) {
        return null;
    }
    if (!preg_match('#^https?://#i', $url)) {
        if (preg_match('#^(?:www\.|m\.)?(?:youtube\.com|youtu\.be)|(?:www\.)?vimeo\.com#i', $url)) {
            $url = 'https://' . $url;
        } else {
            return null;
        }
    }
    $f = filter_var($url, FILTER_VALIDATE_URL);
    if ($f === false) {
        return null;
    }
    return $f;
}

/**
 * @return array{type: string, id: string}|null
 */
function nb_video_embed_parts($url)
{
    $url = nb_sanitize_video_url($url);
    if (!$url) {
        return null;
    }
    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/|m\.youtube\.com/watch\?v=)([a-zA-Z0-9_-]{11})#', $url, $m)) {
        return array('type' => 'youtube', 'id' => $m[1]);
    }
    if (preg_match('#youtube-nocookie\.com/embed/([a-zA-Z0-9_-]{11})#', $url, $m)) {
        return array('type' => 'youtube', 'id' => $m[1]);
    }
    if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) {
        return array('type' => 'vimeo', 'id' => $m[1]);
    }
    return null;
}

/**
 * Responsive iframe HTML for YouTube/Vimeo URLs; empty string if unsupported.
 */
function nb_video_embed_html($url)
{
    $p = nb_video_embed_parts($url);
    if (!$p) {
        return '';
    }
    if ($p['type'] === 'youtube') {
        return '<iframe class="nb-video-iframe" src="https://www.youtube.com/embed/' . htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') . '" title="Property video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
    }
    if ($p['type'] === 'vimeo') {
        return '<iframe class="nb-video-iframe" src="https://player.vimeo.com/video/' . htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') . '" title="Property video" allowfullscreen loading="lazy"></iframe>';
    }
    return '';
}

function nb_property_types_map()
{
    $fallback = array(
        'apartment'            => 'Apartment / Flat',
        'studio'               => 'Studio',
        'house'                => 'Independent House',
        'villa'                => 'Villa',
        'independent_floor'    => 'Independent Floor',
        'commercial'           => 'Commercial',
        'office'               => 'Office Space',
        'retail'               => 'Shop / Retail',
        'warehouse'            => 'Warehouse / Godown',
        'plot'                 => 'Plot / Land',
        'farmhouse'            => 'Farmhouse',
        'pg'                   => 'PG',
        'shared_flat'          => 'Shared Flat',
        'serviced_apartment'   => 'Serviced Apartment',
        'others'               => 'Others',
    );

    $CI =& get_instance();
    if (!isset($CI->db) || !$CI->db || !$CI->db->table_exists('nb_property_types')) {
        return $fallback;
    }

    $CI->db->select('slug, name');
    $CI->db->from('nb_property_types');
    $CI->db->where('is_active', 1);
    $CI->db->order_by('sort_order', 'ASC');
    $CI->db->order_by('name', 'ASC');
    $rows = $CI->db->get()->result();
    if (empty($rows)) {
        return $fallback;
    }

    $out = array();
    foreach ($rows as $r) {
        $slug = isset($r->slug) ? trim((string) $r->slug) : '';
        $name = isset($r->name) ? trim((string) $r->name) : '';
        if ($slug === '' || $name === '') {
            continue;
        }
        $out[$slug] = $name;
    }
    return !empty($out) ? $out : $fallback;
}

/** Persist API token in an HttpOnly cookie so panel form POSTs can restore the PHP session. */
function nb_set_api_token_cookie($token, $expire = 7200)
{
    if (!is_string($token) || $token === '') {
        return;
    }
    $CI =& get_instance();
    $secure = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off');
    $CI->input->set_cookie(array(
        'name'     => 'nb_token',
        'value'    => $token,
        'expire'   => (int) $expire,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => TRUE,
    ));
}

/** Clear API token cookie on logout. */
function nb_clear_api_token_cookie()
{
    $CI =& get_instance();
    $secure = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off');
    $CI->input->set_cookie(array(
        'name'     => 'nb_token',
        'value'    => '',
        'expire'   => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => TRUE,
    ));
}

/**
 * URL path prefix when the app lives in a subfolder (e.g. /cp on Hostinger).
 * Empty on Next.js dev (localhost:3000).
 */
function nb_app_base_path()
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
    if ($host !== '' && preg_match('/^(localhost|127\.0\.0\.1):300[01]$/', $host)) {
        $cached = '';
        return $cached;
    }
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $dir = str_replace('\\', '/', dirname((string) $_SERVER['SCRIPT_NAME']));
        if (preg_match('#(/cp)/?$#', $dir, $m)) {
            $cached = $m[1];
            return $cached;
        }
    }
    $cached = '';
    return $cached;
}

/**
 * Redirect using a root-relative path so the browser stays on localhost:3000
 * (avoids .env BASE_URL=http://localhost:8080/cp breaking panel saves).
 * On production subfolder installs, prefixes /cp so logout/home links stay in the app.
 */
function nb_redirect_path($path, $code = 303)
{
    $path = '/' . ltrim((string) $path, '/');
    $base = nb_app_base_path();
    if ($base !== '' && $path !== $base && $path !== $base . '/' && strpos($path, $base . '/') !== 0) {
        $path = $base . $path;
    }
    $CI =& get_instance();
    $CI->output->set_status_header((int) $code);
    header('Location: ' . $path, true, (int) $code);
    exit;
}
