<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/25
 * Time: 0:52
 */

class CommentModel extends Model
{
    public function listComment($tid, $page = 1)
    {
        return $this->where('tid = :t', ['t' => $tid])->limit(20, ($page - 1) * 20)->fetchAll();
    }

    public function sendComment($tid, $user, $content)
    {
        if ($user != null && $tid != null && $content != null) {
            $comment = ['tid' => $tid, 'username' => $user['username'], 'nickname' => $user['nickname'], 'content' => $content, 'timestamp' => time()];
            return $this->add($comment);
        } else {
            return -1;
        }
    }
}