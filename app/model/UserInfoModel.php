<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/22
 * Time: 14:14
 */

namespace MineBlog\Model;

use BunnyPHP\Model;

class UserInfoModel extends Model
{
    protected $_column = [
        'uid' => ['integer', 'not null'],
        'signature' => ['text'],
        'cover' => ['text'],
        'background' => ['text'],
    ];
    protected $_pk = ['uid'];

    public function get($uid)
    {
        return $this->where(['uid = :uid'], ['uid' => $uid])->fetch();
    }
}