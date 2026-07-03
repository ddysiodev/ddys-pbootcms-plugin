<?php

function ddys_open_storage_dir()
{
    return DDYS_OPEN_DATA_DIR;
}

function ddys_open_cache_dir()
{
    return DDYS_OPEN_DATA_DIR . '/cache';
}

function ddys_open_config_file()
{
    return DDYS_OPEN_DATA_DIR . '/config.php';
}

function ddys_open_storage_ensure()
{
    foreach (array(ddys_open_storage_dir(), ddys_open_cache_dir()) as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    ddys_open_write_protection_files();
    return is_dir(ddys_open_storage_dir()) && is_dir(ddys_open_cache_dir());
}

function ddys_open_write_protection_files()
{
    foreach (array(ddys_open_storage_dir(), ddys_open_cache_dir()) as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        if (!is_file($dir . '/index.html')) {
            @file_put_contents($dir . '/index.html', '');
        }
        if (!is_file($dir . '/.htaccess')) {
            @file_put_contents($dir . '/.htaccess', "Require all denied\nDeny from all\n");
        }
        if (!is_file($dir . '/web.config')) {
            @file_put_contents($dir . '/web.config', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><security><requestFiltering><hiddenSegments><add segment=\"ddys_open\" /></hiddenSegments></requestFiltering></security></system.webServer></configuration>\n");
        }
    }
}

function ddys_open_storage_config()
{
    $file = ddys_open_config_file();
    if (!is_file($file)) {
        return array();
    }
    $config = include $file;
    return is_array($config) ? $config : array();
}

function ddys_open_storage_save_config($config)
{
    ddys_open_storage_ensure();
    $file = ddys_open_config_file();
    $content = "<?php\nif (!defined('ROOT_PATH')) {\n    exit();\n}\n\nreturn " . var_export($config, true) . ";\n";
    $tmp = $file . '.tmp';
    if (@file_put_contents($tmp, $content, LOCK_EX) === false) {
        return false;
    }
    if (!@rename($tmp, $file)) {
        if (is_file($file)) {
            @unlink($file);
        }
        if (!@rename($tmp, $file)) {
            @unlink($tmp);
            return false;
        }
    }
    return true;
}

function ddys_open_cache_key($method, $base, $path, $params)
{
    return md5(strtoupper($method) . '|' . $base . '|' . $path . '|' . serialize($params));
}

function ddys_open_cache_file($key)
{
    $safe = preg_replace('/[^a-f0-9]/', '', strtolower($key));
    return ddys_open_cache_dir() . '/' . $safe . '.php';
}

function ddys_open_cache_payload_file($file)
{
    return preg_match('/^[a-f0-9]{32}\.php$/', basename($file));
}

function ddys_open_cache_rate_file($file)
{
    return preg_match('/^rate-[a-f0-9]{32}\.php$/', basename($file));
}

function ddys_open_cache_get($key)
{
    $file = ddys_open_cache_file($key);
    if (!is_file($file)) {
        return false;
    }
    $payload = include $file;
    if (!is_array($payload) || !isset($payload['expire_at']) || time() >= (int)$payload['expire_at']) {
        @unlink($file);
        return false;
    }
    return array_key_exists('value', $payload) ? $payload['value'] : false;
}

function ddys_open_cache_set($key, $value, $ttl)
{
    if ($ttl <= 0) {
        return;
    }
    ddys_open_storage_ensure();
    ddys_open_cache_prune();
    $payload = array(
        'expire_at' => time() + (int)$ttl,
        'updated_at' => time(),
        'value' => $value,
    );
    $content = "<?php\nif (!defined('ROOT_PATH')) {\n    exit();\n}\n\nreturn " . var_export($payload, true) . ";\n";
    @file_put_contents(ddys_open_cache_file($key), $content, LOCK_EX);
}

function ddys_open_cache_prune()
{
    $dir = ddys_open_cache_dir();
    if (!is_dir($dir)) {
        return;
    }
    foreach (glob($dir . '/*.php') as $file) {
        if (!ddys_open_cache_payload_file($file)) {
            continue;
        }
        $payload = include $file;
        if (!is_array($payload) || !isset($payload['expire_at']) || time() >= (int)$payload['expire_at']) {
            @unlink($file);
        }
    }
    foreach (glob($dir . '/rate-*.php') as $file) {
        if (!ddys_open_cache_rate_file($file)) {
            continue;
        }
        $payload = include $file;
        if (!is_array($payload) || empty($payload['touched_at']) || time() - (int)$payload['touched_at'] > 86400) {
            @unlink($file);
        }
    }
}

function ddys_open_cache_flush()
{
    $count = 0;
    $dir = ddys_open_cache_dir();
    if (!is_dir($dir)) {
        return 0;
    }
    foreach (glob($dir . '/*.php') as $file) {
        if (!ddys_open_cache_payload_file($file) && !ddys_open_cache_rate_file($file)) {
            continue;
        }
        if (@unlink($file)) {
            $count++;
        }
    }
    return $count;
}

function ddys_open_cache_count()
{
    $dir = ddys_open_cache_dir();
    if (!is_dir($dir)) {
        return 0;
    }
    $files = glob($dir . '/*.php');
    if (!is_array($files)) {
        return 0;
    }
    $count = 0;
    foreach ($files as $file) {
        if (ddys_open_cache_payload_file($file)) {
            $count++;
        }
    }
    return $count;
}

function ddys_open_check_rate_limit($scope, $key, $interval)
{
    if ($interval <= 0) {
        return true;
    }
    ddys_open_storage_ensure();
    ddys_open_cache_prune();
    $hash = md5($scope . '|' . $key);
    $file = ddys_open_cache_dir() . '/rate-' . $hash . '.php';
    if (is_file($file)) {
        $payload = include $file;
        if (is_array($payload) && isset($payload['touched_at']) && time() - (int)$payload['touched_at'] < (int)$interval) {
            return false;
        }
    }
    $payload = array('scope' => $scope, 'touched_at' => time());
    $content = "<?php\nif (!defined('ROOT_PATH')) {\n    exit();\n}\n\nreturn " . var_export($payload, true) . ";\n";
    @file_put_contents($file, $content, LOCK_EX);
    return true;
}

function ddys_open_storage_status()
{
    ddys_open_storage_ensure();
    return array(
        'data_dir' => ddys_open_storage_dir(),
        'cache_dir' => ddys_open_cache_dir(),
        'data_writable' => is_writable(ddys_open_storage_dir()),
        'cache_writable' => is_writable(ddys_open_cache_dir()),
        'config_exists' => is_file(ddys_open_config_file()),
    );
}
