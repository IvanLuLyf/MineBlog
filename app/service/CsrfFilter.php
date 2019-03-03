<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/3/1
 * Time: 15:45
 */

class CsrfFilter extends Filter
{
    public function doFilter($fa = [])
    {
        if ($this->_mode == BunnyPHP::MODE_NORMAL || $this->_mode == BunnyPHP::MODE_AJAX) {
            if (!session_id()) session_start();
            if (in_array('check', $fa)) {
                if (isset($_POST['csrf_token']) && $_POST['csrf_token'] != '' && $_POST['csrf_token'] == $_SESSION['csrf_token']) {
                    unset($_SESSION['csrf_token']);
                    return self::NEXT;
                } else {
                    $this->error(['ret' => 2004, 'status' => 'invalid csrf token', 'tp_error_msg' => '非法的请求操作']);
                    return self::STOP;
                }
            }
            $token = md5(time() . rand(1, 1000));
            $_SESSION['csrf_token'] = $token;
            BunnyPHP::app()->set('csrf_token', $token);
            return self::NEXT;
        } else {
            return self::NEXT;
        }
    }
}