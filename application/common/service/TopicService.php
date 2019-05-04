<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;


use app\common\BaseObject;
use app\common\common\PublishScoreInterface;
use app\common\helper\ArrayHelper;
use app\common\model\Topic;
use app\common\model\User;
use app\common\repository\TopicRepository;
use app\common\repository\UserRepository;
use PDOStatement;
use think\Cache;
use think\Collection;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;
use think\Paginator;

/**
 * 主题业务类
 * Class TopicService
 * @package app\common\service
 */
class TopicService extends BaseObject implements PublishScoreInterface
{
    /**
     * 发表主题
     * 1. 写入主题信息
     * 2. 更新用户发帖数与积分
     * 3. 更新版块回复数
     * @param int $userId
     * @param array $data
     * @return Topic
     */
    public function publish($userId, array $data)
    {
        $data = ArrayHelper::filter($data, ['title', 'content', 'flag', 'forum_id']);
        $data['user_id'] = $userId;
        return Db::transaction(function () use ($userId, $data) {
            // 写入主题
            /** @var Topic $topic */
            $topic = TopicRepository::Factory()->insert($data);
            $score = $this->publishScore();
            // 更新用户
            /** @var User $user */
            $user = UserRepository::Factory()->findOne(['user_id' => $userId]);
            $user->score += $score;
            $user->thread_count++;
            $user->save();
            UserScoreLogService::Factory()->log($userId, $score, $user->score, '发表主题');
            // 更新版块
            ForumService::Factory()->addTopicCount($topic->forum_id, 1);
            return $topic;
        });
    }

    /**
     * 删除主题
     * 1. 检测用户是否发帖人或版主
     * 2. 删除主题
     * 3. 减少用户积分和发帖数
     * 4. 减少版块主题数
     * @param $topicId
     * @param $userId
     * @return Topic
     */
    public function delete($topicId, $userId)
    {
        return Db::transaction(function () use ($topicId, $userId) {
            /** @var Topic $topic */
            $topic = TopicRepository::Factory()->findOne(['topic_id' => $topicId]);
            if (empty($topic)) {
                throw new Exception('帖子不存在');
            }
            if (!$this->shouldAccess($userId, $topic)) {
                throw new Exception('你无权删除');
            }
            if (!$topic->delete()) {
                throw new Exception('删除失败');
            }
            // 更新用户
            $score = $this->publishScore();
            /** @var User $user */
            $user = UserRepository::Factory()->findOne(['user_id' => $topic->user_id]);
            $user->thread_count--;
            $user->score -= $score;
            $user->save();
            $msg = $userId == $topic->user_id ? '删除主题' : '主题被版主删除';
            UserScoreLogService::Factory()->log($topic->user_id, -$score, $user->score, $msg);
            // 更新版块
            ForumService::Factory()->addTopicCount($topic->forum_id, -1);
            return $topic;
        });
    }

    /**
     * 增加回复数
     * @param int $topicId
     * @param int $count
     * @return mixed|Model
     * @throws Exception
     * @throws DbException
     */
    public function addReplyCount($topicId, $count)
    {
        /** @var Topic $topic */
        $topic = TopicRepository::Factory()->findOne(['topic_id' => $topicId]);
        if (empty($topic)) {
            throw new Exception('主题不存在');
        }
        $topic->reply_count += $count;
        return $topic->save();
    }

