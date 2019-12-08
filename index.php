<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/5
 * Time: 15:31
 */

define('APP_PATH', __DIR__ . '/');
define('APP_DEBUG', true);
define("IN_TWIMI_PHP", "True", TRUE);
define('TP_NAMESPACE', '\\MineBlog');
date_default_timezone_set('PRC');
if (file_exists(APP_PATH . 'BunnyPHP/BunnyPHP.php')) {
    require(APP_PATH . 'BunnyPHP/BunnyPHP.php');
} else {
    require(APP_PATH . 'vendor/autoload.php');
}
(new BunnyPHP\BunnyPHP())->run();