<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/4/10
 * Time: 16:33
 */

class RssController extends Controller
{
    public function ac_index()
    {
        header('Content-type: application/xml');
        $blogModel = new BlogModel();
        $blogs = $blogModel->getBlogByPage(1);
        $site_name = TP_SITE_NAME;
        $site_url = 'http://' . TP_SITE_URL;
        echo '<?xml version="1.0" encoding="utf-8"?><rss version="2.0">';
        echo '<channel>';
        echo "<title><![CDATA[{$site_name}]]></title><description><![CDATA[{$site_name}]]></description><link>{$site_url}</link><language>zh-cn</language><generator>MineBlog</generator>";
        foreach ($blogs as $blog) {
            $link = "{$site_url}/blog/view/{$blog['tid']}";
            $pubDate = gmdate('r', $blog['timestamp']);
            echo "<item><title>{$blog['title']}</title><link>$link</link><description><![CDATA[{$blog['summary']}]]></description><pubDate>$pubDate</pubDate><author>{$blog['nickname']}</author><guid>$link</guid></item>";
        }
        echo '</channel></rss>';
    }

    public function other()
    {
        header('Content-type: application/xml');
        $blogModel = new BlogModel();
        $username = $this->getAction();
        $user = (new UserModel())->getUserByUsername($username);
        if ($user != null) {
            $blogs = $blogModel->getBlogByUsername($username);
            $title = $user['nickname'] . "的博客";
            $site_url = 'http://' . TP_SITE_URL;
            $blog_url = $site_url . '/user/blog/' . $username;
            echo '<?xml version="1.0" encoding="utf-8"?><rss version="2.0">';
            echo '<channel>';
            echo "<title><![CDATA[{$title}]]></title><description><![CDATA[{$title}]]></description><link>{$blog_url}</link><language>zh-cn</language><generator>MineBlog</generator>";
            foreach ($blogs as $blog) {
                $link = "{$site_url}/blog/view/{$blog['tid']}";
                $pubDate = gmdate('r', $blog['timestamp']);
                echo "<item><title>{$blog['title']}</title><link>$link</link><description><![CDATA[{$blog['summary']}]]></description><pubDate>$pubDate</pubDate><author>{$blog['nickname']}</author><guid>$link</guid></item>";
            }
            echo '</channel></rss>';
        }
    }
}