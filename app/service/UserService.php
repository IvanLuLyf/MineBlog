<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/30
 * Time: 0:50
 */

use BunnyPHP\BunnyPHP;
use BunnyPHP\Service;

class UserService extends Service
{
    public function getLoginUser()
    {
        $token = BunnyPHP::getRequest()->getSession('token');
        if ($token) {
            return (new UserModel)->check($token);
        }
        return null;
    }
}