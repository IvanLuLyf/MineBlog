<?php


namespace MineBlog\Filter;


use BunnyPHP\BunnyPHP;
use BunnyPHP\Filter;
use MineBlog\Model\UserModel;

class AdminFilter extends Filter
{
    public function doFilter($param = [])
    {
        $user = BunnyPHP::app()->get('tp_user');
        $token = BunnyPHP::getRequest()->getSession('token');
        if (!$token) $token = BunnyPHP::getRequest()->getHeader('token');
        if ($token) {
            $user = (new UserModel)->check($token);
            if ($user != null && $user['uid'] == 1) {
                return self::NEXT;
            } else {
                $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
            }
        } else {
            $this->redirect('user', 'login', ['referer' => $_SERVER['REQUEST_URI']]);
        }
        return self::STOP;
    }
}