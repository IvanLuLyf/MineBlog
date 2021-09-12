<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/11/12
 * Time: 1:22
 */

namespace MineBlog\Filter;

use BunnyPHP\BunnyPHP;
use BunnyPHP\Filter;
use MineBlog\Model\UserModel;

class AuthFilter extends Filter
{
    public function doFilter($param = []): int
    {
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            $token = BunnyPHP::getRequest()->getSession('token');
            if (!$token) $token = BunnyPHP::getRequest()->getHeader('token');
            if ($token) {
                $user = (new UserModel)->check($token);
                if ($user != null) {
                    BunnyPHP::app()->set('tp_user', $user);
                    $this->assign('tp_user', $user);
                    return self::NEXT;
                } else {
                    $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
                }
            } else {
                $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
            }
        } elseif ($this->_mode == BunnyPHP::MODE_API) {
            if (isset($_POST['client_id']) && isset($_POST['token'])) {
                $appKey = $_POST['client_id'];
                $appToken = $_POST['token'];
                if ($apiInfo = (new ApiModel())->check($appKey)) {
                    if ($apiInfo['type'] == 1 || $param[0] == '' || $apiInfo[$param[0]] == true) {
                        $userId = (new OauthTokenModel())->check($appKey, $appToken);
                        if ($userId != 0) {
                            $user = (new UserModel)->getUserByUid($userId);
                            BunnyPHP::app()->set('tp_user', $user);
                            BunnyPHP::app()->set('tp_api', $apiInfo);
                            return self::NEXT;
                        } else {
                            $this->error(['ret' => 2003, 'status' => 'invalid token']);
                        }
                    } else {
                        $this->error(['ret' => 2002, 'status' => 'permission denied']);
                    }
                } else {
                    $this->error(['ret' => 2001, 'status' => 'invalid client id']);
                }
            } else {
                $this->error(['ret' => -7, 'status' => 'parameter cannot be empty']);
            }
        } elseif ($this->_mode == BunnyPHP::MODE_AJAX) {
            if (BunnyPHP::app()->get("tp_ajax") === true) {
                $token = BunnyPHP::getRequest()->getSession('token');
                if (!$token) $token = BunnyPHP::getRequest()->getHeader('token');
                if ($token) {
                    $user = (new UserModel)->check($token);
                    if ($user != null) {
                        BunnyPHP::app()->set('tp_user', $user);
                        return self::NEXT;
                    } else {
                        $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
                    }
                } else {
                    $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
                }
            } else {
                $this->error(['ret' => 2002, 'status' => 'permission denied']);
            }
        } else {
            $this->error(['ret' => 2002, 'status' => 'permission denied']);
        }
        return self::STOP;
    }
}