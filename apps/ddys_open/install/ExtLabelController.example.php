<?php
/**
 * Merge this file into apps/home/controller/ExtLabelController.php.
 * Do not overwrite an existing ExtLabelController on a production site.
 */
namespace app\home\controller;

class ExtLabelController
{
    public function run($content)
    {
        require_once dirname(dirname(__DIR__)) . '/ddys_open/bootstrap.php';
        \ddys_open_bootstrap();
        return \ddys_open_parse_labels($content);
    }
}
