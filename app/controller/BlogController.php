<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/29
 * Time: 3:24
 */

namespace MineBlog\Controller;

use BunnyPHP\BunnyPHP;
use BunnyPHP\Config;
use BunnyPHP\Controller;
use BunnyPHP\Language;
use HyperDown\Parser;
use MineBlog\Model\BlogModel;
use MineBlog\Model\CommentModel;
use MineBlog\Service\OauthService;
use MineBlog\Service\UserService;

class BlogController extends Controller
{

    public function ac_index()
    {
        $this->redirect('blog', 'list');
    }

    /**
     * @filter csrf
     * @filter auth
     */
    public function ac_create_get()
    {
        $this->renderTemplate('blog/create.html');
    }

    /**
     * @filter csrf check
     * @filter auth canFeed
     */
    public function ac_create_post()
    {
        if (isset($_POST['title']) && isset($_POST['content'])) {
            $summary = isset($_POST['summary']) ? $_POST['summary'] : $_POST['title'];
            $tid = (new BlogModel())->sendBlog(BunnyPHP::app()->get('tp_user'), $_POST['title'], $_POST['content'], $summary);
            BunnyPHP::getCache()->del('blog_list');
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $this->redirect('blog', 'view', ['tid' => $tid]);
            } elseif ($this->_mode == BunnyPHP::MODE_API) {
                $this->assign('ret', 0)->assign('status', 'ok')->assign('tid', $tid)->render();
            }
        } else {
            $this->assign('ret', -7)->assign('status', 'parameter cannot be empty')->assign('tp_error_msg', Language::get('param_required'))->error();
        }
    }

    /**
     * @param int $tid path(0,0)
     * @param UserService $userService
     */
    public function ac_view(UserService $userService, int $tid = 0)
    {
        $blog = (new BlogModel())->getBlogById($tid);
        $tp_user = $userService->getLoginUser();
        if ($blog != null) {
            if ($blog['visible'] == 0 || ($blog['visible'] > 0 && $tp_user['username'] == $blog['username'])) {
                $comments = (new CommentModel())->listComment($tid);
                if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                    $oauth = [];
                    if (Config::check("oauth")) {
                        $oauth = Config::load('oauth')->get('enabled', []);
                        $sl = Config::load('oauth')->get('shares');
                        $share = new OauthService($this);
                        $shares = [];
                        foreach ($sl as $item) {
                            $shares[] = ['name' => $item, 'url' => $share->share_url($item, 'https://' . $_SERVER["HTTP_HOST"] . "/blog/view/{$blog['tid']}", $blog['title'])];
                        }
                        $this->assign('shares', $shares);
                    }
                    $this->assign('oauth', $oauth);
                    $this->assign('tp_user', $tp_user);
                    if (isset($_SESSION['oauth_user'])) {
                        $this->assign('oauth_user', $_SESSION['oauth_user']);
                    }
                    $cache = BunnyPHP::getCache();
                    if ($cache->has('blog_' . $tid)) {
                        $html_content = $cache->get('blog_' . $tid);
                    } else {
                        $parser = new Parser();
                        $html_content = $parser->makeHtml($blog['content']);
                        $cache->set('blog_' . $tid, $html_content);
                    }
                    $this->assign('cur_ctr', 'blog')->assign("html_content", $html_content);
                }
                $this->assignAll(['ret' => 0, 'status' => 'ok', 'blog' => $blog, 'comments' => $comments])->renderTemplate('blog/view.html');
            } else {
                $this->assignAll(['ret' => 3002, 'status' => 'permission denied', 'tp_error_msg' => Language::get('blog_not_permit')])->error();
            }
        } else {
            $this->assignAll(['ret' => 3001, 'status' => 'invalid tid', 'tp_error_msg' => Language::get('blog_not_exist')])->error();
        }
    }

    /**
     * @param int $page path(0,1)
     * @param UserService $userService
     */
    function ac_list(UserService $userService, int $page = 1)
    {
        $blogModel = new BlogModel();
        $recommend_blogs = $blogModel->getRecommendBlog();
        $blogs = $blogModel->getBlogByPage($page);
        $total = $blogModel->getTotal();

        $endPage = ceil($total / 10);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            $this->assignAll(['tp_user' => $userService->getLoginUser(), 'cur_ctr' => 'blog', 'end_page' => $endPage]);
        }
        $this->assign("recommend_blogs", $recommend_blogs);
        $this->assignAll(['ret' => 0, 'status' => 'ok', "page" => $page, 'total' => $total, "blogs" => $blogs])->renderTemplate('blog/list.html');
    }

    /**
     * @filter auth
     * @param int $tid path(0,0)
     */
    function ac_comment(int $tid = 0)
    {
        $blog = (new BlogModel())->getBlogById($tid);
        if ($blog != null) {
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                (new CommentModel())->sendComment($tid, BunnyPHP::app()->get('tp_user'), $_POST['content']);
                $this->redirect('blog', 'view', ['tid' => $tid]);
            }
        } else {
            $this->assignAll(['ret' => 3001, 'status' => 'invalid tid', 'tp_error_msg' => Language::get('blog_not_exist')])->error();
        }
    }

    /**
     * @param int $tid path(0,0)
     */
    function ac_o_comment(int $tid = 0)
    {
        $blog = (new BlogModel())->getBlogById($tid);
        if ($blog != null) {
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $oauth_user = BunnyPHP::getRequest()->getSession('oauth_user');
                if ($oauth_user) {
                    (new CommentModel())->sendComment($tid, ['username' => $oauth_user['uid'], 'nickname' => $oauth_user['nickname']], $_POST['content'], $oauth_user['type']);
                    $this->redirect('blog', 'view', ['tid' => $tid]);
                }
            }
        } else {
            $this->assignAll(['ret' => 3001, 'status' => 'invalid tid', 'tp_error_msg' => Language::get('blog_not_exist')])->error();
        }
    }

    function ac_search($word, UserService $userService, $page = 1, $limit = 5)
    {
        if ($word) {
            $result = (new BlogModel())->searchBlog($word, $page, 0, $limit);
            $endPage = ceil($result['total'] / $limit);
        } else {
            $result = ['total' => 0, 'blogs' => []];
            $endPage = 0;
        }
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            $this->assignAll(['tp_user' => $userService->getLoginUser(), 'cur_ctr' => 'blog', 'end_page' => $endPage]);
        }
        $this->assignAll(['ret' => 0, 'status' => 'ok', 'word' => $word, "page" => $page, 'total' => $result['total'], "blogs" => $result['blogs']])->renderTemplate('blog/search.html');
    }

    /**
     * @filter ajax
     * @filter api
     * @filter auth
     */
    public function ac_image_post()
    {
        $tp_user = BunnyPHP::app()->get('tp_user');
        if (isset($_FILES['file'])) {
            $image_type = ['image/bmp', 'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'application/x-bmp', 'application/x-jpg', 'application/x-png'];
            if (in_array($_FILES["file"]["type"], $image_type) && ($_FILES["file"]["size"] < 2000000)) {
                $t = time() % 1000;
                $url = BunnyPHP::getStorage()->upload("blog/" . $tp_user['uid'] . '_' . $t . ".jpg", $_FILES["file"]["tmp_name"]);
                $response = ['ret' => 0, 'status' => 'ok', 'url' => $url];
            } else {
                $response = ['ret' => 2, 'status' => 'invalid file'];
            }
            $this->assignAll($response);
        }
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {

        } else {
            $this->render('blog/image.html');
        }
    }
}