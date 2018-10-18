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
        if (isset($_REQUEST['referer'])) {
            session_start();
            $referer = $_REQUEST['referer'];
            $_SESSION['referer'] = $referer;
            $this->assign('referer', $referer);
        }
        $this->render("user/login.html");
    }

    public function ac_login_post()
    {
        $result = (new UserModel())->login($_POST['username'], $_POST['password']);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            if ($result['ret'] == 0) {
                session_start();
                $_SESSION['token'] = $result['token'];
                if (isset($_SESSION['referer'])) {
                    $referer = $_SESSION['referer'];
                    unset($_SESSION['referer']);
                    $this->redirect($referer);
                } else {
                    $this->redirect('index', 'index');
                }
            } else {
                $this->assignAll($result);
                $this->render('user/login.html');
            }
        } elseif ($this->_mode == BunnyPHP::MODE_API) {
            $this->assignAll($result);
            $this->render();
        }
    }

    public function ac_register_get()
    {
        if (Config::load('config')->get('allow_reg')) {
            if (isset($_REQUEST['referer'])) {
                session_start();
                $referer = $_REQUEST['referer'];
                $_SESSION['referer'] = $referer;
                $this->assign('referer', $referer);
            }
            $this->render("user/register.html");
        } else
            echo "<h1>站点关闭注册</h1>";
    }

    public function ac_register_post()
    {
        if (Config::load('config')->get('allow_reg')) {
            $result = (new UserModel())->register($_POST['username'], $_POST['password'], $_POST['email'], $_POST['nickname']);
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                if ($result['ret'] == 0) {
                    session_start();
                    $_SESSION['token'] = $result['token'];
                    if (isset($_SESSION['referer'])) {
                        $referer = $_SESSION['referer'];
                        unset($_SESSION['referer']);
                        $this->redirect($referer);
                    } else {
                        $this->redirect('index', 'index');
                    }
                } else {
                    $this->assignAll($result);
                    $this->render('user/register.html');
                }
            } elseif ($this->_mode == BunnyPHP::MODE_API) {
                $this->assignAll($result);
                $this->render();
            }
        } else {
            echo "<h1>站点关闭注册</h1>";
        }
    }

    public function ac_logout()
    {
        session_start();
        unset($_SESSION['token']);
        $this->redirect('user', 'login');
    }

    public function ac_avatar_get(array $path)
    {
        if (count($path) == 0) $path = [0];
        $uid = isset($_GET['uid']) ? $_GET['uid'] : $path[0];
        $username = isset($_GET['username']) ? $_GET['username'] : null;
        $imgUrl = "/static/img/avatar.png";
        if ($username != null) {
            if ($uid = (new UserModel())->where(["username = :username"], ['username' => $username])->fetch()['id']) {
                $imgUrl = (new AvatarModel())->getAvatar($uid);
            }
        } else if ($uid != 0) {
            $imgUrl = (new AvatarModel())->getAvatar($uid);
        }
        $this->redirect($imgUrl);
    }
}