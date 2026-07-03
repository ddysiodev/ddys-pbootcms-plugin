<?php

if (!defined('DDYS_OPEN_ID')) {
    define('DDYS_OPEN_ID', 'ddys_open');
}
if (!defined('DDYS_OPEN_VERSION')) {
    define('DDYS_OPEN_VERSION', '0.1.0');
}
if (!defined('DDYS_OPEN_API_DEFAULT')) {
    define('DDYS_OPEN_API_DEFAULT', 'https://ddys.io/api/v1');
}
if (!defined('DDYS_OPEN_SITE_DEFAULT')) {
    define('DDYS_OPEN_SITE_DEFAULT', 'https://ddys.io');
}
if (!defined('DDYS_OPEN_ROOT')) {
    define('DDYS_OPEN_ROOT', defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__)));
}
if (!defined('DDYS_OPEN_APP_DIR')) {
    define('DDYS_OPEN_APP_DIR', __DIR__);
}
if (!defined('DDYS_OPEN_DATA_DIR')) {
    define('DDYS_OPEN_DATA_DIR', DDYS_OPEN_ROOT . '/data/ddys_open');
}
if (!defined('DDYS_OPEN_STATIC_DIR')) {
    define('DDYS_OPEN_STATIC_DIR', DDYS_OPEN_ROOT . '/static/ddys_open');
}

function ddys_open_bootstrap()
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    require_once DDYS_OPEN_APP_DIR . '/security.php';
    require_once DDYS_OPEN_APP_DIR . '/storage.php';
    require_once DDYS_OPEN_APP_DIR . '/client.php';
    require_once DDYS_OPEN_APP_DIR . '/render.php';
    require_once DDYS_OPEN_APP_DIR . '/labels.php';
}

function ddys_open_root_path($path = '')
{
    return DDYS_OPEN_ROOT . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function ddys_open_app_path($path = '')
{
    return DDYS_OPEN_APP_DIR . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

