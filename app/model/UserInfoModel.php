<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/22
 * Time: 14:14
 */

class UserInfoModel extends Model
{
    public function get($uid)
    {
        return $this->where(['uid = :uid'], ['uid' => $uid])->fetch();
    }
}