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
        if (Config::check("oauth")) {
            $sites = Config::load('oauth')->all();
            $oauth = [];
            foreach ($sites as $name => $site) {
                $oauth[] = $name;
            }
            $this->assign('oauth', $oauth);
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
            if ($result['ret'] == 0) {
                $appToken = (new OauthTokenModel())->get($result['uid'], $_POST['appkey']);
                $result['token'] = $appToken['token'];
                $result['expire'] = $appToken['expire'];
            }
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
        } else {
            $this->assign('ret', 1007);
            $this->assign('status', 'register not allowed');
            $this->assign('tp_error_msg', "站点关闭注册");
            $this->render('common/error.html');
        }
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
                if ($result['ret'] == 0) {
                    $appToken = (new OauthTokenModel())->get($result['uid'], $_POST['appkey']);
                    $result['token'] = $appToken['token'];
                    $result['expire'] = $appToken['expire'];
                }
                $this->assignAll($result);
                $this->render();
            }
        } else {
            $this->assign('ret', 1007);
            $this->assign('status', 'register not allowed');
            $this->assign('tp_error_msg', "站点关闭注册");
            $this->render('common/error.html');
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
            if ($uid = (new UserModel())->where(["username = :username"], ['username' => $username])->fetch()['uid']) {
                $imgUrl = (new AvatarModel())->getAvatar($uid);
            }
        } else if ($uid != 0) {
            $imgUrl = (new AvatarModel())->getAvatar($uid);
        }
        $this->redirect($imgUrl);
    }

    public function ac_blog(array $path)
    {
        if (count($path) == 0) $path = [''];
        $username = isset($_GET['username']) ? $_GET['username'] : $path[0];
        $page = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : isset($path[1]) ? $path[1] : 1;
        $tp_user = $this->service('user')->getLoginUser();
        if ($username == '') {
            if ($tp_user == null) {
                $this->redirect('user', 'login', ['referer' => View::get_url('user', 'blog')]);
                return;
            }
            $username = $tp_user['username'];
        }
        $visible = 0;
        if ($tp_user != null && $tp_user['username'] == $username) {
            $visible = 2;
        }
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            include APP_PATH . 'library/Parser.php';
            $parser = new HyperDown\Parser;
            $this->assign('parser', $parser);
            $this->assign('tp_user', $tp_user);
            $this->assign('cur_ctr', 'blog');
        }
        $user = (new UserModel())->where(["username = :username"], ['username' => $username])->fetch(['uid', 'username', 'nickname']);
        $user_info = (new UserInfoModel())->get($user['uid']);
        $blogs = (new BlogModel())->getBlogByUsername($username, $visible);
        $this->assign('user', $user);
        $this->assign('user_info', $user_info);
        $this->assign("page", $page);
        $this->assign("blogs", $blogs);
        $this->render('user/blog.html');
    }
}