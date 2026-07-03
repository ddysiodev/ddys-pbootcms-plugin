<?php

function ddys_open_api_get($path, $params = array(), $options = array())
{
    return ddys_open_api_request('GET', $path, $params, null, $options);
}

function ddys_open_api_post($path, $body = array(), $options = array())
{
    return ddys_open_api_request('POST', $path, array(), $body, $options);
}

function ddys_open_api_request($method, $path, $params, $body, $options)
{
    $settings = ddys_open_settings();
    $method = strtoupper($method);
    $path = '/' . ltrim((string)$path, '/');
    $base = rtrim($settings['api_base_url'], '/');
    $url = $base . $path;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params, '', '&');
    }
    $ttl = isset($options['cache_ttl']) ? ddys_open_int_range($options['cache_ttl'], 0, 0, 604800) : ddys_open_ttl_for_path($path, $settings);
    $useCache = $method === 'GET' && empty($options['no_cache']);
    $cacheKey = ddys_open_cache_key($method, $base, $path, $params);
    if ($useCache) {
        $cached = ddys_open_cache_get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
    }

    $headers = array(
        'Accept: application/json',
        'User-Agent: ddys-pbootcms-plugin/' . DDYS_OPEN_VERSION,
    );
    if (!empty($options['auth'])) {
        if ($settings['api_key'] === '') {
            return ddys_open_error('低端影视 API Key 尚未配置。', 403);
        }
        $headers[] = 'Authorization: Bearer ' . $settings['api_key'];
    }
    $raw = ddys_open_http_request($method, $url, $body, $headers, $settings['timeout']);
    if (ddys_open_is_error($raw)) {
        return $raw;
    }
    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return ddys_open_error('低端影视 API 返回了无效 JSON。', 0, array('raw' => $raw));
    }
    if (!ddys_open_success_response($json)) {
        $message = isset($json['message']) ? $json['message'] : '低端影视 API 请求失败。';
        return ddys_open_error($message, 0, $json);
    }
    if ($useCache && $ttl > 0) {
        ddys_open_cache_set($cacheKey, $json, $ttl);
    }
    return $json;
}

function ddys_open_http_request($method, $url, $body, $headers, $timeout)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false) {
            return ddys_open_error('低端影视 API 网络请求失败：' . $err, $status);
        }
        if ($status >= 400) {
            return ddys_open_error('低端影视 API HTTP ' . $status . '。', $status, array('raw' => $raw));
        }
        return $raw;
    }

    $opts = array(
        'http' => array(
            'method' => $method,
            'timeout' => (int)$timeout,
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
        ),
    );
    if ($body !== null) {
        $opts['http']['header'] .= "\r\nContent-Type: application/json";
        $opts['http']['content'] = json_encode($body);
    }
    $raw = @file_get_contents($url, false, stream_context_create($opts));
    if ($raw === false) {
        return ddys_open_error('当前 PHP 环境无法请求低端影视 API。', 0);
    }
    $status = ddys_open_stream_status_code(isset($http_response_header) ? $http_response_header : array());
    if ($status >= 400) {
        return ddys_open_error('低端影视 API HTTP ' . $status . '。', $status, array('raw' => $raw));
    }
    return $raw;
}

function ddys_open_stream_status_code($headers)
{
    if (!is_array($headers) || empty($headers[0])) {
        return 0;
    }
    return preg_match('#\s([0-9]{3})\s#', $headers[0], $matches) ? (int)$matches[1] : 0;
}

function ddys_open_success_response($json)
{
    return !(isset($json['success']) && $json['success'] === false);
}

function ddys_open_ttl_for_path($path, $settings)
{
    if (preg_match('#^/(types|genres|regions|calendar)$#', $path)) {
        return (int)$settings['dictionary_cache_ttl'];
    }
    if (preg_match('#^/(latest|hot)$#', $path)) {
        return (int)$settings['fresh_cache_ttl'];
    }
    if (preg_match('#^/(movies/[^/]+|movies/[^/]+/sources|movies/[^/]+/related|collections/[^/]+|shares/[0-9]+)$#', $path)) {
        return (int)$settings['detail_cache_ttl'];
    }
    if (preg_match('#^/(movies/[^/]+/comments|suggest|shares|requests|activities|user/)#', $path)) {
        return (int)$settings['community_cache_ttl'];
    }
    if (preg_match('#^/(movies|search|collections)#', $path)) {
        return (int)$settings['list_cache_ttl'];
    }
    return (int)$settings['default_cache_ttl'];
}

