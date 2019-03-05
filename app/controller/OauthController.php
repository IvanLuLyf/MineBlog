<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/30
 * Time: 16:27
 */

class OauthController extends Controller
{
    function ac_connect(array $path)
    {
        if (count($path) < 1) $path = [''];
        list($type) = $path;
        switch ($type) {
            case 'qq':
                $oauth = Config::load('oauth')->get('qq');
                $url = 'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=' . $oauth['key'] . '&redirect_uri=' . urlencode($oauth['callback']);
                break;
            case 'wb':
                $oauth = Config::load('oauth')->get('wb');
                $url = 'https://api.weibo.com/oauth2/authorize?client_id=' . $oauth['key'] . '&redirect_uri=' . urlencode($oauth['callback']) . '&response_type=code';
                break;
            case 'gh':
                $oauth = Config::load('oauth')->get('gh');
                $url = 'https://github.com/login/oauth/authorize?client_id=' . $oauth['key'] . '&redirect_uri=' . urlencode($oauth['callback']) . '&scope=user,public_repo';
                break;
            default:
                $oauth = Config::load('oauth')->get($type);
                $url = $oauth['url'] . '/oauth/authorize?client_id=' . $oauth['key'] . '&redirect_uri=' . urlencode($oauth['callback']);
                break;
        }
        if (isset($_REQUEST['referer'])) {
            session_start();
            $_SESSION['referer'] = $_REQUEST['referer'];
        }
        $this->redirect($url);
    }

    function ac_callback(array $path, UserService $userService)
    {
        if (count($path) < 1) $path = [''];
        list($type) = $path;
        $uid = null;
        $bind_model = new BindModel();
        if (isset($_GET['code'])) {
            $bind = (new OauthService($this))->oauth($type);
            if ($uid = $bind_model->getUid($bind['uid'], $type)) {
                $userToken = (new UserModel())->refresh($uid);
                session_start();
                $_SESSION['token'] = $userToken;
                $bind_model->where(['buid = :buid and type = :t'], ['buid' => $bind['uid'], 't' => $type])->update(['token' => $bind['token'], 'expire' => $bind['expire']]);
                if (isset($_SESSION['referer'])) {
                    $referer = $_SESSION['referer'];
                    unset($_SESSION['referer']);
                    $this->redirect($referer);
                } else {
                    $this->redirect('index', 'index');
                }
            } else {
                if ($user = $userService->getLoginUser()) {
                    $bind_data = array('uid' => $user['uid'], 'type' => $type, 'buid' => $bind['uid'], 'token' => $bind['token'], 'expire' => $bind['expire']);
                    $bind_model->add($bind_data);
                    $this->redirect('setting', 'oauth', ['type' => $type]);
                } else {
                    session_start();
                    $_SESSION['oauth_user'] = [
                        'type' => $type,
                        'uid' => $bind['uid'],
                        'token' => $bind['token'],
                        'expire' => $bind['expire'],
                        'nickname' => $bind['nickname'],
                    ];
                    if (Config::load('config')->get('allow_reg')) {
                        $this->assign('oauth', ['nickname' => $bind['nickname'], 'type' => $type])
                            ->render('oauth/connect.html');
                    } else {
                        if (isset($_SESSION['referer'])) {
                            $referer = $_SESSION['referer'];
                            unset($_SESSION['referer']);
                            $this->redirect($referer);
                        } else {
                            $this->redirect('index', 'index');
                        }
                    }
                }
            }
        }
    }

    /**
     * @filter api
     */
    function ac_login()
    {
        $type = $_REQUEST['type'];
        $bind_uid = $_REQUEST['buid'];
        $bind_token = $_REQUEST['token'];
        $model = new BindModel();
        if ($uid = $model->getUid($bind_uid, $type)) {
            $model->where(['buid=:b and type=:t'], ['b' => $bind_uid, 't' => $type])->update(['token' => $bind_token]);
            $result = (new UserModel())->getUserByUid($uid);
            $appToken = (new OauthTokenModel())->get($uid, $_POST['appkey']);
            $result['token'] = $appToken['token'];
            $result['expire'] = $appToken['expire'];
            $this->assign('ret', 0)->assign('status', 'ok')->assignAll($result)->render();
        }
    }

    function ac_bind(array $path)
    {
        if (count($path) < 1) $path = [''];
        list($type) = $path;
        $bind_type = $_REQUEST['type'];
        if ($bind_type == 'reg') {
            $result = (new UserModel())->register($_POST['username'], $_POST['password'], $_POST['email'], $_POST['nickname']);
        } else {
            $result = (new UserModel())->login($_POST['username'], $_POST['password']);
        }
        if ($result['ret'] == 0) {
            session_start();
            $_SESSION['access_token'] = $result['token'];
            $bind = $_SESSION['oauth_user'];
            $bind_data = ['uid' => $result['uid'], 'type' => $type, 'buid' => $bind['uid'], 'token' => $bind['token'], 'expire' => $bind['expire']];
            (new BindModel())->add($bind_data);
            if (isset($_SESSION['referer'])) {
                $referer = $_SESSION['referer'];
                unset($_SESSION['referer']);
                $this->redirect($referer);
            } else {
                $this->redirect('index', 'index');
            }
        } else {
            $this->assignAll($result);
            $this->assign('oauth', ['type' => $type, 'nickname' => isset($_POST['nickname']) ? $_POST['nickname'] : ''])
                ->render('oauth/connect.html');
        }
    }

    function ac_logout()
    {
        session_start();
        unset($_SESSION['oauth_user']);
        if (isset($_REQUEST['referer'])) {
            $this->redirect($_REQUEST['referer']);
        } else {
            $this->redirect('user', 'login');
        }
    }

    function ac_avatar(array $path)
    {
        if (count($path) < 1) $path = ['', ''];
        list($type, $bind_id) = $path;
        $this->redirect((new OauthService($this))->avatar($type, $bind_id));
    }
}