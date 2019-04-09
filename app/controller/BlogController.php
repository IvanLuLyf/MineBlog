<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/29
 * Time: 3:24
 */

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
        $this->render('blog/create.html');
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
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $this->redirect('blog', 'view', ['tid' => $tid]);
            } elseif ($this->_mode == BunnyPHP::MODE_API) {
                $this->assign('ret', 0)->assign('status', 'ok')->assign('tid', $tid)->render();
            }
        } else {
            $this->assign('ret', 1004)->assign('status', 'empty arguments')->assign('tp_error_msg', "必要参数为空")->error();
        }
    }

    /**
     * @param $tid
     * @param UserService $userService
     * @path tid 0 0
     */
    public function ac_view($tid, UserService $userService)
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
                    }
                    $this->assign('oauth', $oauth);
                    if (Config::check('oauth')) {
                        $sl = Config::load('oauth')->get('shares');
                        $share = new OauthService($this);
                        $shares = [];
                        foreach ($sl as $item) {
                            $shares[] = ['name' => $item, 'url' => $share->share_url($item, 'https://' . $_SERVER["HTTP_HOST"] . "/blog/view/{$blog['tid']}", $blog['title'])];
                        }
                        $this->assign('shares', $shares);
                    }
                    $this->assign('tp_user', $tp_user);
                    if (isset($_SESSION['oauth_user'])) {
                        $this->assign('oauth_user', $_SESSION['oauth_user']);
                    }
                    include APP_PATH . 'library/Parser.php';
                    $parser = new HyperDown\Parser;
                    $html_content = $parser->makeHtml($blog['content']);
                    $this->assign('cur_ctr', 'blog')->assign("html_content", $html_content);
                }
                $this->assign("blog", $blog)->assign('comments', $comments)
                    ->render('blog/view.html');
            } else {
                $this->assign('ret', 4002)->assign('status', 'permission denied')->assign('tp_error_msg', "没有访问权限")->error();
            }
        } else {
            $this->assign('ret', 3001)->assign('status', 'invalid tid')->assign('tp_error_msg', "博客不存在")->error();
        }
    }

    /**
     * @param $page
     * @param UserService $userService
     * @path page 0 1
     */
    function ac_list($page, UserService $userService)
    {
        $blogModel = new BlogModel();
        $blogs = $blogModel->getBlogByPage($page);
        $recommend_blogs = $blogModel->getRecommendBlog();
        $total = $blogModel->getTotal();
        $endPage = ceil($total / 10);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            $this->assign('tp_user', $userService->getLoginUser())
                ->assign('cur_ctr', 'blog')->assign('end_page', $endPage);
        }
        $this->assign("recommend_blogs", $recommend_blogs);
        $this->assign("page", $page)->assign('total', $total)->assign("blogs", $blogs)
            ->render('blog/list.html');
    }

    /**
     * @param $tid
     * @filter auth
     * @path tid 0 0
     */
    function ac_comment($tid)
    {
        $blog = (new BlogModel())->getBlogById($tid);
        if ($blog != null) {
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $cid = (new CommentModel())->sendComment($tid, BunnyPHP::app()->get('tp_user'), $_POST['content']);
                $this->redirect('blog', 'view', ['tid' => $tid]);
            }
        } else {
            $this->assign('ret', 4001);
            $this->assign('status', 'blog not found');
            $this->assign('tp_error_msg', "博客不存在");
            $this->render('common/error.html');
        }
    }

    /**
     * @param $tid
     * @path tid 0 0
     */
    function ac_o_comment($tid)
    {
        $blog = (new BlogModel())->getBlogById($tid);
        if ($blog != null) {
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $oauth_user = BunnyPHP::getRequest()->getSession('oauth_user');
                if ($oauth_user) {
                    $cid = (new CommentModel())->sendComment($tid, ['username' => $oauth_user['uid'], 'nickname' => $oauth_user['nickname']], $_POST['content'], $oauth_user['type']);
                    $this->redirect('blog', 'view', ['tid' => $tid]);
                }
            }
        } else {
            $this->assign('ret', 4001);
            $this->assign('status', 'blog not found');
            $this->assign('tp_error_msg', "博客不存在");
            $this->render('common/error.html');
        }
    }

    function ac_search(UserService $userService)
    {
        if (isset($_REQUEST['word']) && $_REQUEST['word'] != '') {
            $word = $_REQUEST['word'];
            $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            $result = (new BlogModel())->searchBlog($word, $page);
            $endPage = ceil($result['total'] / 5);
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $this->assign('tp_user', $userService->getLoginUser())
                    ->assign('cur_ctr', 'blog')->assign('end_page', $endPage);
            }
            $this->assign('word', $word);
            $this->assign("page", $page)->assign('total', $result['total'])->assign("blogs", $result['blogs'])
                ->render('blog/search.html');
        } else {
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $this->assign('word', '');
                $this->assign('total', 0)->assign("blogs", []);
                $this->assign('tp_user', $userService->getLoginUser())->render('blog/search.html');
            }
        }
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
                $url = $this->storage()->upload("blog/" . $tp_user['uid'] . '_' . $t . ".jpg", $_FILES["file"]["tmp_name"]);
                $response = array('ret' => 0, 'status' => 'ok', 'url' => $url);
            } else {
                $response = array('ret' => 1007, 'status' => 'wrong file');
            }
            $this->assignAll($response);
        }
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {

        } else {
            $this->render('blog/image.html');
        }
    }
}