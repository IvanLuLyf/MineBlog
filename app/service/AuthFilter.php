<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/11/12
 * Time: 1:22
 */

class AuthFilter extends Filter
{
    public function doFilter($fa = [])
    {
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            session_start();
            if (isset($_SESSION['token']) && $_SESSION['token'] != "") {
                $user = (new UserModel)->check($_SESSION["token"]);
                if ($user != null) {
                    BunnyPHP::app()->set('tp_user', $user);
                    return self::NEXT;
                } else {
                    $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
                }
            } else {
                $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
            }
        } elseif ($this->_mode == BunnyPHP::MODE_API) {
            if (isset($_POST['appkey']) && isset($_POST['token'])) {
                $appKey = $_POST['appkey'];
                $appToken = $_POST['token'];
                if ($apiInfo = (new ApiModel())->check($appKey)) {
                    if ($apiInfo['type'] == 1 || $fa[0] == '' || $apiInfo[$fa[0]] == true) {
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
                    $this->error(['ret' => 2001, 'status' => 'invalid appkey']);
                }
            } else {
                $this->error(['ret' => 1004, 'status' => 'empty arguments']);
            }
        } else {
            $this->error(['ret' => -1, 'status' => 'not permission']);
        }
        return self::STOP;
    }
}