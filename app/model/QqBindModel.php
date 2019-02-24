<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/2/14
 * Time: 2:25
 */

class QqBindModel extends Model
{
    public function getAvatar($key, $uid)
    {
        if ($bind_row = $this->where(['uid=:uid'], ['uid' => $uid])->fetch()) {
            $bind_uid = $bind_row['buid'];
            return "https://qzapp.qlogo.cn/qzapp/$key/$bind_uid/100";
        } else {
            return '/static/images/avatar.jpg';
        }
    }

    public function getUid($bind_uid)
    {
        if ($bind_row = $this->where(['buid=:b'], ['b' => $bind_uid])->fetch()) {
            return $bind_row['uid'];
        } else {
            return null;
        }
    }
}