<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:17
 */

use BunnyPHP\Config;
use BunnyPHP\Controller;

class IndexController extends Controller
{
    function ac_index()
    {
        if (Config::check('config')) {
            $this->redirect('blog', 'list');
        } else {
            $this->redirect('install', 'index');
        }
    }
}