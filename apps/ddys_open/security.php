<?php

function ddys_open_defaults()
{
    return array(
        'api_base_url' => DDYS_OPEN_API_DEFAULT,
        'site_base_url' => DDYS_OPEN_SITE_DEFAULT,
        'api_key' => '',
        'timeout' => 12,
        'default_cache_ttl' => 300,
        'dictionary_cache_ttl' => 86400,
        'fresh_cache_ttl' => 300,
        'list_cache_ttl' => 600,
        'detail_cache_ttl' => 1800,
        'community_cache_ttl' => 120,
        'theme' => 'auto',
        'layout' => 'grid',
        'columns' => 4,
        'target' => '_blank',
        'show_source_link' => 1,
        'enable_styles' => 1,
        'enable_request_form' => 0,
        'request_interval' => 60,
        'show_nav' => 1,
        'enable_pretty_urls' => 0,
        'pretty_base_path' => 'ddys',
        'nonce_salt' => '',
        'debug' => 0,
    );
}

function ddys_open_settings()
{
    $settings = array_merge(ddys_open_defaults(), ddys_open_storage_config());
    $settings['api_base_url'] = ddys_open_normalize_base_url($settings['api_base_url'], DDYS_OPEN_API_DEFAULT);
    $settings['site_base_url'] = ddys_open_normalize_base_url($settings['site_base_url'], DDYS_OPEN_SITE_DEFAULT);
    $settings['timeout'] = ddys_open_int_range($settings['timeout'], 12, 1, 30);
    $settings['default_cache_ttl'] = ddys_open_int_range($settings['default_cache_ttl'], 300, 0, 604800);
    $settings['dictionary_cache_ttl'] = ddys_open_int_range($settings['dictionary_cache_ttl'], 86400, 0, 604800);
    $settings['fresh_cache_ttl'] = ddys_open_int_range($settings['fresh_cache_ttl'], 300, 0, 604800);
    $settings['list_cache_ttl'] = ddys_open_int_range($settings['list_cache_ttl'], 600, 0, 604800);
    $settings['detail_cache_ttl'] = ddys_open_int_range($settings['detail_cache_ttl'], 1800, 0, 604800);
    $settings['community_cache_ttl'] = ddys_open_int_range($settings['community_cache_ttl'], 120, 0, 604800);
    $settings['columns'] = ddys_open_int_range($settings['columns'], 4, 1, 6);
    $settings['request_interval'] = ddys_open_int_range($settings['request_interval'], 60, 10, 3600);
    $settings['theme'] = ddys_open_choice($settings['theme'], array('auto', 'light', 'dark'), 'auto');
    $settings['layout'] = ddys_open_choice($settings['layout'], array('grid', 'list', 'compact'), 'grid');
    $settings['target'] = ddys_open_choice($settings['target'], array('_blank', '_self'), '_blank');
    foreach (array('show_source_link', 'enable_styles', 'enable_request_form', 'show_nav', 'enable_pretty_urls', 'debug') as $key) {
        $settings[$key] = ddys_open_bool($settings[$key]) ? 1 : 0;
    }
    $settings['pretty_base_path'] = ddys_open_normalize_base_path($settings['pretty_base_path'], 'ddys');
    $settings['api_key'] = trim((string)$settings['api_key']);
    $settings['nonce_salt'] = trim((string)$settings['nonce_salt']);
    return $settings;
}

