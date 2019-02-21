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
     * @filter auth
     */
    public function ac_create_get()
    {
        $this->assign('tp_user', BunnyPHP::app()->get('tp_user'));
        $this->render('blog/create.html');
    }

    /**
     * @filter auth canFeed
     */
    public function ac_create_post()
    {
        if (isset($_POST['title']) && isset($_POST['content'])) {
            $tid = (new BlogModel())->sendBlog(BunnyPHP::app()->get('tp_user'), $_POST['title'], $_POST['content']);
            if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                $this->redirect('blog', 'view', ['tid' => $tid]);
            } elseif ($this->_mode == BunnyPHP::MODE_API) {
                $this->assign('ret', 0)->assign('status', 'ok')->assign('tid', $tid)->render();
            }
        } else {
            $this->assign('ret', 1004)->assign('status', 'empty arguments')->assign('tp_error_msg', "必要参数为空")
                ->render('common/error.html');
        }
    }

    public function ac_view(array $path, UserService $userService)
    {
        $tid = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : (isset($path[0]) ? $path[0] : 0);
        $blog = (new BlogModel())->getBlogById($tid);
        $tp_user = $userService->getLoginUser();
        if ($blog != null) {
            if ($blog['visible'] == 0 || ($blog['visible'] > 0 && $tp_user['username'] == $blog['username'])) {
                $comments = (new CommentModel())->listComment($tid);
                if ($this->_mode == BunnyPHP::MODE_NORMAL) {
                    $this->assign('tp_user', $tp_user);
                    include APP_PATH . 'library/Parser.php';
                    $parser = new HyperDown\Parser;
                    $html_content = $parser->makeHtml($blog['content']);
                    $this->assign('cur_ctr', 'blog')->assign("html_content", $html_content);
                }
                $this->assign("blog", $blog)->assign('comments', $comments)
                    ->render('blog/view.html');
            } else {
                $this->assign('ret', 4002)->assign('status', 'permission denied')->assign('tp_error_msg', "没有访问权限")
                    ->render('common/error.html');
            }
        } else {
            $this->assign('ret', 3001)->assign('status', 'invalid tid')->assign('tp_error_msg', "博客不存在")
                ->render('common/error.html');
        }
    }

    function ac_list(array $path, UserService $userService)
    {
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : isset($path[0]) ? $path[0] : 1;
        $blogs = (new BlogModel())->getBlogByPage($page);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            include APP_PATH . 'library/Parser.php';
            $parser = new HyperDown\Parser;
            $this->assign('parser', $parser)->assign('tp_user', $userService->getLoginUser())
                ->assign('cur_ctr', 'blog');
        }
        $this->assign("page", $page)->assign("blogs", $blogs)
            ->render('blog/list.html');
    }

    /**
     * @param array $path
     * @filter auth
     */
    function ac_comment(array $path)
    {
        $tid = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : isset($path[0]) ? $path[0] : 0;
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
}