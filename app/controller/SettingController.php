<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/13
 * Time: 1:29
 */

namespace MineBlog\Controller;

use BunnyPHP\BunnyPHP;
use BunnyPHP\Config;
use BunnyPHP\Controller;
use BunnyPHP\Language;
use MineBlog\Model\AvatarModel;
use MineBlog\Model\BindModel;
use MineBlog\Service\OauthService;

class SettingController extends Controller
{
    public function ac_index()
    {
        $this->redirect('setting', 'avatar');
    }

    /**
     * @filter auth
     */
    public function ac_avatar()
    {
        $this->assign('cur_st', 'avatar')->render('setting/avatar.html');
    }

    /**
     * @filter auth
     */
    public function ac_gravatar()
    {
        $tp_user = BunnyPHP::app()->get('tp_user');
        if (!empty($tp_user['uid'])) {
            (new AvatarModel())->upload($tp_user['uid'], 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($tp_user['email']))));
        }
        $this->redirect('setting', 'avatar');
    }

    /**
     * @filter auth
     * @param array $path
     */
    public function ac_oauth(array $path)
    {
        if (Config::check('oauth')) {
            if (count($path) < 1) $path = [''];
            list($type) = $path;
            $oauth_enabled = Config::load('oauth')->get('enabled', []);
            $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : $type;
            $type = $type == '' ? $oauth_enabled[0][0] : $type;
            $tp_user = BunnyPHP::app()->get('tp_user');
            $bind = (new BindModel())->where(['uid=:u and type=:t'], ['u' => $tp_user['uid'], 't' => $type])->fetch();
            if ($bind != null) {
                $this->assign('tp_bind', $bind);
                $image = (new OauthService($this))->avatar($type, $bind['bind'], $bind['token']);
                $this->assign('avatar', $image);
            }
            $name = '';
            foreach ($oauth_enabled as $o) {
                if ($o[0] == $type) {
                    $name = $o[1];
                    break;
                }
            }
            $this->assign("oauth_list", $oauth_enabled);
            $this->assign('cur_st', "oauth")
                ->assign('oauth', ['type' => $type, 'name' => $name])
                ->render('setting/oauth.html');
        } else {
            $this->assignAll(['ret' => 1007, 'status' => 'oauth is not enabled', 'tp_error_msg' => Language::get('oauth_close')])->error();
        }
    }

    /**
     * @filter auth
     * @param array $path
     */
    public function ac_oauth_avatar(array $path)
    {
        if (count($path) < 1) $path = [''];
        list($type) = $path;
        $tp_user = BunnyPHP::app()->get('tp_user');
        if (!empty($tp_user['uid'])) {
            (new AvatarModel())->upload($tp_user['uid'], $_REQUEST['avatar']);
        }
        $this->assign('tp_user', $tp_user);
        $this->redirect('setting', $type);
    }
}