function ddys_open_save_settings_from_post()
{
    $current = ddys_open_settings();
    $next = array_merge($current, array(
        'api_base_url' => ddys_open_post('api_base_url', DDYS_OPEN_API_DEFAULT),
        'site_base_url' => ddys_open_post('site_base_url', DDYS_OPEN_SITE_DEFAULT),
        'api_key' => ddys_open_post('api_key', ''),
        'timeout' => ddys_open_post('timeout', 12),
        'default_cache_ttl' => ddys_open_post('default_cache_ttl', 300),
        'dictionary_cache_ttl' => ddys_open_post('dictionary_cache_ttl', 86400),
        'fresh_cache_ttl' => ddys_open_post('fresh_cache_ttl', 300),
        'list_cache_ttl' => ddys_open_post('list_cache_ttl', 600),
        'detail_cache_ttl' => ddys_open_post('detail_cache_ttl', 1800),
        'community_cache_ttl' => ddys_open_post('community_cache_ttl', 120),
        'theme' => ddys_open_post('theme', 'auto'),
        'layout' => ddys_open_post('layout', 'grid'),
        'columns' => ddys_open_post('columns', 4),
        'target' => ddys_open_post('target', '_blank'),
        'show_source_link' => ddys_open_post('show_source_link', '0'),
        'enable_styles' => ddys_open_post('enable_styles', '0'),
        'enable_request_form' => ddys_open_post('enable_request_form', '0'),
        'request_interval' => ddys_open_post('request_interval', 60),
        'show_nav' => ddys_open_post('show_nav', '0'),
        'enable_pretty_urls' => ddys_open_post('enable_pretty_urls', '0'),
        'pretty_base_path' => ddys_open_post('pretty_base_path', 'ddys'),
        'debug' => ddys_open_post('debug', '0'),
    ));
    if ($next['nonce_salt'] === '') {
        $next['nonce_salt'] = ddys_open_random_token();
    }
    return ddys_open_storage_save_config(array_intersect_key(ddys_open_normalize_settings($next), ddys_open_defaults()));
}

function ddys_open_normalize_settings($settings)
{
    $merged = array_merge(ddys_open_defaults(), $settings);
    $merged['api_base_url'] = ddys_open_normalize_base_url($merged['api_base_url'], DDYS_OPEN_API_DEFAULT);
    $merged['site_base_url'] = ddys_open_normalize_base_url($merged['site_base_url'], DDYS_OPEN_SITE_DEFAULT);
    $merged['timeout'] = ddys_open_int_range($merged['timeout'], 12, 1, 30);
    $merged['default_cache_ttl'] = ddys_open_int_range($merged['default_cache_ttl'], 300, 0, 604800);
    $merged['dictionary_cache_ttl'] = ddys_open_int_range($merged['dictionary_cache_ttl'], 86400, 0, 604800);
    $merged['fresh_cache_ttl'] = ddys_open_int_range($merged['fresh_cache_ttl'], 300, 0, 604800);
    $merged['list_cache_ttl'] = ddys_open_int_range($merged['list_cache_ttl'], 600, 0, 604800);
    $merged['detail_cache_ttl'] = ddys_open_int_range($merged['detail_cache_ttl'], 1800, 0, 604800);
    $merged['community_cache_ttl'] = ddys_open_int_range($merged['community_cache_ttl'], 120, 0, 604800);
    $merged['columns'] = ddys_open_int_range($merged['columns'], 4, 1, 6);
    $merged['request_interval'] = ddys_open_int_range($merged['request_interval'], 60, 10, 3600);
    $merged['theme'] = ddys_open_choice($merged['theme'], array('auto', 'light', 'dark'), 'auto');
    $merged['layout'] = ddys_open_choice($merged['layout'], array('grid', 'list', 'compact'), 'grid');
    $merged['target'] = ddys_open_choice($merged['target'], array('_blank', '_self'), '_blank');
    $merged['pretty_base_path'] = ddys_open_normalize_base_path($merged['pretty_base_path'], 'ddys');
    foreach (array('show_source_link', 'enable_styles', 'enable_request_form', 'show_nav', 'enable_pretty_urls', 'debug') as $key) {
        $merged[$key] = ddys_open_bool($merged[$key]) ? 1 : 0;
    }
    return $merged;
}

function ddys_open_get($key, $default = '')
{
    if (function_exists('get')) {
        $value = get($key);
        if ($value !== null && $value !== '') {
            return ddys_open_request_scalar($value, $default);
        }
    }
    return isset($_GET[$key]) ? ddys_open_request_scalar($_GET[$key], $default) : $default;
}

function ddys_open_post($key, $default = '')
{
    if (function_exists('post')) {
        $value = post($key);
        if ($value !== null && $value !== '') {
            return ddys_open_request_scalar($value, $default);
        }
    }
    return isset($_POST[$key]) ? ddys_open_request_scalar($_POST[$key], $default) : $default;
}

