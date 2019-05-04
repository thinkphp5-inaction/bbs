<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\admin\controller;

use app\common\service\ForumService;
use app\common\service\TopicService;
use think\exception\DbException;
use think\Request;

/**
 * 主题
 * Class Topic
 * @package app\admin\controller
 */
class Topic extends BaseController
{
    protected function _initialize()
    {
        $this->loginRequired();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws DbException
     */
    public function index(Request $request)
    {
        $forumId = $request->param('forum_id');
        $keyword = $request->param('keyword');

        $list = TopicService::Factory()->listWithUserWithForum($forumId, $keyword);
        $forums = ForumService::Factory()->all();
        $this->assign('forums', $forums);
        $this->assign('list', $list);
        return $this->fetch();
    }
}