<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/13
 * Time: 1:29
 */

class SettingController extends Controller
{
    /**
     * @filter auth
     */
    public function ac_index()
    {
        $this->assign('tp_user', BunnyPHP::app()->get('tp_user'));
        $this->render('setting/avatar.html');
    }

    /**
     * @filter auth
     */
    public function ac_gravatar()
    {
        $tp_user = BunnyPHP::app()->get('tp_user');
        (new AvatarModel())->upload($tp_user['uid'], 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($tp_user['email']))));
        $this->assign('tp_user', $tp_user);
        $this->redirect('setting', 'index');
    }
}