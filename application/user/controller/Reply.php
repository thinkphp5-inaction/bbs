<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\user\controller;

use app\common\service\ReplyService;
use think\exception\DbException;

/**
 * 回复管理
 * Class Reply
 * @package app\user\controller
 */
class Reply extends BaseController
{
    /**
     * 回复列表
     * @return mixed
     * @throws DbException
     */
    public function index()
    {
        $list = ReplyService::Factory()->listWithTopicWithForumByUser($this->userId());
        $this->assign('list', $list);
        return $this->fetch();
    }
}