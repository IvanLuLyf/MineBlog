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
        $url = '/index';
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
            case 'tm':
                $oauth = Config::load('oauth')->get('tm');
                $url = 'http://tp.twimi.cn/index.php?mod=tauth&appkey=' . $oauth['key'] . '&url=' . urlencode($oauth['callback']);
                break;
        }
        $this->redirect($url);
    }

    function ac_callback(array $path, UserService $userService)
    {
        if (count($path) < 1) $path = [''];
        list($type) = $path;
        $uid = null;
        $bind_model = null;
        if (isset($_GET['code'])) {
            switch ($type) {
                case 'tm';
                    $oauth = Config::load('oauth')->get('tm');
                    $bind = $this->tm_oauth($oauth, $_GET['code']);
                    $bind_model = new TwimiBindModel();
                    break;
                case 'qq':
                    $oauth = Config::load('oauth')->get('qq');
                    $bind = $this->qq_oauth($oauth, $_GET['code']);
                    $bind_model = new QqBindModel();
                    break;
                case 'wb':
                    $oauth = Config::load('oauth')->get('wb');
                    $bind = $this->wb_oauth($oauth, $_GET['code']);
                    $bind_model = new SinaBindModel();
                    break;
            }
            if ($uid = $bind_model->getUid($bind['uid'])) {
                $userToken = (new UserModel())->refresh($uid);
                session_start();
                $_SESSION['token'] = $userToken;
                $bind_model->where(['buid = :buid'], ['buid' => $bind['uid']])->update(['token' => $bind['token'], 'expire' => $bind['expire']]);
                if (isset($_SESSION['referer'])) {
                    $referer = $_SESSION['referer'];
                    unset($_SESSION['referer']);
                    $this->redirect($referer);
                } else {
                    $this->redirect('index', 'index');
                }
            } else {
                if ($user = $userService->getLoginUser()) {
                    $bind_data = array('uid' => $user['uid'], 'buid' => $bind['uid'], 'token' => $bind['token'], 'expire' => $bind['expire']);
                    switch ($type) {
                        case 'tm';
                            (new TwimiBindModel())->add($bind_data);
                            break;
                        case 'qq':
                            (new QqBindModel())->add($bind_data);
                            break;
                        case 'wb':
                            (new SinaBindModel())->add($bind_data);
                            break;
                    }
                    $this->redirect('setting', $type);
                } else {
                    session_start();
                    if (Config::load('config')->get('allow_reg')) {
                        $_SESSION[$type . '_bind_uid'] = $bind['uid'];
                        $_SESSION[$type . '_token'] = $bind['token'];
                        $_SESSION[$type . '_expire'] = $bind['expire'];
                        $this->assign('oauth', ['nickname' => $bind['nickname'], 'type' => $type])
                            ->render('oauth/connect.html');
                    } else {
                        $_SESSION['oauth_user'] = [
                            'type' => $type,
                            'uid' => $bind['uid'],
                            'nickname' => $bind['nickname'],
                        ];
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
        $model = null;
        switch ($type) {
            case 'tm';
                $model = new TwimiBindModel();
                break;
            case 'qq':
                $model = new QqBindModel();
                break;
            case 'wb':
                $model = new SinaBindModel();
                break;
        }
        if ($uid = $model->getUid($bind_uid)) {
            $model->where(['buid=:b'], ['b' => $bind_uid])->update(['token' => $bind_token]);
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
            $bind_data = array('uid' => $result['uid'], 'buid' => $_SESSION[$type . '_bind_uid'], 'token' => $_SESSION[$type . '_token'], 'expire' => $_SESSION[$type . '_expire']);
            switch ($type) {
                case 'tm';
                    (new TwimiBindModel())->add($bind_data);
                    break;
                case 'qq':
                    (new QqBindModel())->add($bind_data);
                    break;
                case 'wb':
                    (new SinaBindModel())->add($bind_data);
                    break;
            }
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

    function ac_avatar(array $path)
    {
        if (count($path) < 1) $path = ['', ''];
        list($type, $bind_id) = $path;
        $imgUrl = '/static/images/avatar.jpg';
        switch ($type) {
            case 'qq':
                $oauth = Config::load('oauth')->get('qq');
                $imgUrl = $this->qq_avatar($oauth, $bind_id);
                break;
            case 'wb':
                $oauth = Config::load('oauth')->get('wb');
                $imgUrl = $this->wb_avatar($oauth, $bind_id);
                break;
            case 'tm':
                $imgUrl = "https://ts.twimi.cn/user/avatar/$bind_id";
                break;
        }
        $this->redirect($imgUrl);
    }

    function tm_oauth($oauth, $code)
    {
        $strInfo = $this->do_post_request("http://tp.twimi.cn/api.php?mod=tauth&action=gettoken", "appkey=" . $oauth['key'] . "&appsecret=" . $oauth['secret'] . "&code=" . $code);
        $oauth_data = json_decode($strInfo, true);
        $oauthToken = $oauth_data['token'];
        $strUserInfo = $this->do_post_request("http://tp.twimi.cn/api.php?mod=user&action=getinfo", "appkey=" . $oauth['key'] . "&token=$oauthToken");
        $user_info = json_decode($strUserInfo, true);
        return ['uid' => $user_info['id'], 'nickname' => $user_info['nickname'], 'token' => $oauthToken, 'expire' => $oauth_data['expire']];
    }

    function qq_oauth($oauth, $code)
    {
        $token_url = 'https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&' . 'client_id=' . $oauth['key'] . '&redirect_uri=' . urlencode($oauth['callback']) . '&client_secret=' . $oauth['secret'] . '&code=' . $code;
        $token = [];
        parse_str($this->do_get_request($token_url), $token);
        $open_id_str = $this->do_get_request('https://graph.qq.com/oauth2.0/me?access_token=' . $token['access_token']);
        if (strpos($open_id_str, "callback") !== false) {
            $l_pos = strpos($open_id_str, "(");
            $r_pos = strrpos($open_id_str, ")");
            $open_id_str = substr($open_id_str, $l_pos + 1, $r_pos - $l_pos - 1);
        }
        $open_id = json_decode($open_id_str, TRUE);
        $user_info_url = 'https://graph.qq.com/user/get_user_info?' . 'access_token=' . $token['access_token'] . '&oauth_consumer_key=' . $oauth['key'] . '&openid=' . $open_id['openid'] . '&format=json';
        $user_info = json_decode($this->do_get_request($user_info_url), TRUE);
        return ['uid' => $open_id['openid'], 'nickname' => $user_info['nickname'], 'token' => $token['access_token'], 'expire' => time() + $token['expires_in']];
    }

    function wb_oauth($oauth, $code)
    {
        $token_url = 'https://api.weibo.com/oauth2/access_token';
        $token = json_decode($this->do_post_request($token_url, "client_id=" . $oauth['key'] . "&client_secret=" . $oauth['secret'] . "&grant_type=authorization_code&code=" . $code . "&redirect_uri=" . $oauth['callback']), TRUE);
        $user_info_url = "https://api.weibo.com/2/users/show.json?access_token=" . $token['access_token'] . "&uid=" . $token['uid'];
        $user_info = json_decode($this->do_get_request($user_info_url), TRUE);
        return ['uid' => $token['uid'], 'nickname' => $user_info['screen_name'], 'token' => $token['access_token'], 'expire' => time() + $token['expires_in']];
    }

    function wb_avatar($oauth, $bind_id)
    {
        $row = (new SinaBindModel())->where(['uid = 1'], [])->fetch();
        $user_info_url = "https://api.weibo.com/2/users/show.json?access_token=" . $row['token'] . "&uid=" . $bind_id;
        $user_info = json_decode($this->do_get_request($user_info_url), TRUE);
        return $user_info['avatar_large'];
    }

    function qq_avatar($oauth, $bind_id)
    {
        return "https://qzapp.qlogo.cn/qzapp/{$oauth['key']}/$bind_id/100";
    }

    function do_get_request($url)
    {
        $params = ['http' => ['method' => 'GET',]];
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) die("Problem with $url, $php_errormsg");
        $response = @stream_get_contents($fp);
        if ($response === false) die("Problem reading data from $url, $php_errormsg");
        return $response;
    }

    function do_post_request($url, $data, $optional_headers = null)
    {
        $params = ['http' => ['method' => 'POST', 'content' => $data]];
        if ($optional_headers !== null) $params['http']['header'] = $optional_headers;
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) die("Problem with $url, $php_errormsg");
        $response = @stream_get_contents($fp);
        if ($response === false) die("Problem reading data from $url, $php_errormsg");
        return $response;
    }
}