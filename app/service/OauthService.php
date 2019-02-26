<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/2/26
 * Time: 17:31
 */

class OauthService extends Service
{
    private $controller;

    function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    function oauth($type)
    {
        switch ($type) {
            case 'tm';
                $oauth = Config::load('oauth')->get('tm');
                return $this->tm_oauth($oauth, $_GET['code']);
            case 'qq':
                $oauth = Config::load('oauth')->get('qq');
                return $this->qq_oauth($oauth, $_GET['code']);
            case 'wb':
                $oauth = Config::load('oauth')->get('wb');
                return $this->wb_oauth($oauth, $_GET['code']);
            case 'gh':
                $oauth = Config::load('oauth')->get('gh');
                return $this->gh_oauth($oauth, $_GET['code']);
        }
        return null;
    }

    function avatar($type, $bind_id, $token = '')
    {
        $imgUrl = '/static/img/avatar.png';
        switch ($type) {
            case 'qq':
                $oauth = Config::load('oauth')->get('qq');
                $imgUrl = $this->qq_avatar($oauth, $bind_id);
                break;
            case 'wb':
                $imgUrl = $this->wb_avatar($bind_id, $token);
                break;
            case 'gh':
                $imgUrl = "https://avatars.githubusercontent.com/u/$bind_id";
                break;
            case 'tm':
                $imgUrl = "https://ts.twimi.cn/user/avatar/$bind_id";
                break;
        }
        return $imgUrl;
    }

    private function tm_oauth($oauth, $code)
    {
        $strInfo = $this->do_post_request("http://tp.twimi.cn/api.php?mod=tauth&action=gettoken", "appkey=" . $oauth['key'] . "&appsecret=" . $oauth['secret'] . "&code=" . $code);
        $oauth_data = json_decode($strInfo, true);
        $oauthToken = $oauth_data['token'];
        $strUserInfo = $this->do_post_request("http://tp.twimi.cn/api.php?mod=user&action=getinfo", "appkey=" . $oauth['key'] . "&token=$oauthToken");
        $user_info = json_decode($strUserInfo, true);
        return ['uid' => $user_info['id'], 'nickname' => $user_info['nickname'], 'token' => $oauthToken, 'expire' => $oauth_data['expire']];
    }

    private function qq_oauth($oauth, $code)
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

    private function gh_oauth($oauth, $code)
    {
        $token_url = 'https://github.com/login/oauth/access_token';
        $token = [];
        parse_str($this->do_post_request($token_url, "client_id=" . $oauth['key'] . "&client_secret=" . $oauth['secret'] . "&code=" . $code . "&redirect_uri=" . $oauth['callback']), $token);
        $user_info_url = "https://api.github.com/user?access_token=" . $token['access_token'];
        $user_info = json_decode($this->do_get_request($user_info_url), TRUE);
        return ['uid' => $user_info['id'], 'nickname' => $user_info['login'], 'token' => $token['access_token'], 'expire' => time()];
    }

    private function wb_oauth($oauth, $code)
    {
        $token_url = 'https://api.weibo.com/oauth2/access_token';
        $token = json_decode($this->do_post_request($token_url, "client_id=" . $oauth['key'] . "&client_secret=" . $oauth['secret'] . "&grant_type=authorization_code&code=" . $code . "&redirect_uri=" . $oauth['callback']), TRUE);
        $user_info_url = "https://api.weibo.com/2/users/show.json?access_token=" . $token['access_token'] . "&uid=" . $token['uid'];
        $user_info = json_decode($this->do_get_request($user_info_url), TRUE);
        return ['uid' => $token['uid'], 'nickname' => $user_info['screen_name'], 'token' => $token['access_token'], 'expire' => time() + $token['expires_in']];
    }

    private function wb_avatar($bind_id, $token)
    {
        if ($token == '') {
            $token = (new BindModel())->where(['uid = 1 and type="wb"'], [])->fetch()['token'];
        }
        $user_info_url = "https://api.weibo.com/2/users/show.json?access_token=$token&uid=$bind_id";
        $user_info = json_decode($this->do_get_request($user_info_url), TRUE);
        return str_replace('http:', 'https:', $user_info['avatar_large']);
    }

    private function qq_avatar($oauth, $bind_id)
    {
        return "https://qzapp.qlogo.cn/qzapp/{$oauth['key']}/$bind_id/100";
    }

    private function do_get_request($url)
    {
        $params = ['http' => ['method' => 'GET', 'header' => ['User-Agent: BunnyPHP']]];
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            $this->controller->assign('ret', 2004)
                ->assign('status', "can't open url")
                ->assign('tp_error_msg', "无法打开请求页面" . json_encode(error_get_last()))
                ->render('common/error.html');
            die();
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            $this->controller->assign('ret', 2005)
                ->assign('status', "can't read content")
                ->assign('tp_error_msg', "无法读取页面内容" . json_encode(error_get_last()))
                ->render('common/error.html');
            die();
        }
        return $response;
    }

    private function do_post_request($url, $data, $optional_headers = null)
    {
        $params = ['http' => ['method' => 'POST', 'header' => ['User-Agent: MineBlog'], 'content' => $data]];
        if ($optional_headers !== null) $params['http']['header'] = $optional_headers;
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            $this->controller->assign('ret', 2004)
                ->assign('status', "can't open url")
                ->assign('tp_error_msg', "无法打开请求页面")
                ->render('common/error.html');
            die();
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            $this->controller->assign('ret', 2005)
                ->assign('status', "can't read content")
                ->assign('tp_error_msg', "无法读取页面内容")
                ->render('common/error.html');
            die();
        }
        return $response;
    }
}