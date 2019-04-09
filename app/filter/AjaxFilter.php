<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/3/3
 * Time: 15:31
 */

class AjaxFilter extends Filter
{
    public function doFilter($fa = [])
    {
        BunnyPHP::app()->set("tp_ajax", true);
        return self::NEXT;
    }
}