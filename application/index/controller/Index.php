<?php

namespace app\index\controller;

use app\common\model\Forum;
use app\common\service\ForumService;
use app\common\service\TopicService;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

/**
 * 首页
 * Class Index
 * @package app\index\controller
 */
class Index extends BaseController
{
    /**
     * 首页
     * @return mixed
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function index()
    {
        $forums = ForumService::Factory()->all(Forum::STATUS_VISIBLE);
        $topics = TopicService::Factory()->listLatest(30);
        $this->assign('forums', $forums);
        $this->assign('topics', $topics);
        return $this->fetch();
    }
}
