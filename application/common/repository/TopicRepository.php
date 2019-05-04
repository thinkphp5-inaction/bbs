<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;

use app\common\model\Topic;
use PDOStatement;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;
use think\Paginator;

/**
 * 主题仓储
 * Class TopicRepository
 * @package app\common\repository
 */
class TopicRepository extends Repository
{
    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return Topic::class;
    }

    /**
     * 获取帖子详情
     * @param int $topicId
     * @param array $relations
     * @return array|false|PDOStatement|string|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function showWithRelations($topicId, array $relations = [])
    {
        $model = new Topic();
        $model->where('topic_id', $topicId);
        $model->with($relations);
        $topic = $model->find();
        if (empty($topic)) {
            throw new Exception('帖子不存在');
        }
        return $topic;
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
        $model = new Topic();
        $model->where('forum_id', $forumId);
        $model->with(['user']);
        $model->order(['top' => 'desc', 'topic_id' => 'desc']);
        return $model->paginate($size);
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
        $model = new Topic();
        if (!empty($forumId)) {
            $model->where('forum_id', $forumId);
        }
        if (!empty($keyword)) {
            $model->where('title', 'like', '%' . $keyword . '%');
        }
        $model->with(['user', 'forum']);
        $model->order(['top' => 'desc', 'topic_id' => 'desc']);
        return $model->paginate($size);
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
        $model = new Topic();
        $model->where('user_id', $userId);
        $model->with(['forum']);
        $model->order(['topic_id' => 'desc']);
        return $model->paginate($size);
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
        $model = new Topic();
        $model->field('content', true);
        $model->order(['topic_id' => 'desc']);
        $model->limit($size);
        $model->with(['forum', 'user']);
        return $model->select();
    }
}