function ddys_open_request_scalar($value, $default = '')
{
    if (is_array($value) || is_object($value)) {
        return $default;
    }
    return trim(str_replace("\0", '', (string)$value));
}

function ddys_open_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ddys_open_attr($value)
{
    return ddys_open_h($value);
}

function ddys_open_substr($value, $start, $length)
{
    $value = (string)$value;
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length, 'UTF-8');
    }
    return substr($value, $start, $length);
}

function ddys_open_bool($value)
{
    if (is_bool($value)) {
        return $value;
    }
    $value = strtolower(trim((string)$value));
    return in_array($value, array('1', 'true', 'yes', 'on'), true);
}

function ddys_open_int_range($value, $fallback, $min, $max)
{
    if (is_numeric($value)) {
        $value = (int)$value;
        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }
        return $value;
    }
    return $fallback;
}

function ddys_open_choice($value, $allowed, $fallback)
{
    $value = strtolower(trim((string)$value));
    return in_array($value, $allowed, true) ? $value : $fallback;
}

function ddys_open_normalize_base_url($value, $fallback)
{
    $value = trim((string)$value);
    if ($value === '' || !preg_match('#^https?://#i', $value)) {
        return $fallback;
    }
    $parts = parse_url($value);
    if (empty($parts['scheme']) || empty($parts['host']) || !empty($parts['user']) || !empty($parts['pass'])) {
        return $fallback;
    }
    return rtrim($value, '/');
}

function ddys_open_safe_media_url($value)
{
    $value = trim((string)$value);
    if ($value === '' || !preg_match('#^https?://#i', $value)) {
        return '';
    }
    return $value;
}

function ddys_open_normalize_base_path($value, $fallback)
{
    $value = trim((string)$value);
    $value = trim($value, "/ \t\r\n");
    if ($value === '' || !preg_match('#^[a-zA-Z0-9_\-/]+$#', $value) || strpos($value, '..') !== false) {
        return $fallback;
    }
    return $value;
}

function ddys_open_site_root()
{
    if (defined('SITE_DIR')) {
        $root = trim((string)SITE_DIR);
        return rtrim($root === '' ? '/' : $root, '/') . '/';
    }
    return '/';
}

