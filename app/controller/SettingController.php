<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/13
 * Time: 1:29
 */

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
        $this->assign('tp_user', BunnyPHP::app()->get('tp_user'))->assign('cur_st', 'avatar');
        $this->render('setting/avatar.html');
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
        $this->assign('tp_user', $tp_user);
        $this->redirect('setting', 'avatar');
    }

    /**
     * @filter auth
     * @param array $path
     */
    public function ac_oauth(array $path)
    {
        if (count($path) < 1) $path = [''];
        list($type) = $path;
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : $type;
        $tp_user = BunnyPHP::app()->get('tp_user');
        $bind = (new BindModel())->where(['uid = :u and type = :t'], ['u' => $tp_user['uid'], 't' => $type])->fetch();
        $names = ['wb' => '微博', 'qq' => 'QQ', 'tm' => 'Twimi', 'gh' => 'Github'];
        if ($bind != null) {
            $this->assign('tp_bind', $bind);
            $image = (new OauthService($this))->avatar($type, $bind['bind'], $bind['token']);
            $this->assign('avatar', $image);
        }
        $this->assign('cur_st', $type)
            ->assign('oauth', ['type' => $type, 'name' => $names[$type]])
            ->assign('tp_user', $tp_user)
            ->render('setting/oauth.html');
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