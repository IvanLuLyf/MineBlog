<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/25
 * Time: 0:52
 */

class CommentModel extends Model
{
    protected $_column = [
        'cid' => ['integer', 'not null'],
        'tid' => ['integer', 'not null'],
        'type' => ['text'],
        'username' => ['text', 'not null'],
        'nickname' => ['text'],
        'content' => ['text', 'not null'],
        'timestamp' => ['text'],
    ];
    protected $_pk = ['cid'];
    protected $_ai = 'cid';

    public function listComment($tid, $page = 1)
    {
        return $this->where('tid = :t', ['t' => $tid])->limit(20, ($page - 1) * 20)->fetchAll();
    }

    public function sendComment($tid, $user, $content, $type = '')
    {
        if ($user != null && $tid != null && $content != null) {
            $comment = ['tid' => $tid, 'type' => $type, 'username' => $user['username'], 'nickname' => $user['nickname'], 'content' => $content, 'timestamp' => time()];
            return $this->add($comment);
        } else {
            return -1;
        }
    }
}