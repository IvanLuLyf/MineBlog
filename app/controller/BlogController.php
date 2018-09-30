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

    public function ac_create_get()
    {
        $tp_user = $this->service('user')->getLoginUser();
        if ($tp_user == null) {
            $this->redirect('user', 'login');
            return;
        }
        $this->assign('tp_user', $tp_user);
        $this->render('blog/create.html');
    }

    public function ac_create_post()
    {
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            $tp_user = $this->service('user')->getLoginUser();
            if ($tp_user == null) {
                $this->redirect('user', 'login');
                return;
            }
            $tid = (new BlogModel())->sendBlog($tp_user, $_POST['title'], $_POST['message']);
            $this->redirect("/blog/view/$tid");
        }
    }

    public function ac_view($path = [])
    {
        $tid = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : isset($path[0]) ? $path[0] : 0;
        $blog = (new BlogModel())->getBlogById($tid);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            if ($blog == null) {
                $this->redirect('blog', 'list');
                return;
            }
            $this->assign('tp_user', $this->service('user')->getLoginUser());
            include APP_PATH . 'library/Parser.php';
            $parser = new HyperDown\Parser;
            $html_content = $parser->makeHtml($blog['message']);
            $this->assign('cur_ctr', 'blog');
            $this->assign("html_content", $html_content);
        }
        $this->assign("blog", $blog);
        $this->render('blog/view.html');
    }

    function ac_list($path = [])
    {
        $page = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : isset($path[0]) ? $path[0] : 1;
        $blogs = (new BlogModel())->getBlogByPage($page);
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            include APP_PATH . 'library/Parser.php';
            $parser = new HyperDown\Parser;
            $this->assign('parser', $parser);
            $this->assign('tp_user', $this->service('user')->getLoginUser());
            $this->assign('cur_ctr', 'blog');
        }
        $this->assign("page", $page);
        $this->assign("blogs", $blogs);
        $this->render('blog/list.html');
    }
}