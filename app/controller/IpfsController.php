<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/2/28
 * Time: 17:26
 */

class IpfsController extends Controller
{
    /**
     * @filter auth
     * @param array $path
     */
    public function ac_publish(array $path)
    {
        $tid = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : (isset($path[0]) ? $path[0] : 0);
        $blog = (new BlogModel())->getBlogById($tid);
        $tp_user = BunnyPHP::app()->get("tp_user");
        if ($tp_user != null && $blog != null) {
            if ($tp_user['username'] == $blog['username']) {
                $static_path = '/ipfs/Qma39UmDJ7T2Ns2Bvjoditt1JrjFzML4eXPv7utMupjEUj/';
                $blog_date = date('Y-m-d H:i:s', $blog['timestamp']);
                $avatar = 'https://' . $_SERVER["HTTP_HOST"] . "/user/avatar?username={$blog['username']}";
                include APP_PATH . 'library/Parser.php';
                $parser = new HyperDown\Parser;
                $blogContent = $parser->makeHtml($blog['content']);
                $result = <<<HTML_CONTENT
<html>
<head>
<title>${blog['title']}</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0">
<link href="${static_path}mineblog.css" rel="stylesheet">
</head>
<body>
<div class="article">
<h3>${blog['title']}</h3>
<div class="user">
<img class="avatar" src="${avatar}"/>
<div class="user-info"><div>${blog['nickname']}</div><div>${blog_date}</div></div>
</div>
<div class="markdown-body">${blogContent}</div>
</div>
<div class="footer"><p>Publish with <a href="https://github.com/IvanLuLyf/MineBlog"><img src="${static_path}mineblog.png" class="footer-img"></a> · Host by <a href="https://ipfs.io"><img src="${static_path}ipfs.png" class="footer-img"></a></p><p>&copy;2019 ${blog['nickname']}. All rights reserved.</p></div>
</body></html>
HTML_CONTENT;
                $url = (new IpfsStorage([]))->write('', $result);
                $this->redirect($url);
            } else {
                $this->assign('ret', 4002)->assign('status', 'permission denied')->assign('tp_error_msg', "没有访问权限")
                    ->render('common/error.html');
            }
        } else {
            $this->assign('ret', 3001)->assign('status', 'invalid tid')->assign('tp_error_msg', "博客不存在")
                ->render('common/error.html');
        }
    }

    public function other(array $path)
    {
        $extra = '';
        if (count($path) > 0) {
            $extra = '/' . implode('/', $path);
        }
        $url = "https://ipfs.infura.io/ipfs/" . $this->getAction() . $extra;
        $this->redirect($url);
    }
}