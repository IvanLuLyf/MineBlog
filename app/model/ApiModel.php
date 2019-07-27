<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/1/1
 * Time: 16:45
 */

namespace MineBlog\Model;

use BunnyPHP\Model;

class ApiModel extends Model
{
    protected $_column = [
        'id' => ['integer', 'not null'],
        'uid' => ['integer', 'not null'],
        'appname' => ['text', 'not null'],
        'appkey' => ['text', 'not null'],
        'appsecret' => ['text', 'not null'],
        'appurl' => ['text', 'not null'],
        'type' => ['integer'],
        'auth' => ['integer'],
    ];
    protected $_pk = ['id'];
    protected $_ai = 'id';

    public function check($appKey)
    {
        if ($row = $this->where(["appkey = ?"], [$appKey])->fetch()) {
            return [
                'id' => $row['id'],
                'name' => $row['appname'],
                'type' => $row['type'],
                'canGetInfo' => (intval($row['auth']) & 1) && true,
                'canFeed' => (intval($row['auth']) & 2) && true,
                'canGetFriend' => (intval($row['auth']) & 4) && true,
                'canRequestPay' => (intval($row['auth']) & 8) && true,
                'canPay' => (intval($row['auth']) & 16) && true
            ];
        } else {
            return null;
        }
    }

    public function validate($appKey, $appSecret)
    {
        if ($row = $this->where(["appkey = ? and appsecret = ?"], [$appKey, $appSecret])->fetch()) {
            return [
                'id' => $row['id'],
                'name' => $row['appname'],
                'type' => $row['type'],
                'canGetInfo' => (intval($row['auth']) & 1) && true,
                'canFeed' => (intval($row['auth']) & 2) && true,
                'canGetFriend' => (intval($row['auth']) & 4) && true,
                'canRequestPay' => (intval($row['auth']) & 8) && true,
                'canPay' => (intval($row['auth']) & 16) && true
            ];
        } else {
            return null;
        }
    }

    public function getAuthorByAppKey($appKey)
    {
        if ($row = $this->where(["appkey = ?"], [$appKey])->fetch()) {
            return $row['uid'];
        } else {
            return null;
        }
    }

    public function getAuthorByAppId($aid)
    {
        if ($row = $this->where(["id = ?"], [$aid])->fetch()) {
            return $row['uid'];
        } else {
            return null;
        }
    }
}