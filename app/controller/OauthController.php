<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/30
 * Time: 16:27
 */

class OauthController extends Controller
{
    function ac_connect(array $path, $referer)
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
        if ($referer) {
            BunnyPHP::getRequest()->setSession('referer', $referer);
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
                BunnyPHP::getRequest()->setSession('token', $userToken);
                $bind_model->where(['bind = :b and type = :t'], ['b' => $bind['uid'], 't' => $type])->update(['token' => $bind['token'], 'expire' => $bind['expire']]);
                $referer = BunnyPHP::getRequest()->delSession('referer');
                if ($referer) {
                    $this->redirect($referer);
                } else {
                    $this->redirect('index', 'index');
                }
            } else {
                if ($user = $userService->getLoginUser()) {
                    $bind_data = array('uid' => $user['uid'], 'type' => $type, 'bind' => $bind['uid'], 'token' => $bind['token'], 'expire' => $bind['expire']);
                    $bind_model->add($bind_data);
                    $this->redirect('setting', 'oauth', ['type' => $type]);
                } else {
                    BunnyPHP::getRequest()->setSession('oauth_user', [
                        'type' => $type,
                        'uid' => $bind['uid'],
                        'token' => $bind['token'],
                        'expire' => $bind['expire'],
                        'nickname' => $bind['nickname'],
                    ]);
                    if (Config::load('config')->get('allow_reg')) {
                        $this->assign('oauth', ['nickname' => $bind['nickname'], 'type' => $type])
                            ->render('oauth/connect.html');
                    } else {
                        $referer = BunnyPHP::getRequest()->delSession('referer');
                        if ($referer) {
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
     * @param $type
     * @param $bind
     * @param $token
     */
    function ac_login($type, $bind, $token)
    {
        $model = new BindModel();
        if ($uid = $model->getUid($bind, $type)) {
            $model->where(['bind=:b and type=:t'], ['b' => $bind, 't' => $type])->update(['token' => $token]);
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
            BunnyPHP::getRequest()->setSession('access_token', $result['token']);
            $bind = BunnyPHP::getRequest()->getSession('oauth_user');
            $bind_data = ['uid' => $result['uid'], 'type' => $type, 'bind' => $bind['uid'], 'token' => $bind['token'], 'expire' => $bind['expire']];
            (new BindModel())->add($bind_data);
            $referer = BunnyPHP::getRequest()->delSession('referer');
            if ($referer) {
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

    function ac_logout($referer)
    {
        BunnyPHP::getRequest()->delSession('oauth_user');
        if ($referer) {
            $this->redirect($referer);
        } else {
            $this->redirect('user', 'login');
        }
    }

    /**
     * @param string $type path(0)
     * @param string $bind_id path(1)
     */
    function ac_avatar(string $type, string $bind_id)
    {
        $this->redirect((new OauthService($this))->avatar($type, $bind_id));
    }
}