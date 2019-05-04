<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\index\controller;


use app\common\service\ForumAdminService;
use app\common\service\ForumService;
use app\common\service\TopicService;
use think\Exception;
use think\Request;

class Forum extends BaseController
{
    /**
     * 版块详情，显示版块简介，版主列表，帖子列表
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request)
    {
        if (empty($request->param('id'))) {
            $this->error('您的请求有误!');
        }
        try {
            $forum = ForumService::Factory()->show($request->param('id'), \app\common\model\Forum::STATUS_VISIBLE);
            $admins = ForumAdminService::Factory()->listByForum($forum->forum_id);
            $topics = TopicService::Factory()->listWithUserByForum($forum->forum_id, 10);

            $this->assign('forum', $forum);
            $this->assign('admins', $admins);
            $this->assign('topics', $topics);
            return $this->fetch();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}