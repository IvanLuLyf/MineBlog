<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/13
 * Time: 17:30
 */

class AvatarModel extends Model
{
    public function upload($uid, $path)
    {
        if ($this->where(["uid = ?"], [$uid])->fetch()) {
            return $this->where(["uid = :uid"], ['uid' => $uid])->update(['url' => $path]);
        } else {
            return $this->add(['uid' => $uid, 'url' => $path]);
        }
    }

    public function getAvatar($uid)
    {
        if ($row = $this->where(["uid = ?"], [$uid])->fetch()) {
            return $row['url'];
        } else {
            return '/static/img/avatar.png';
        }
    }
}