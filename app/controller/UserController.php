<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:43
 */

namespace MineBlog\Controller;

use BunnyPHP\BunnyPHP;
use BunnyPHP\Config;
use BunnyPHP\Controller;
use BunnyPHP\Language;
use BunnyPHP\View;
use MineBlog\Model\AvatarModel;
use MineBlog\Model\BlogModel;
use MineBlog\Model\OauthTokenModel;
use MineBlog\Model\UserInfoModel;
use MineBlog\Model\UserModel;
use MineBlog\Service\UserService;

class UserController extends Controller
{
    /**
     * @filter csrf
     * @param $referer
     */
    public function ac_login_get($referer)
    {
        if ($referer) {
            BunnyPHP::getRequest()->setSession('referer', $referer);
            $this->assign('referer', $referer);
        }
        $oauth = [];
        if (Config::check("oauth")) {
            $oauth = Config::load('oauth')->get('enabled', []);
        }
        $this->assign('oauth', $oauth);
        $this->renderTemplate("user/login.html");
    }

    /**
     * @filter csrf check
     * @param $referer
     */
    public function ac_login_post($referer)
    {
        $result = (new UserModel())->login($_POST['username'], $_POST['password']);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            if ($result['ret'] == 0) {
                BunnyPHP::getRequest()->setSession('token', $result['token']);
                $refererUrl = BunnyPHP::getRequest()->delSession('referer');
                $refererUrl = $referer ? $referer : $refererUrl;
                if ($refererUrl) {
                    $this->redirect($refererUrl);
                } else {
                    $this->redirect('index', 'index');
                }
            } else {
                $this->assignAll($result);
                BunnyPHP::getLogger()->info("login failed username:'{u}' password:'{p}'", ['{u}' => $_POST['username'], '{p}' => $_POST['password']]);
                $oauth = [];
                if (Config::check("oauth")) {
                    $oauth = Config::load('oauth')->get('enabled', []);
                }
                $this->assign('oauth', $oauth);
                $this->renderTemplate('user/login.html');
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

    /**
     * @filter csrf
     * @param $referer
     */
    public function ac_register_get($referer)
    {
        if (Config::load('config')->get('allow_reg')) {
            if ($referer) {
                BunnyPHP::getRequest()->setSession('referer', $referer);
                $this->assign('referer', $referer);
            }
            $this->renderTemplate("user/register.html");
        } else {
            $this->assignAll(['ret' => 1005, 'status' => 'registration is not allowed', 'tp_error_msg' => Language::get('reg_close')])->error();
        }
    }

    /**
     * @filter csrf check
     * @param $referer
     */
    public function ac_register_post($referer)
    {
        if (Config::load('config')->get('allow_reg')) {
            $result = (new UserModel())->register($_POST['username'], $_POST['password'], $_POST['email'], $_POST['nickname']);
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                if ($result['ret'] == 0) {
                    BunnyPHP::getRequest()->setSession('token', $result['token']);
                    $refererUrl = BunnyPHP::getRequest()->delSession('referer');
                    $refererUrl = $referer ? $referer : $refererUrl;
                    if ($refererUrl) {
                        $this->redirect($refererUrl);
                    } else {
                        $this->redirect('index', 'index');
                    }
                } else {
                    $this->assignAll($result);
                    $this->renderTemplate('user/register.html');
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
            $this->assignAll(['ret' => 1005, 'status' => 'registration is not allowed', 'tp_error_msg' => Language::get('reg_close')])->error();
        }
    }

    public function ac_logout()
    {
        BunnyPHP::getRequest()->delSession('token');
        $this->redirect('user', 'login');
    }

    public function ac_avatar_get(array $path, $username)
    {
        if (count($path) == 0) $path = [0];
        $uid = isset($_GET['uid']) ? $_GET['uid'] : $path[0];
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

    /**
     * @filter ajax
     * @filter api
     * @filter auth
     */
    public function ac_avatar_post()
    {
        $tp_user = BunnyPHP::app()->get('tp_user');
        $this->assign('tp_user', $tp_user);
        if (isset($_FILES['avatar'])) {
            $image_type = ['image/bmp', 'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'application/x-bmp', 'application/x-jpg', 'application/x-png'];
            if (in_array($_FILES["avatar"]["type"], $image_type) && ($_FILES["avatar"]["size"] < 2000000)) {
                $t = time() % 1000;
                $url = BunnyPHP::getStorage()->upload("avatar/" . $tp_user['uid'] . '_' . $t . ".jpg", $_FILES["avatar"]["tmp_name"]);
                (new AvatarModel())->upload($tp_user['uid'], $url);
                $response = ['ret' => 0, 'status' => 'ok', 'url' => $url];
            } else {
                $response = ['ret' => 2, 'status' => 'invalid file'];
            }
            $this->assignAll($response);
        }
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            $this->redirect('setting', 'avatar');
        } else {
            $this->render('setting/avatar.html');
        }
    }

    /**
     * @param $username string path(0)
     * @param $page integer path(1,1)
     * @param UserService $userService
     */
    public function ac_blog($username, $page, UserService $userService)
    {
        $tp_user = $userService->getLoginUser();
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
            $this->assign('tp_user', $tp_user);
            $this->assign('cur_ctr', 'blog');
        }
        $user = (new UserModel())->where(["username = :username"], ['username' => $username])->fetch(['uid', 'username', 'nickname']);
        if ($user != null) {
            $user_info = (new UserInfoModel())->get($user['uid']);
            $blogs = (new BlogModel())->getBlogByUsername($username, $visible);
            $this->assign('user', $user);
            $this->assign('user_info', $user_info);
            $this->assign("page", $page);
            $this->assign("blogs", $blogs);
            $this->render('user/blog.html');
        } else {
            $this->assignAll(['ret' => 1002, 'status' => "user does not exist", 'tp_error_msg' => Language::get('user_not_exist')])->error();
        }
    }
}