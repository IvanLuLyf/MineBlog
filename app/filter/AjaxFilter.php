<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/3/3
 * Time: 15:31
 */

namespace MineBlog\Filter;

use BunnyPHP\BunnyPHP;
use BunnyPHP\Filter;

class AjaxFilter extends Filter
{
    public function doFilter($param = []): int
    {
        BunnyPHP::app()->set("tp_ajax", true);
        return self::NEXT;
    }
}