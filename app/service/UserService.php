<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/30
 * Time: 0:50
 */

class UserService extends Service
{
    public function getLoginUser()
    {
        session_start();
        if (isset($_SESSION['token']) && $_SESSION['token'] != "") {
            return (new UserModel)->check($_SESSION["token"]);
        }
        return null;
    }

    public function getAuthUser()
    {

    }
}