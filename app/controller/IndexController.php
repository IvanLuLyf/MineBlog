<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:17
 */

namespace MineBlog\Controller;

use BunnyPHP\Config;
use BunnyPHP\Controller;
use MineBlog\Model\UserModel;

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

    public function ac_test(){
        echo UserModel::name();
        echo UserModel::create(true);
    }
}