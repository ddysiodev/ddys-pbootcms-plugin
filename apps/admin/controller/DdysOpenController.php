<?php

namespace app\admin\controller;

use core\basic\Controller;

class DdysOpenController extends Controller
{
    private function boot()
    {
        require_once dirname(dirname(dirname(__DIR__))) . '/apps/ddys_open/bootstrap.php';
        \ddys_open_bootstrap();
        \ddys_open_storage_ensure();
    }

    public function __construct()
    {
        $this->boot();
        if (!\ddys_open_admin_logged_in()) {
            if (function_exists('location') && function_exists('url')) {
                location(url('/admin/Index/index'));
            }
            echo 'Access denied.';
            exit;
        }
    }

    private function adminUrl($params = array())
    {
        $base = function_exists('url') ? url('/admin/DdysOpen/index', false) : \ddys_open_site_root() . 'index.php/admin/DdysOpen/index';
        return empty($params) ? $base : \ddys_open_append_query($base, $params);
    }

    public function index()
    {
        $op = \ddys_open_get('op', 'home');
        $notice = '';
        $error = '';
        $settings = \ddys_open_settings();

        if (strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') === 'POST') {
            if (!\ddys_open_check_nonce(\ddys_open_post('ddys_nonce'), 'admin')) {
                $error = '表单校验失败，请刷新页面后重试。';
            } elseif (\ddys_open_post('action') === 'save') {
                if (\ddys_open_save_settings_from_post()) {
                    $notice = '配置已保存。';
                    $settings = \ddys_open_settings();
                } else {
                    $error = '配置保存失败，请确认 data/ddys_open 目录可写。';
                }
            }
        }

        if ($op === 'flush' && \ddys_open_check_nonce(\ddys_open_get('ddys_nonce'), 'admin')) {
            $count = \ddys_open_cache_flush();
            $notice = '已清理缓存：' . intval($count) . ' 个文件。';
        }
        if ($op === 'test' && \ddys_open_check_nonce(\ddys_open_get('ddys_nonce'), 'admin')) {
            $payload = \ddys_open_api_get('/types', array(), array('no_cache' => true));
            $notice = \ddys_open_is_error($payload) ? '连接测试失败：' . $payload['message'] : '连接测试成功。';
        }

        $storage = \ddys_open_storage_status();
        $nonce = \ddys_open_nonce('admin');
        $adminUrl = $this->adminUrl();
        $frontUrl = \ddys_open_page_url('latest');
        $apiUrl = \ddys_open_endpoint_url('api') . '?route=latest&limit=3';
        $requestUrl = \ddys_open_endpoint_url('request');

        ?><!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>低端影视 - PbootCMS 插件</title>
  <link rel="stylesheet" type="text/css" href="<?php echo \ddys_open_attr(\ddys_open_static_url('css/admin.css?v=' . DDYS_OPEN_VERSION)); ?>" />
</head>
<body class="ddys-pbootcms-admin-page">
  <div class="ddys-pbootcms-admin">
    <header class="ddys-pbootcms-admin-header">
      <div>
        <h1>低端影视</h1>
        <p>PbootCMS 插件设置、诊断和标签生成器。</p>
      </div>
      <img src="<?php echo \ddys_open_attr(\ddys_open_static_url('images/logo.png')); ?>" alt="" width="32" height="32" />
    </header>

    <?php if ($notice !== '') { ?><div class="ddys-pbootcms-notice"><?php echo \ddys_open_h($notice); ?></div><?php } ?>
    <?php if ($error !== '') { ?><div class="ddys-pbootcms-error"><?php echo \ddys_open_h($error); ?></div><?php } ?>

    <section class="ddys-pbootcms-panel">
      <h2>诊断</h2>
      <div class="ddys-pbootcms-actions">
        <a class="button" href="<?php echo \ddys_open_attr($this->adminUrl(array('op' => 'test', 'ddys_nonce' => $nonce))); ?>">测试低端影视 API</a>
        <a class="button" href="<?php echo \ddys_open_attr($this->adminUrl(array('op' => 'flush', 'ddys_nonce' => $nonce))); ?>">清理缓存</a>
        <a class="button" href="<?php echo \ddys_open_attr($frontUrl); ?>" target="_blank" rel="noopener">打开前台页</a>
      </div>
      <table>
        <tr><th>项目</th><th>当前值</th></tr>
        <tr><td>插件版本</td><td><?php echo \ddys_open_h(DDYS_OPEN_VERSION); ?></td></tr>
        <tr><td>API Base URL</td><td><?php echo \ddys_open_h($settings['api_base_url']); ?></td></tr>
        <tr><td>缓存文件</td><td><?php echo intval(\ddys_open_cache_count()); ?></td></tr>
        <tr><td>配置文件</td><td><?php echo $storage['config_exists'] ? '已创建' : '未创建'; ?></td></tr>
        <tr><td>data 目录</td><td><?php echo $storage['data_writable'] ? '可写' : '不可写：' . \ddys_open_h($storage['data_dir']); ?></td></tr>
        <tr><td>cache 目录</td><td><?php echo $storage['cache_writable'] ? '可写' : '不可写：' . \ddys_open_h($storage['cache_dir']); ?></td></tr>
        <tr><td>独立页面</td><td><a href="<?php echo \ddys_open_attr($frontUrl); ?>" target="_blank" rel="noopener"><?php echo \ddys_open_h($frontUrl); ?></a></td></tr>
        <tr><td>JSON 代理</td><td><a href="<?php echo \ddys_open_attr($apiUrl); ?>" target="_blank" rel="noopener"><?php echo \ddys_open_h($apiUrl); ?></a></td></tr>
        <tr><td>求片提交</td><td><?php echo \ddys_open_h($requestUrl); ?></td></tr>
      </table>
    </section>

    <form class="ddys-pbootcms-panel" method="post" action="<?php echo \ddys_open_attr($adminUrl); ?>">
      <input type="hidden" name="ddys_nonce" value="<?php echo \ddys_open_attr($nonce); ?>" />
      <input type="hidden" name="action" value="save" />
      <h2>配置</h2>
      <div class="ddys-pbootcms-grid">
        <label>API Base URL<input name="api_base_url" type="url" value="<?php echo \ddys_open_attr($settings['api_base_url']); ?>" /></label>
        <label>官网 Base URL<input name="site_base_url" type="url" value="<?php echo \ddys_open_attr($settings['site_base_url']); ?>" /></label>
        <label>API Key<input name="api_key" type="password" value="<?php echo \ddys_open_attr($settings['api_key']); ?>" autocomplete="off" /></label>
        <label>请求超时（秒）<input name="timeout" type="number" min="1" max="30" value="<?php echo intval($settings['timeout']); ?>" /></label>
        <label>默认缓存（秒）<input name="default_cache_ttl" type="number" min="0" max="604800" value="<?php echo intval($settings['default_cache_ttl']); ?>" /></label>
        <label>字典缓存（秒）<input name="dictionary_cache_ttl" type="number" min="0" max="604800" value="<?php echo intval($settings['dictionary_cache_ttl']); ?>" /></label>
        <label>最新/热门缓存（秒）<input name="fresh_cache_ttl" type="number" min="0" max="604800" value="<?php echo intval($settings['fresh_cache_ttl']); ?>" /></label>
        <label>列表缓存（秒）<input name="list_cache_ttl" type="number" min="0" max="604800" value="<?php echo intval($settings['list_cache_ttl']); ?>" /></label>
        <label>详情缓存（秒）<input name="detail_cache_ttl" type="number" min="0" max="604800" value="<?php echo intval($settings['detail_cache_ttl']); ?>" /></label>
        <label>社区缓存（秒）<input name="community_cache_ttl" type="number" min="0" max="604800" value="<?php echo intval($settings['community_cache_ttl']); ?>" /></label>
        <label>主题<select name="theme"><option value="auto"<?php echo $settings['theme'] === 'auto' ? ' selected' : ''; ?>>跟随系统</option><option value="light"<?php echo $settings['theme'] === 'light' ? ' selected' : ''; ?>>浅色</option><option value="dark"<?php echo $settings['theme'] === 'dark' ? ' selected' : ''; ?>>深色</option></select></label>
        <label>布局<select name="layout"><option value="grid"<?php echo $settings['layout'] === 'grid' ? ' selected' : ''; ?>>网格</option><option value="list"<?php echo $settings['layout'] === 'list' ? ' selected' : ''; ?>>列表</option><option value="compact"<?php echo $settings['layout'] === 'compact' ? ' selected' : ''; ?>>紧凑</option></select></label>
        <label>列数<input name="columns" type="number" min="1" max="6" value="<?php echo intval($settings['columns']); ?>" /></label>
        <label>链接打开<select name="target"><option value="_blank"<?php echo $settings['target'] === '_blank' ? ' selected' : ''; ?>>新窗口</option><option value="_self"<?php echo $settings['target'] === '_self' ? ' selected' : ''; ?>>当前窗口</option></select></label>
        <label>伪静态基础路径<input name="pretty_base_path" type="text" value="<?php echo \ddys_open_attr($settings['pretty_base_path']); ?>" /></label>
        <label>求片间隔（秒）<input name="request_interval" type="number" min="10" max="3600" value="<?php echo intval($settings['request_interval']); ?>" /></label>
      </div>
      <div class="ddys-pbootcms-checks">
        <label><input name="show_source_link" type="checkbox" value="1"<?php echo !empty($settings['show_source_link']) ? ' checked' : ''; ?> /> 显示来源链接</label>
        <label><input name="enable_styles" type="checkbox" value="1"<?php echo !empty($settings['enable_styles']) ? ' checked' : ''; ?> /> 加载前台样式</label>
        <label><input name="enable_request_form" type="checkbox" value="1"<?php echo !empty($settings['enable_request_form']) ? ' checked' : ''; ?> /> 启用求片表单</label>
        <label><input name="show_nav" type="checkbox" value="1"<?php echo !empty($settings['show_nav']) ? ' checked' : ''; ?> /> 独立页导航</label>
        <label><input name="enable_pretty_urls" type="checkbox" value="1"<?php echo !empty($settings['enable_pretty_urls']) ? ' checked' : ''; ?> /> 启用伪静态链接</label>
        <label><input name="debug" type="checkbox" value="1"<?php echo !empty($settings['debug']) ? ' checked' : ''; ?> /> 调试模式</label>
      </div>
      <p><button class="button primary" type="submit">保存配置</button></p>
    </form>

    <section class="ddys-pbootcms-panel">
      <h2>标签生成器</h2>
      <div class="ddys-pbootcms-generator">
        <label>类型
          <select id="ddys-pbootcms-shortcode-kind">
            <option value="ddys_movies">影片列表</option><option value="ddys_latest">最新</option><option value="ddys_hot">热门</option><option value="ddys_search">搜索</option><option value="ddys_suggest">搜索建议</option><option value="ddys_calendar">日历</option><option value="ddys_movie">影片详情</option><option value="ddys_sources">资源</option><option value="ddys_related">相关推荐</option><option value="ddys_comments">评论</option><option value="ddys_collections">片单列表</option><option value="ddys_collection">片单详情</option><option value="ddys_shares">分享列表</option><option value="ddys_share">分享详情</option><option value="ddys_requests">求片列表</option><option value="ddys_activities">动态</option><option value="ddys_user">用户</option><option value="ddys_types">类型字典</option><option value="ddys_genres">题材字典</option><option value="ddys_regions">地区字典</option><option value="ddys_request_form">求片表单</option>
          </select>
        </label>
        <label>slug <input id="ddys-pbootcms-shortcode-slug" type="text" /></label>
        <label>id <input id="ddys-pbootcms-shortcode-id" type="number" min="1" /></label>
        <label>username <input id="ddys-pbootcms-shortcode-username" type="text" /></label>
        <label>q <input id="ddys-pbootcms-shortcode-q" type="text" /></label>
        <label>limit <input id="ddys-pbootcms-shortcode-limit" type="number" min="1" max="50" value="12" /></label>
        <label>per_page <input id="ddys-pbootcms-shortcode-per-page" type="number" min="1" max="50" /></label>
        <label>year <input id="ddys-pbootcms-shortcode-year" type="number" min="1900" max="2099" /></label>
        <label>month <input id="ddys-pbootcms-shortcode-month" type="number" min="1" max="12" /></label>
        <label>type <input id="ddys-pbootcms-shortcode-type" type="text" placeholder="movie" /></label>
      </div>
      <p><button type="button" class="button" id="ddys-pbootcms-shortcode-build">生成</button></p>
      <textarea id="ddys-pbootcms-label-output" rows="4" readonly>{ddys:latest limit=12}</textarea>
      <textarea id="ddys-pbootcms-shortcode-output" rows="4" readonly>[ddys_latest limit="12"]</textarea>
      <pre>{ddys:latest type=movie limit=12}
{ddys:hot limit=10}
{ddys:search}
{ddys:calendar year=2026 month=7}
{ddys:movie slug=this-tempting-madness}
{ddys:requestform}</pre>
    </section>
  </div>
  <script src="<?php echo \ddys_open_attr(\ddys_open_static_url('js/admin.js?v=' . DDYS_OPEN_VERSION)); ?>"></script>
</body>
</html><?php
        exit;
    }
}

