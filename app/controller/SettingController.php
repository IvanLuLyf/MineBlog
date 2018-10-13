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
        $tp_user = $this->service('user')->getLoginUser();
        if ($tp_user == null) {
            $this->redirect('user', 'login');
            return;
        }
        $this->assign('tp_user', $tp_user);
        $this->render('setting/avatar.html');
    }

    public function ac_gravatar()
    {
        $tp_user = $this->service('user')->getLoginUser();
        if ($tp_user == null) {
            $this->redirect('user', 'login');
            return;
        }
        (new AvatarModel())->upload($tp_user['id'], 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($tp_user['email']))));
        $this->assign('tp_user', $tp_user);
        $this->redirect('setting', 'index');
    }
}