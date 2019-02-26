<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/2/26
 * Time: 16:51
 */

class BindModel extends Model
{
    public function getUid($bind_uid, $type)
    {
        if ($bind_row = $this->where(['buid=:b and type=:t'], ['b' => $bind_uid, 't' => $type])->fetch()) {
            return $bind_row['uid'];
        } else {
            return null;
        }
    }
}