<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/27
 * Time: 2:20
 */

class SiteController extends Controller
{
    public function ac_build()
    {
        $tp_user = $this->service('user')->getLoginUser();
        if ($tp_user == null) {
            $this->redirect('user', 'login', ['referer' => View::get_url('user', 'blog')]);
            return;
        }
        $localStatic = false;
        $showHeader = false;
        $showFooter = false;
        include APP_PATH . 'library/Parser.php';
        $parser = new HyperDown\Parser;
        $blogs = (new BlogModel())->getBlogByUsername($tp_user['username']);
        $blogId = count($blogs);
        if ($localStatic) {
            $this->storage()->write("${tp_user['username']}/mineblog.css", file_get_contents(APP_PATH . "static/css/markdown.css"));
        }
        foreach ($blogs as $blog) {
            $this->generate(($blogId--), $blog, $parser, $localStatic, $showHeader, $showFooter);
        }
    }

    public function generate($blogId, $blog, $parser, $localStatic = true, $showHeader = true, $showFooter = true)
    {
        $header = '';
        $footer = '';
        $static_path = "./";
        $blogContent = $parser->makeHtml($blog['content']);
        if (!$localStatic) {
            $static_path = "/ipfs/Qma39UmDJ7T2Ns2Bvjoditt1JrjFzML4eXPv7utMupjEUj/";
        }
        if ($showHeader) {
            $blog_date = date('Y-m-d H:i:s', $blog['timestamp']);
            $avatar = "https://www.gravatar.com/avatar/4d7256505dfa251c8ce683bde5592248";
            $header = <<<HTML_HEADER
<div class="user">
<img class="avatar" src="${avatar}"/>
<div class="user-info"><div>${blog['nickname']}</div><div>${blog_date}</div></div>
</div>
HTML_HEADER;
        }
        if ($showFooter) {
            $footer = <<<HTML_FOOTER
<div class="footer"><p>Publish with <img src="${static_path}mineblog.png" class="footer-img"> · Host by <img src="${static_path}ipfs.png" class="footer-img"></p><p>&copy;2018 ${blog['nickname']}. All rights reserved.</p></div>
HTML_FOOTER;
        }
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
<h1>${blog['title']}</h1>
${header}
<div class="markdown-body">${blogContent}</div>
</div>
${footer}
</body></html>
HTML_CONTENT;
        $this->storage()->write("${blog['username']}/${blogId}.html", $result);
    }
}