    /**
     * 增加收藏数量
     * @param int $topicId
     * @param int $count
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function addFavoriteCount($topicId, $count)
    {
        /** @var Topic $topic */
        $topic = TopicRepository::Factory()->findOne(['topic_id' => $topicId]);
        if (empty($topic)) {
            throw new Exception('主题不存在');
        }
        $topic->favorite_count += $count;
        return $topic->save();
    }

    /**
     * 帖子详情(包括发帖人,所在版块)
     * @param int $topicId
     * @return array|false|PDOStatement|string|Model|Topic
     * @throws DbException
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function showWithUserWithForum($topicId)
    {
        return TopicRepository::Factory()->showWithRelations($topicId, ['user', 'forum']);
    }

    /**
     * 帖子详情(包含版块)
     * @param int $topicId
     * @return array|false|PDOStatement|string|Model|Topic
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function showWithForum($topicId)
    {
        return TopicRepository::Factory()->showWithRelations($topicId, ['forum']);
    }

    /**
     * 查看主题
     * @param int $topicId
     * @return array|false|PDOStatement|string|Model|Topic
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function show($topicId)
    {
        return TopicRepository::Factory()->showWithRelations($topicId);
    }

    /**
     * 帖子详情(包含发帖人)
     * @param int $topicId
     * @return array|false|PDOStatement|string|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function showWithUser($topicId)
    {
        return TopicRepository::Factory()->showWithRelations($topicId, ['user']);
    }

    /**
     * 获取版块帖子列表
     * @param int $forumId
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithUserByForum($forumId, $size = 10)
    {
        return TopicRepository::Factory()->listWithUserByForum($forumId, $size);
    }

    /**
     * 用户主题列表
     * @param int $userId
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithForumByUser($userId, $size = 10)
    {
        return TopicRepository::Factory()->listWithForumByUser($userId, $size);
    }

    /**
     * 管理后台帖子列表
     * @param int $forumId
     * @param null $keyword
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithUserWithForum($forumId = 0, $keyword = null, $size = 10)
    {
        return TopicRepository::Factory()->listWithUserWithForum($forumId, $keyword, $size);
    }

    /**
     * 最新帖子
     * @param int $size
     * @return false|PDOStatement|string|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function listLatest($size = 30)
    {
        return TopicRepository::Factory()->listLatest($size);
    }

    /**
     * 置顶帖子
     * @param int $adminId
     * @param int $topicId
     * @return mixed
     */
    public function setTop($adminId, $topicId)
    {
        return Db::transaction(function () use ($adminId, $topicId) {
            $topic = $this->scoreOperate($adminId, $topicId, $this->topScore(), '置顶帖子');
            $topic->top = 1;
            $topic->save();
        });
    }

    /**
     * 取消帖子置顶
     * @param int $adminId
     * @param int $topicId
     * @return mixed
     */
    public function unsetTop($adminId, $topicId)
    {
        return Db::transaction(function () use ($adminId, $topicId) {
            $topic = $this->scoreOperate($adminId, $topicId, -$this->topScore(), '取消帖子置顶');
            $topic->top = 0;
            $topic->save();
        });
    }

    /**
     * 积分操作(上层开启事务)
     * @param int $adminId
     * @param int $topicId
     * @param int $score
     * @param string $msg
     * @return Topic
     * @throws DbException
     * @throws Exception
     */
    private function scoreOperate($adminId, $topicId, $score, $msg)
    {
        /** @var Topic $topic */
        $topic = TopicRepository::Factory()->findOne(['topic_id' => $topicId]);
        if (empty($topic)) {
            throw new Exception('帖子不存在');
        }
        if (!ForumAdminService::Factory()->isAdmin($adminId, $topic->forum_id)) {
            throw new Exception('您无权操作');
        }
        // 添加帖子日志
        TopicScoreLogService::Factory()->log($adminId, $topicId, $score, $msg);
        // 添加用户积分日志
        /** @var User $user */
        $user = UserRepository::Factory()->findOne(['user_id' => $topic->user_id]);
        $user->score += $score;
        $user->save();
        UserScoreLogService::Factory()->log($user->user_id, $score, $user->score, $msg);
        return $topic;
    }

    /**
     * 检查操作权限
     * @param int $userId
     * @param Topic $topic
     * @return bool
     * @throws DbException
     */
    public function shouldAccess($userId, Topic $topic)
    {
        return $userId == $topic->user_id || ForumAdminService::Factory()->isAdmin($userId, $topic->forum_id);
    }

    /**
     * 获取积分
     * @return int
     */
    public function publishScore(): int
    {
        return config('app.score.publish_topic');
    }

    /**
     * 帖子置顶积分
     * @return int
     */
    public function topScore(): int
    {
        return config('app.score.top_topic');
    }

    /**
     * 编辑帖子
     * @param int $topicId
     * @param int $userId
     * @param array $data
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function update($topicId, $userId, array $data)
    {
        $data = ArrayHelper::filter($data, ['title', 'content', 'flag']);
        /** @var Topic $topic */
        $topic = TopicRepository::Factory()->findOne(['topic_id' => $topicId]);
        if (empty($topic)) {
            throw new Exception('帖子不存在');
        }
        if (!$this->shouldAccess($userId, $topic)) {
            throw new Exception('您无权操作');
        }
        return TopicRepository::Factory()->update($topic, $data);
    }

    /**
     * 点击数
     * @param int $topicId
     * @param string $ip
     * @param int|null $userId
     * @return mixed
     */
    public function view($topicId, $ip, $userId)
    {
        $cacheKey = sprintf('topic:view:%d:%s', $topicId, $ip);
        if (!empty($userId)) {
            $cacheKey = sprintf('topic:view:%d:%d', $topicId, $userId);
        }
        return Cache::remember($cacheKey, function () use ($topicId) {
            /** @var Topic $topic */
            $topic = TopicRepository::Factory()->findOne(['topic_id' => $topicId]);
            if (empty($topic)) {
                throw new Exception('主题不存在');
            }
            TopicRepository::Factory()->update($topic, ['view_count' => $topic->view_count + 1]);
            return 1;
        }, 60);
    }
}