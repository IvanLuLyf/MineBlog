<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/4/10
 * Time: 16:33
 */

namespace MineBlog\Controller;

use BunnyPHP\Controller;
use MineBlog\Model\BlogModel;
use MineBlog\Model\UserModel;

class RssController extends Controller
{
    public function ac_index($page = 1)
    {
        $blogModel = new BlogModel();
        $blogs = $blogModel->getBlogByPage($page);
        $this->assign('title', TP_SITE_NAME);
        $this->assign('site_url', 'http://' . TP_SITE_URL);
        $this->assign('blog_url', 'http://' . TP_SITE_URL);
        foreach ($blogs as &$blog) {
            $blog['pubDate'] = gmdate('r', $blog['timestamp']);
        }
        $this->assign('blogs', $blogs);
        $this->renderTemplate('rss/list.xml');
        header('Content-type: application/xml');
    }

    public function other()
    {
        $blogModel = new BlogModel();
        $username = $this->getAction();
        $user = (new UserModel())->getUserByUsername($username);
        if ($user != null) {
            $blogs = $blogModel->getBlogByUsername($username);
            $this->assign('title', $user['nickname'] . "的博客");
            $this->assign('site_url', 'http://' . TP_SITE_URL);
            $this->assign('blog_url', 'http://' . TP_SITE_URL . '/user/blog/' . $username);
            foreach ($blogs as &$blog) {
                $blog['pubDate'] = gmdate('r', $blog['timestamp']);
            }
            $this->assign('blogs', $blogs);
            $this->renderTemplate('rss/list.xml');
        }
        header('Content-type: application/xml');
    }
}