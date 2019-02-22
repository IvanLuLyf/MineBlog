<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/30
 * Time: 0:09
 */

class BlogModel extends Model
{
    public function getBlogByPage($page = 1, $visible = 0, $size = 5)
    {
        return $this->where('visible=:v', ['v' => $visible])->order(['tid desc'])->limit($size, ($page - 1) * $size)->fetchAll();
    }

    public function getTotal()
    {
        return $this->fetch("count(*) num")['num'];
    }

    public function searchBlog($word, $page = 1, $visible = 0, $size = 5)
    {
        $blogs = $this->where('visible=:v and content like :c', ['v' => $visible, 'c' => "%$word%"])->order(['tid desc'])->limit($size, ($page - 1) * $size)->fetchAll();
        $total = $this->where('visible=:v and content like :c', ['v' => $visible, 'c' => "%$word%"])->fetch("count(*) num")['num'];
        return ['blogs' => $blogs, 'total' => $total];
    }

    public function getBlogById($id)
    {
        return $this->where("tid=:tid", ['tid' => $id])->fetch();
    }

    public function getBlogByUsername($username, $visible = 0)
    {
        return $this->where("username=:un and visible<=:v", ['un' => $username, 'v' => $visible])->order(['tid desc'])->fetchAll();
    }

    public function sendBlog($user, $title, $content, $visible = 0)
    {
        if ($user != null && $title != null && $content != null) {
            $blog = ['username' => $user['username'], 'nickname' => $user['nickname'], 'title' => $title, 'content' => $content, 'timestamp' => time(), 'visible' => $visible];
            return $this->add($blog);
        } else {
            return -1;
        }
    }
}