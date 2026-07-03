<?php

function ddys_open_parse_labels($content)
{
    if (strpos($content, '{ddys:') !== false) {
        $content = preg_replace_callback('/\{ddys:([a-z_]+)(\s+[^}]*)?\}/i', 'ddys_open_label_callback', $content);
    }
    return ddys_open_parse_shortcodes($content);
}

function ddys_open_label_callback($matches)
{
    $name = strtolower($matches[1]);
    if ($name === 'requestform') {
        $name = 'request_form';
    }
    $tag = 'ddys_' . $name;
    $atts = ddys_open_parse_attributes(isset($matches[2]) ? $matches[2] : '');
    return ddys_open_render_shortcode($tag, $atts);
}

function ddys_open_parse_shortcodes($content)
{
    if (strpos($content, '[ddys_') === false) {
        return $content;
    }
    return preg_replace_callback('/\[(ddys_[a-z_]+)([^\]]*)\]/i', 'ddys_open_shortcode_callback', $content);
}

function ddys_open_shortcode_callback($matches)
{
    $tag = strtolower($matches[1]);
    $atts = ddys_open_parse_attributes(isset($matches[2]) ? $matches[2] : '');
    return ddys_open_render_shortcode($tag, $atts);
}

function ddys_open_parse_attributes($text)
{
    $atts = array();
    if (preg_match_all('/([a-zA-Z0-9_\\-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\\s\\]}]+))/', $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $value = '';
            if (isset($match[2]) && $match[2] !== '') {
                $value = $match[2];
            } elseif (isset($match[3]) && $match[3] !== '') {
                $value = $match[3];
            } elseif (isset($match[4])) {
                $value = $match[4];
            }
            $atts[str_replace('-', '_', strtolower($match[1]))] = ddys_open_request_scalar($value);
        }
    }
    return $atts;
}

function ddys_open_render_shortcode($tag, $atts)
{
    $tag = strtolower($tag);
    $atts = array_merge(array(
        'type' => '',
        'genre' => '',
        'region' => '',
        'year' => '',
        'sort' => '',
        'page' => '',
        'per_page' => '',
        'limit' => '',
        'q' => '',
        'month' => '',
        'slug' => '',
        'id' => '',
        'username' => '',
        'layout' => '',
        'target' => '',
    ), $atts);

    if ($tag === 'ddys_movies') {
        return ddys_open_render_list(ddys_open_api_get('/movies', ddys_open_build_query($atts, array('type', 'genre', 'region', 'year', 'sort', 'page', 'per_page')), array()), $atts);
    }
    if ($tag === 'ddys_latest') {
        return ddys_open_render_list(ddys_open_api_get('/latest', ddys_open_build_query($atts, array('type', 'limit')), array()), $atts);
    }
    if ($tag === 'ddys_hot') {
        return ddys_open_render_list(ddys_open_api_get('/hot', ddys_open_build_query($atts, array('type', 'genre', 'region', 'limit')), array()), $atts);
    }
    if ($tag === 'ddys_search') {
        return ddys_open_render_search($atts);
    }
    if ($tag === 'ddys_suggest') {
        return ddys_open_render_list(ddys_open_api_get('/suggest', ddys_open_build_query($atts, array('q')), array()), $atts);
    }
    if ($tag === 'ddys_calendar') {
        return ddys_open_render_calendar(ddys_open_api_get('/calendar', ddys_open_build_query($atts, array('year', 'month')), array()), $atts);
    }
    if ($tag === 'ddys_movie') {
        if ($atts['slug'] === '') {
            return ddys_open_render_error(ddys_open_error('ddys_movie 缺少 slug。'), $atts);
        }
        return ddys_open_render_detail(ddys_open_api_get('/movies/' . rawurlencode($atts['slug']), array(), array()), $atts);
    }
    if ($tag === 'ddys_sources') {
        if ($atts['slug'] === '') {
            return ddys_open_render_error(ddys_open_error('ddys_sources 缺少 slug。'), $atts);
        }
        return ddys_open_render_sources(ddys_open_api_get('/movies/' . rawurlencode($atts['slug']) . '/sources', array(), array()), $atts);
    }
    if ($tag === 'ddys_related') {
        if ($atts['slug'] === '') {
            return ddys_open_render_error(ddys_open_error('ddys_related 缺少 slug。'), $atts);
        }
        return ddys_open_render_list(ddys_open_api_get('/movies/' . rawurlencode($atts['slug']) . '/related', array(), array()), $atts);
    }
    if ($tag === 'ddys_comments') {
        if ($atts['slug'] === '') {
            return ddys_open_render_error(ddys_open_error('ddys_comments 缺少 slug。'), $atts);
        }
        return ddys_open_render_list(ddys_open_api_get('/movies/' . rawurlencode($atts['slug']) . '/comments', ddys_open_build_query($atts, array('page', 'per_page')), array()), $atts);
    }
    if ($tag === 'ddys_collections') {
        return ddys_open_render_list(ddys_open_api_get('/collections', ddys_open_build_query($atts, array('page', 'per_page')), array()), $atts);
    }
    if ($tag === 'ddys_collection') {
        if ($atts['slug'] === '') {
            return ddys_open_render_error(ddys_open_error('ddys_collection 缺少 slug。'), $atts);
        }
        return ddys_open_render_detail(ddys_open_api_get('/collections/' . rawurlencode($atts['slug']), ddys_open_build_query($atts, array('page', 'per_page')), array()), $atts);
    }
    if ($tag === 'ddys_shares') {
        return ddys_open_render_list(ddys_open_api_get('/shares', ddys_open_build_query($atts, array('page', 'per_page')), array()), $atts);
    }
    if ($tag === 'ddys_share') {
        if ($atts['id'] === '') {
            return ddys_open_render_error(ddys_open_error('ddys_share 缺少 id。'), $atts);
        }
        return ddys_open_render_detail(ddys_open_api_get('/shares/' . intval($atts['id']), array(), array()), $atts);
    }
    if ($tag === 'ddys_requests') {
        return ddys_open_render_list(ddys_open_api_get('/requests', ddys_open_build_query($atts, array('page', 'per_page')), array()), $atts);
    }
    if ($tag === 'ddys_activities') {
        return ddys_open_render_list(ddys_open_api_get('/activities', ddys_open_build_query($atts, array('type', 'page', 'per_page')), array()), $atts);
    }
    if ($tag === 'ddys_user') {
        if ($atts['username'] === '') {
            return ddys_open_render_error(ddys_open_error('ddys_user 缺少 username。'), $atts);
        }
        return ddys_open_render_detail(ddys_open_api_get('/user/' . rawurlencode($atts['username']), array(), array()), $atts);
    }
    if ($tag === 'ddys_types' || $tag === 'ddys_genres' || $tag === 'ddys_regions') {
        $path = '/' . str_replace('ddys_', '', $tag);
        return ddys_open_render_dictionary(ddys_open_api_get($path, array(), array()), $atts);
    }
    if ($tag === 'ddys_request_form') {
        return ddys_open_render_request_form($atts);
    }
    return '';
}

function ddys_open_render($tag, $atts = array())
{
    $tag = strpos($tag, 'ddys_') === 0 ? $tag : 'ddys_' . $tag;
    return ddys_open_render_shortcode($tag, is_array($atts) ? $atts : array());
}