function ddys_open_static_url($path = '')
{
    return ddys_open_site_root() . 'static/ddys_open' . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function ddys_open_pboot_url($path, $query = array())
{
    $qs = http_build_query(ddys_open_clean_query($query), '', '&');
    if (class_exists('core\\basic\\Url')) {
        return \core\basic\Url::home($path, false, $qs);
    }
    $url = ddys_open_site_root() . 'index.php/' . trim($path, '/');
    return $qs === '' ? $url : $url . '?' . $qs;
}

function ddys_open_endpoint_url($endpoint)
{
    $settings = ddys_open_settings();
    if (!empty($settings['enable_pretty_urls'])) {
        $base = ddys_open_site_root() . $settings['pretty_base_path'];
        if ($endpoint === 'request') {
            return $base . '/request-submit';
        }
        if ($endpoint === 'api') {
            return $base . '/api';
        }
    }
    return ddys_open_pboot_url('ddys/' . $endpoint);
}

function ddys_open_page_url($view = 'latest', $params = array())
{
    $settings = ddys_open_settings();
    $view = ddys_open_choice($view, array('latest', 'hot', 'search', 'calendar', 'movie', 'collections', 'requests'), 'latest');
    if (!empty($settings['enable_pretty_urls'])) {
        $base = ddys_open_site_root() . $settings['pretty_base_path'];
        if ($view === 'latest') {
            $url = $base . '/';
        } elseif ($view === 'movie') {
            $slug = isset($params['slug']) ? rawurlencode(ddys_open_request_scalar($params['slug'])) : '';
            $url = $slug !== '' ? $base . '/movie/' . $slug : $base . '/';
            unset($params['slug']);
        } else {
            $url = $base . '/' . rawurlencode($view);
        }
        return ddys_open_append_query($url, $params);
    }
    $query = array_merge($view === 'latest' ? array() : array('view' => $view), $params);
    return ddys_open_pboot_url('ddys/index', $query);
}

function ddys_open_clean_query($params)
{
    $out = array();
    foreach ($params as $key => $value) {
        $value = ddys_open_request_scalar($value);
        if ($value !== '') {
            $out[$key] = $value;
        }
    }
    return $out;
}

function ddys_open_append_query($url, $params)
{
    $query = ddys_open_clean_query($params);
    if (empty($query)) {
        return $url;
    }
    return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($query, '', '&');
}

function ddys_open_json_response($payload, $status = 200)
{
    if ($status === 200 && ddys_open_is_error($payload) && !empty($payload['status'])) {
        $status = ddys_open_int_range($payload['status'], 500, 400, 599);
    }
    if (!headers_sent()) {
        if (function_exists('http_response_code')) {
            http_response_code($status);
        }
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode($payload, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
    exit;
}

function ddys_open_error($message, $status = 0, $payload = array())
{
    return array(
        'ddys_error' => true,
        'success' => false,
        'message' => (string)$message,
        'status' => (int)$status,
        'payload' => $payload,
    );
}

function ddys_open_is_error($value)
{
    return is_array($value) && !empty($value['ddys_error']);
}

function ddys_open_build_query($source, $keys)
{
    $out = array();
    foreach ($keys as $key) {
        if (isset($source[$key]) && trim((string)$source[$key]) !== '') {
            $out[$key] = ddys_open_normalize_query_value($key, $source[$key]);
        }
    }
    return $out;
}

function ddys_open_normalize_query_value($key, $value)
{
    $value = ddys_open_request_scalar($value);
    if ($value === '') {
        return '';
    }
    if ($key === 'limit' || $key === 'per_page') {
        return ddys_open_int_range($value, 12, 1, 50);
    }
    if ($key === 'page') {
        return ddys_open_int_range($value, 1, 1, 999);
    }
    if ($key === 'year') {
        return ddys_open_int_range($value, 0, 0, 2099);
    }
    if ($key === 'month') {
        return ddys_open_int_range($value, 0, 0, 12);
    }
    return $value;
}

function ddys_open_random_token()
{
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(24));
    }
    return sha1(uniqid('', true) . mt_rand());
}

function ddys_open_nonce($action = 'default', $bucket = null)
{
    $settings = ddys_open_settings();
    if ($settings['nonce_salt'] === '') {
        $settings['nonce_salt'] = ddys_open_random_token();
        ddys_open_storage_save_config(array_intersect_key($settings, ddys_open_defaults()));
    }
    if ($bucket === null) {
        $bucket = floor(time() / 43200);
    }
    return hash_hmac('sha256', $action . '|' . $bucket, $settings['nonce_salt']);
}

function ddys_open_check_nonce($token, $action = 'default')
{
    $token = ddys_open_request_scalar($token);
    if ($token === '') {
        return false;
    }
    $bucket = floor(time() / 43200);
    return ddys_open_hash_equals(ddys_open_nonce($action, $bucket), $token) || ddys_open_hash_equals(ddys_open_nonce($action, $bucket - 1), $token);
}

function ddys_open_hash_equals($known, $user)
{
    if (function_exists('hash_equals')) {
        return hash_equals($known, $user);
    }
    if (strlen($known) !== strlen($user)) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < strlen($known); $i++) {
        $result |= ord($known[$i]) ^ ord($user[$i]);
    }
    return $result === 0;
}

function ddys_open_current_ip()
{
    if (function_exists('get_user_ip')) {
        return get_user_ip();
    }
    foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key) {
        if (!empty($_SERVER[$key])) {
            $parts = explode(',', (string)$_SERVER[$key]);
            $value = trim($parts[0]);
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }
    }
    return 'unknown';
}

function ddys_open_admin_logged_in()
{
    if (function_exists('session')) {
        return session('sid') ? true : false;
    }
    return !empty($_SESSION['sid']);
}

