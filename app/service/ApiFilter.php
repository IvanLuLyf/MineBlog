<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/1/1
 * Time: 23:26
 */
class ApiFilter extends Filter
{
    public function doFilter()
    {
        if ($this->_mode == BunnyPHP::MODE_API) {
            if (isset($_POST['appkey']) && isset($_POST['appsecret'])) {
                $appKey = $_POST['appkey'];
                $appSecret = $_POST['appsecret'];
                if (($apiInfo = (new ApiModel())->validate($appKey, $appSecret)) != null) {
                    if ($apiInfo['type'] == 1) {
                        return self::NEXT;
                    } else {
                        $this->error(['ret' => 2002, 'status' => 'permission denied']);
                    }
                } else {
                    $this->error(['ret' => 2001, 'status' => 'invalid appkey']);
                }
            } else {
                $this->error(['ret' => 1004, 'status' => 'empty arguments']);
            }
            return self::STOP;
        }
        return self::NEXT;
    }
}