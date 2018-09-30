<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/30
 * Time: 0:09
 */

class BlogModel extends Model
{
    public function getBlogByPage($page = 1, $size = 5)
    {
        return $this->order(['tid desc'])->limit($size, ($page - 1) * $size)->fetchAll();
    }

    public function getBlogById($id)
    {
        return $this->where("tid=:tid", ['tid' => $id])->fetch();
    }

    public function sendBlog($user, $title, $message)
    {
        if ($user != null && $title != null && $message != null) {
            $blog = ['username' => $user['username'], 'nickname' => $user['nickname'], 'title' => $title, 'message' => $message, 'timestamp' => time()];
            return $this->add($blog);
        } else {
            return -1;
        }
    }
}