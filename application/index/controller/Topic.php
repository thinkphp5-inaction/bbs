<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\index\controller;

use app\common\service\FavoriteService;
use app\common\service\ForumAdminService;
use app\common\service\ForumService;
use app\common\service\ReplyService;
use app\common\service\TopicService;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Request;

class Topic extends BaseController
{
    protected $guestActions = ['show'];

    protected function _initialize()
    {
        $this->loginRequired();
    }

    /**
     * 发帖表单
     * @param Request $request
     * @return mixed
     */
    public function publish(Request $request)
    {
        $forumId = $request->param('forum_id');
        if (empty($forumId)) {
            $this->error('您的请求有误');
        }
        try {
            $forum = ForumService::Factory()->show($forumId, \app\common\model\Forum::STATUS_VISIBLE);
            $this->assign('forum', $forum);
            return $this->fetch();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 处理发表
     * @param Request $request
     */
    public function do_publish(Request $request)
    {
        $errmsg = $this->validate($request->post(), [
            'forum_id|所属版块' => 'require',
            'title|标题' => 'require|max:100',
            'content|内容' => 'require',
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        $topic = TopicService::Factory()->publish($this->userId(), $request->post());
        $this->redirect('show', ['id' => $topic->topic_id]);
    }

    /**
     * 查看帖子
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request)
    {
        $topicId = $request->param('id');
        if (empty($topicId)) {
            $this->error('您的请求有误');
        }
        try {
            TopicService::Factory()->view($topicId, $request->ip(), $this->userId());
            $topic = TopicService::Factory()->showWithUserWithForum($topicId);
            $replies = ReplyService::Factory()->listWithUserByTopic($topicId);
            $this->assign('topic', $topic);
            $this->assign('replies', $replies);
            $this->assign('firstPage', $request->get('page', 1) == 1);
            $canView = !$topic->flag || ReplyService::Factory()->hasReplied($topicId, $this->userId());
            $canAccess = TopicService::Factory()->shouldAccess($this->userId(), $topic);
            $this->assign('canView', $canView || $canAccess);
            $this->assign('canAccess', $canAccess);
            $this->assign('userId',$this->userId());
            $this->assign('isAdmin', ForumAdminService::Factory()->isAdmin($this->userId(), $topic->forum_id));
            $this->assign('isFavorite', FavoriteService::Factory()->isFavorite($this->userId(), $topicId));
            return $this->fetch();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 编辑主题
     * @param Request $request
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function update(Request $request)
    {
        $topicId = $request->param('id');
        if (empty($topicId)) {
            $this->error('您的请求有误');
        }
        $topic = TopicService::Factory()->showWithForum($topicId);
        if (!TopicService::Factory()->shouldAccess($this->userId(), $topic)) {
            $this->error('您无权操作!');
        }
        $this->assign('topic', $topic);
        $this->assign('reply_visible', $topic->isReplyVisible());
        return $this->fetch();
    }

    /**
     * 处理编辑
     * @param Request $request
     * @throws DbException
     * @throws Exception
     */
    public function do_update(Request $request)
    {
        $topicId = $request->param('id');
        if (empty($topicId)) {
            $this->error('您的请求有误');
        }
        $data = $request->post();
        $errmsg = $this->validate($data, [
            'title|标题' => 'require|max:100',
            'content|内容' => 'require',
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        $data['flag'] = isset($data['flag']) ? $data['flag'] & \app\common\model\Topic::FLAG_REPLY_VISIBLE : 0;
        TopicService::Factory()->update($topicId, $this->userId(), $data);
        $this->success('编辑成功', url('show', ['id' => $topicId]));
    }

    /**
     * 删除帖子
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        $topic = TopicService::Factory()->delete($id, $this->userId());
        $this->success('删除成功', url('forum/show', ['id' => $topic->forum_id]));
    }

    /**
     * 置顶
     * @param Request $request
     */
    public function top(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        TopicService::Factory()->setTop($this->userId(), $id);
        $this->success('操作成功');
    }

    /**
     * 取消置顶
     * @param Request $request
     */
    public function untop(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        TopicService::Factory()->unsetTop($this->userId(), $id);
        $this->success('操作成功');
    }

    /**
     * 收藏
     * @param Request $request
     * @throws DbException
     */
    public function favorite(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        FavoriteService::Factory()->add($this->userId(), $id);
        $this->success('操作成功');
    }

    /**
     * 取消收藏
     * @param Request $request
     */
    public function unfavorite(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        FavoriteService::Factory()->remove($this->userId(), $id);
        $this->success('操作成功');
    }

    /**
     * 回帖
     * @param Request $request
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function reply(Request $request)
    {
        $topicId = $request->param('topic_id');
        if (empty($topicId)) {
            $this->error('您的请求有误');
        }
        $topic = TopicService::Factory()->showWithForum($topicId);
        $this->assign('topic', $topic);
        return $this->fetch();
    }

    /**
     * 回复
     * @param Request $request
     */
    public function do_reply(Request $request)
    {
        $topicId = $request->param('topic_id');
        if (empty($topicId)) {
            $this->error('您的请求有误');
        }
        $data = $request->post();
        $errmsg = $this->validate($data, [
            'forum_id|版块ID' => 'require',
            'content|回复内容' => 'require'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        $data['topic_id'] = $topicId;
        ReplyService::Factory()->publish($this->userId(), $data);
        $this->redirect('topic/show', ['id' => $topicId]);
    }
}