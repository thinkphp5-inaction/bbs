<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;

use app\common\BaseObject;
use app\common\common\PublishScoreInterface;
use app\common\helper\ArrayHelper;
use app\common\model\Reply;
use app\common\model\User;
use app\common\repository\ReplyRepository;
use app\common\repository\UserRepository;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\Paginator;

/**
 * 回复业务
 * Class ReplyService
 * @package app\common\service
 */
class ReplyService extends BaseObject implements PublishScoreInterface
{
    /**
     * 发表回复
     * 1. 写入回复信息
     * 2. 更新帖子回复数量
     * 3. 更新用户发帖数与积分
     * 4. 更新版块回复数
     * @param int $userId
     * @param array $data
     * @return Reply
     */
    public function publish($userId, array $data)
    {
        $data = ArrayHelper::filter($data, ['content', 'topic_id', 'forum_id']);
        $data['user_id'] = $userId;
        return Db::transaction(function () use ($data, $userId) {
            // 写入回复
            /** @var Reply $reply */
            $reply = ReplyRepository::Factory()->insert($data);
            $topic = TopicService::Factory()->addReplyCount($reply->topic_id, 1);
            // 用户用户发帖数和积分
            $score = $this->publishScore();
            /** @var User $user */
            $user = UserRepository::Factory()->findOne(['user_id' => $userId]);
            if (empty($user)) {
                throw new Exception('用户不存在');
            }
            $user->thread_count++;
            $user->score += $score;
            $user->save();
            UserScoreLogService::Factory()->log($userId, $score, $user->score, '发表回复');
            // 更新版块回复数
            ForumService::Factory()->addReplyCount($topic->forum_id, 1);
            return $reply;
        });
    }

    /**
     * 删除回复
     * 1. 检测用户是否发帖人或版主
     * 2. 删除回复
     * 3. 减少帖子回复数
     * 4. 减少用户积分和发帖数
     * 5. 减少版块帖子数
     * @param int $userId
     * @param int $replyId
     * @return mixed
     */
    public function delete($userId, $replyId)
    {
        return Db::transaction(function () use ($userId, $replyId) {
            /** @var Reply $reply */
            $reply = ReplyRepository::Factory()->findOne(['reply_id' => $replyId]);
            if (empty($reply)) {
                throw new Exception('回复不存在');
            }
            if (!$this->shouldAccess($userId, $reply)) {
                throw new Exception('您无权删除');
            }
            if (!$reply->delete()) {
                throw new Exception('删除失败');
            }
            // 减少主题回复数
            TopicService::Factory()->addReplyCount($reply->topic_id, -1);
            // 发帖人积分和发帖数处理
            $score = $this->publishScore();
            /** @var User $user */
            $user = UserRepository::Factory()->findOne(['user_id' => $reply->user_id]);
            $user->thread_count--;
            $user->score -= $score;
            $user->save();
            $msg = $userId == $reply->user_id ? '删除回复' : '回复被版主删除';
            UserScoreLogService::Factory()->log($userId, -$score, $user->score, $msg);
            // 更新版块回复数
            ForumService::Factory()->addReplyCount($reply->forum_id, -1);
        });
    }

    /**
     * 删除主题回复
     * @param int $topicId
     * @return int
     */
    public function deleteByTopic($topicId)
    {
        return Reply::destroy(['topic_id' => $topicId]);
    }

    /**
     * 根据主题获取回复列表+用户
     * @param int $topicId
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithUserByTopic($topicId, $size = 10)
    {
        return ReplyRepository::Factory()->listWithUserByTopic($topicId, $size);
    }

    /**
     * 根据用户获取回复列表+主题
     * @param int $userId
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithTopicWithForumByUser($userId, $size = 10)
    {
        return ReplyRepository::Factory()->listWithTopicWithForumByUser($userId, $size);
    }


    /**
     * 编辑回复
     * @param int $userId
     * @param int $replyId
     * @param $content
     * @return mixed|Reply
     * @throws DbException
     * @throws Exception
     */
    public function update($userId, $replyId, $content)
    {
        /** @var Reply $reply */
        $reply = ReplyRepository::Factory()->findOne(['reply_id' => $replyId]);
        if (empty($reply)) {
            throw new Exception('回复不存在');
        }
        if (!$this->shouldAccess($userId, $reply)) {
            throw new Exception('您无权编辑');
        }
        $reply->content = $content;
        $reply->save();
        return $reply;
    }


    /**
     * 是否有权限操作
     * @param int $userId
     * @param Reply $reply
     * @return bool
     * @throws DbException
     */
    public function shouldAccess(int $userId, Reply $reply): bool
    {
        return $userId == $reply->user_id || ForumAdminService::Factory()->isAdmin($userId, $reply->forum_id);
    }

    /**
     * 获取积分
     * @return int
     */
    public function publishScore(): int
    {
        return config('app.score.publish_reply');
    }

    /**
     * 检测是否回复
     * @param int $topicId
     * @param int $userId
     * @return bool
     * @throws DbException
     */
    public function hasReplied($topicId, $userId)
    {
        return ReplyRepository::Factory()->findOne(['topic_id' => $topicId, 'user_id' => $userId]) != null;
    }

    public function showWithTopicWithForumByUser($replyId, $userId)
    {
        return ReplyRepository::Factory()->showWithTopicWithForumByUser($replyId, $userId);
    }
}