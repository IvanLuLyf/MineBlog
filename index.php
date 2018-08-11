<?php
define('APP_PATH', __DIR__ . '/');
define('APP_DEBUG', false);
define("IN_TWIMI_PHP", "True", TRUE);
date_default_timezone_set('PRC');
if (!file_exists("config/config.php")) {
    header("Location: /install.php");
    return;
}
require(APP_PATH . 'BunnyPHP/BunnyPHP.php');
$config = require(APP_PATH . 'config/config.php');
(new BunnyPHP($config))->run();