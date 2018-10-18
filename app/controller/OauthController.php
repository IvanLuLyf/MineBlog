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
                $o = new SaeTOAuthV2($oauth['key'], $oauth['secret']);
                $url = $o->getAuthorizeURL($oauth['callback']);
                break;
            case 'tm':
                $oauth = Config::load('oauth')->get('tm');
                $url = 'http://tp.twimi.cn/index.php?mod=tauth&appkey=' . $oauth['key'] . '&url=' . urlencode($oauth['callback']);
                break;
        }
        $this->redirect($url);
    }

    function ac_callback(array $path)
    {
        if (count($path) < 1) $path = [''];
        list($type) = $path;
        if (isset($_GET['code'])) {
            switch ($type) {
                case 'tm';
                    $oauth = Config::load('oauth')->get('tm');
                    $strInfo = $this->do_post_request("http://tp.twimi.cn/api.php?mod=tauth&action=gettoken", "appkey=" . $oauth['key'] . "&appsecret=" . $oauth['secret'] . "&code=" . $_GET['code']);
                    $oauthData = json_decode($strInfo);
                    $oauthToken = $oauthData->token;
                    $oauthExpire = $oauthData->expire;
                    $strUserInfo = $this->do_post_request("http://tp.twimi.cn/api.php?mod=user&action=getinfo", "appkey=" . $oauth['key'] . "&token=$oauthToken");
                    $userInfo = json_decode($strUserInfo);
                    $bindId = $userInfo->id;
                    session_start();
                    $_SESSION['token'] = (new UserModel())->getTokenByUid($bindId);
                    $this->redirect('/index/index');
                    break;
            }
        }
    }

    function do_post_request($url, $data, $optional_headers = null)
    {
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            die("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            die("Problem reading data from $url, $php_errormsg");
        }
        return $response;
    }
}