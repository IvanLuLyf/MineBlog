<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/1/30
 * Time: 15:06
 */
class TwimiBindModel extends Model
{
    public function getUid($bind_uid)
    {
        if ($bind_row = $this->where(['buid=:b'], ['b' => $bind_uid])->fetch()) {
            return $bind_row['uid'];
        } else {
            return null;
        }
    }
}