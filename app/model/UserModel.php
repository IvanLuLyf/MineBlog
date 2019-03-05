<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/29
 * Time: 1:27
 */

class UserModel extends Model
{
    protected $_column = [
        'uid' => ['integer', 'not null'],
        'username' => ['varchar(16)', 'not null'],
        'password' => ['varchar(32)', 'not null'],
        'nickname' => ['varchar(32)'],
        'email' => ['text', 'not null'],
        'token' => ['text', 'not null'],
        'expire' => ['text']
    ];
    protected $_pk = ['uid'];
    protected $_ai = 'uid';

    public function getUsers($page = 1)
    {
        return $this->limit(10, ($page - 1) * 10)->fetchAll();
    }

    public function refresh($uid)
    {
        $user = $this->where("uid = :u", ['u' => $uid])->fetch();
        $timestamp = time();
        if ($user['expire'] == null || $timestamp > intval($user['expire'])) {
            $token = md5($user['uid'] . $user['username'] . $timestamp);
            $updates = ['token' => $token, 'expire' => $timestamp + 604800];
            $this->where(["uid = :uid"], ['uid' => $uid])->update($updates);
        } else {
            $token = $user['token'];
        }
        return $token;
    }

    public function login(string $username, string $password)
    {
        $user = $this->where("username = :u or email = :e", ['u' => $username, 'e' => $username])->fetch();
        if ($user != null) {
            if ($user['password'] == md5($password)) {
                $timestamp = time();
                $uid = $user['uid'];
                if ($user['expire'] == null || $timestamp > intval($user['expire'])) {
                    $token = md5($user['uid'] . $user['username'] . $timestamp);
                    $updates = ['token' => $token, 'expire' => $timestamp + 604800];
                    $this->where(["uid = :uid"], ['uid' => $uid])->update($updates);
                } else {
                    $token = $user['token'];
                }
                $response = ['ret' => 0, 'status' => 'ok', 'uid' => $uid, 'username' => $user['username'], 'email' => $user['email'], 'token' => $token, 'nickname' => $user['nickname'], 'expire' => $timestamp + 604800];
            } else {
                $response = ['ret' => 1001, 'status' => "password error", 'tp_error_msg' => "密码错误"];
            }
        } else {
            $response = ['ret' => 1002, 'status' => "user not exists", 'tp_error_msg' => "用户名不存在"];
        }
        return $response;
    }

    public function register($username, $password, $email, $nickname = '')
    {
        if (isset($password) && isset($email)) {
            if (preg_match('/^[A-Za-z0-9_]+$/u', $username) && strlen($username) >= 4) {
                if ($this->where("username = :u or email = :e", ['u' => $username, 'e' => $email])->fetch()) {
                    $response = ['ret' => 1003, 'status' => "username exists", 'tp_error_msg' => "用户名已存在"];
                } else {
                    if ($nickname == '') {
                        $nickname = $username;
                    }
                    $timestamp = time();
                    $token = md5($password . $username . $timestamp);
                    $new_data = ['username' => $username, 'email' => $email, 'password' => md5($password), 'nickname' => $nickname, 'token' => $token, 'expire' => $timestamp + 604800];
                    if ($uid = $this->add($new_data)) {
                        $response = ['ret' => 0, 'status' => 'ok', 'uid' => $uid, 'username' => $username, 'email' => $email, 'token' => $token, 'nickname' => $nickname];
                    } else {
                        $response = ['ret' => 1006, 'status' => "database error", 'tp_error_msg' => "数据库内部出错"];
                    }
                }
            } else {
                $response = ['ret' => 1005, 'status' => "invalid username", 'tp_error_msg' => "用户名仅能为字母数字且长度大于4"];
            }
        } else {
            $response = ['ret' => 1004, 'status' => "empty arguments", 'tp_error_msg' => "参数不能为空"];
        }
        return $response;
    }

    public function check($token)
    {
        $user = $this->where(["token = ? and expire> ?"], [$token, time()])->fetch();
        return $user;
    }

    public function getUserByUid($uid)
    {
        $user = $this->where("uid = ?", [$uid])->fetch();
        $response = [
            'uid' => $uid,
            'username' => $user['username'],
            'email' => $user['email'],
            'nickname' => $user['nickname']
        ];
        return $response;
    }

    public function getUserByUsername($username)
    {
        $user = $this->where("username = ?", [$username])->fetch();
        $response = [
            'uid' => $user['uid'],
            'username' => $user['username'],
            'email' => $user['email'],
            'nickname' => $user['nickname']
        ];
        return $response;
    }

    public function getTokenByUid($uid)
    {
        if ($user = $this->where("uid = ?", [$uid])->fetch()) {
            return $user['token'];
        } else {
            return null;
        }
    }
}