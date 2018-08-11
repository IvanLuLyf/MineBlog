<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:43
 */

class UserController extends Controller
{
    public function ac_login_get()
    {
        $this->render("user/login.html");
    }

    public function ac_login_post()
    {
        $result = (new UserModel())->login($_POST['username'], $_POST['password']);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            if ($result['ret'] == 0) {
                session_start();
                $_SESSION['token'] = $result['token'];
                $this->redirect('/index/index');
            } else {
                $this->assign('tp_error_msg', $result['status']);
                $this->render('user/login.html');
            }
        } elseif ($this->_mode == BunnyPHP::MODE_API) {
            $this->assignAll($result);
            $this->render();
        }
    }

    public function ac_register_get()
    {
        $this->render("user/register.html");
    }

    public function ac_register_post()
    {
        $result = (new UserModel())->register($_POST['username'], $_POST['password'], $_POST['email'], $_POST['nickname']);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            if ($result['ret'] == 0) {
                session_start();
                $_SESSION['token'] = $result['token'];
                $this->redirect('/index/index');
            } else {
                $this->assign('tp_error_msg', $result['status']);
                $this->render('user/register.html');
            }
        } elseif ($this->_mode == BunnyPHP::MODE_API) {
            $this->assignAll($result);
            $this->render();
        }
    }

    public function ac_logout()
    {
        session_start();
        unset($_SESSION['token']);
        header('Location: /user/login');
    }
}