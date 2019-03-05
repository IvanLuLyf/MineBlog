<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/30
 * Time: 0:09
 */

class BlogModel extends Model
{
    protected $_column = [
        'tid' => ['integer', 'not null'],
        'username' => ['varchar(16)', 'not null'],
        'nickname' => ['varchar(32)'],
        'title' => ['text', 'not null'],
        'content' => ['text', 'not null'],
        'recommend' => ['varchar(1)', 'default 0'],
        'summary' => ['varchar(60)'],
        'timestamp' => ['text'],
        'visible' => ['integer', 'default 0'],
        'view_num' => ['integer', 'default 0'],
        'comment_num' => ['integer', 'default 0'],
        'like_num' => ['integer', 'default 0'],
    ];
    protected $_pk = ['tid'];
    protected $_ai = 'tid';

    public function getBlogByPage($page = 1, $visible = 0, $size = 10)
    {
        return $this->where('visible=:v', ['v' => $visible])->order(['tid desc'])->limit($size, ($page - 1) * $size)->fetchAll();
    }

    public function getRecommendBlog($page = 1, $visible = 0, $size = 10)
    {
        return $this->where('visible=:v and recommend=1', ['v' => $visible])->order(['tid desc'])->limit($size, ($page - 1) * $size)->fetchAll(['tid', 'title']);
    }

    public function getTotal()
    {
        return $this->fetch("count(*) num")['num'];
    }

    public function searchBlog($word, $page = 1, $visible = 0, $size = 10)
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

    public function sendBlog($user, $title, $content, $summary = '', $visible = 0)
    {
        if ($user != null && $title != null && $content != null) {
            $blog = ['username' => $user['username'], 'nickname' => $user['nickname'], 'title' => $title, 'content' => $content, 'summary' => $summary, 'timestamp' => time(), 'visible' => $visible];
            return $this->add($blog);
        } else {
            return -1;
        }
    }
}