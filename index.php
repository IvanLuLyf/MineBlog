<?php
define('APP_PATH', __DIR__ . '/');
define('APP_DEBUG', false);
define("IN_TWIMI_PHP", "True", TRUE);
date_default_timezone_set('PRC');
require(APP_PATH . 'BunnyPHP/BunnyPHP.php');
(new BunnyPHP())->run();