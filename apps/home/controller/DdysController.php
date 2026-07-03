<?php

namespace app\home\controller;

use core\basic\Controller;

class DdysController extends Controller
{
    private function boot()
    {
        require_once dirname(dirname(dirname(__DIR__))) . '/apps/ddys_open/bootstrap.php';
        \ddys_open_bootstrap();
        \ddys_open_storage_ensure();
    }

    public function index()
    {
        $this->boot();
        $view = \ddys_open_choice(\ddys_open_get('view', 'latest'), array('latest', 'hot', 'search', 'calendar', 'movie', 'collections', 'requests'), 'latest');
        $params = array(
            'limit' => \ddys_open_get('limit'),
            'page' => \ddys_open_get('page'),
            'per_page' => \ddys_open_get('per_page'),
            'year' => \ddys_open_get('year'),
            'month' => \ddys_open_get('month'),
            'slug' => \ddys_open_get('slug'),
            'q' => \ddys_open_get('q', \ddys_open_get('ddys_q')),
            'type' => \ddys_open_get('type', \ddys_open_get('ddys_type')),
        );
        echo \ddys_open_render_full_page($view, $params);
        exit;
    }

    public function api()
    {
        $this->boot();
        \ddys_open_json_response(\ddys_open_proxy_response());
    }

    public function request()
    {
        $this->boot();
        if (strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') !== 'POST') {
            \ddys_open_json_response(\ddys_open_error('Method not allowed.', 405), 405);
        }
        \ddys_open_json_response(\ddys_open_handle_request_form());
    }

    public function check()
    {
        $this->boot();
        $storage = \ddys_open_storage_status();
        \ddys_open_json_response(array(
            'success' => true,
            'plugin' => DDYS_OPEN_ID,
            'version' => DDYS_OPEN_VERSION,
            'storage' => array(
                'data_writable' => !empty($storage['data_writable']),
                'cache_writable' => !empty($storage['cache_writable']),
                'config_exists' => !empty($storage['config_exists']),
            ),
        ));
    }
}