function ddys_open_allowed_route($route)
{
    return in_array($route, array('movies', 'latest', 'hot', 'search', 'suggest', 'calendar', 'movie', 'sources', 'related', 'comments', 'collections', 'collection', 'shares', 'share', 'requests', 'activities', 'user', 'types', 'genres', 'regions'), true);
}

function ddys_open_proxy_path($route)
{
    $slug = ddys_open_get('slug');
    $id = ddys_open_get('id');
    $username = ddys_open_get('username');
    switch ($route) {
        case 'movies': return '/movies';
        case 'latest': return '/latest';
        case 'hot': return '/hot';
        case 'search': return '/search';
        case 'suggest': return '/suggest';
        case 'calendar': return '/calendar';
        case 'movie': return $slug === '' ? '' : '/movies/' . rawurlencode($slug);
        case 'sources': return $slug === '' ? '' : '/movies/' . rawurlencode($slug) . '/sources';
        case 'related': return $slug === '' ? '' : '/movies/' . rawurlencode($slug) . '/related';
        case 'comments': return $slug === '' ? '' : '/movies/' . rawurlencode($slug) . '/comments';
        case 'collections': return '/collections';
        case 'collection': return $slug === '' ? '' : '/collections/' . rawurlencode($slug);
        case 'shares': return '/shares';
        case 'share': return $id === '' ? '' : '/shares/' . intval($id);
        case 'requests': return '/requests';
        case 'activities': return '/activities';
        case 'user': return $username === '' ? '' : '/user/' . rawurlencode($username);
        case 'types': return '/types';
        case 'genres': return '/genres';
        case 'regions': return '/regions';
    }
    return '';
}

function ddys_open_proxy_query()
{
    $keys = array('type', 'genre', 'region', 'year', 'sort', 'page', 'per_page', 'limit', 'q', 'month');
    $query = array();
    foreach ($keys as $key) {
        $value = ddys_open_get($key);
        if ($value !== '') {
            $query[$key] = ddys_open_normalize_query_value($key, $value);
        }
    }
    return $query;
}

function ddys_open_proxy_response()
{
    $route = strtolower(ddys_open_get('route', 'latest'));
    if (!ddys_open_allowed_route($route)) {
        return ddys_open_error('Route not allowed.', 403);
    }
    $path = ddys_open_proxy_path($route);
    if ($path === '') {
        return ddys_open_error('Invalid route parameters.', 400);
    }
    return ddys_open_api_get($path, ddys_open_proxy_query(), array());
}

function ddys_open_handle_request_form()
{
    $settings = ddys_open_settings();
    if (empty($settings['enable_request_form'])) {
        return ddys_open_error('求片表单未启用。', 403);
    }
    if ($settings['api_key'] === '') {
        return ddys_open_error('低端影视 API Key 尚未配置。', 403);
    }
    if (!ddys_open_check_nonce(ddys_open_post('ddys_nonce'), 'request')) {
        return ddys_open_error('表单校验失败，请刷新页面后重试。', 403);
    }
    $ip = ddys_open_current_ip();
    if (!ddys_open_check_rate_limit('request', $ip, (int)$settings['request_interval'])) {
        return ddys_open_error('提交过于频繁，请稍后再试。', 429);
    }
    $title = ddys_open_post('title');
    if ($title === '') {
        return ddys_open_error('请填写片名。', 400);
    }
    $title = ddys_open_substr($title, 0, 255);
    $year = ddys_open_post('year');
    $year = $year === '' ? '' : ddys_open_int_range($year, 0, 1900, 2099);
    $type = ddys_open_choice(ddys_open_post('type'), array('movie', 'series', 'variety', 'anime'), '');
    $body = array(
        'title' => $title,
        'year' => $year,
        'type' => $type,
        'description' => ddys_open_substr(ddys_open_post('description'), 0, 1000),
        'douban_id' => ddys_open_substr(ddys_open_post('douban_id'), 0, 30),
    );
    return ddys_open_api_post('/requests', $body, array('auth' => true, 'no_cache' => true));
}
