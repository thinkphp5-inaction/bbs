<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;

use app\common\model\Reply;
use PDOStatement;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\exception\DbException;
use think\Model;
use think\Paginator;

/**
 * 回复仓储
 * Class ReplyRepository
 * @package app\common\repository
 */
class ReplyRepository extends Repository
{
    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return Reply::class;
    }

    /**
     * 根据主题ID获取回复列表+回复者
     * @param int $topicId
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithUserByTopic($topicId, $size = 10)
    {
        $model = new Reply();
        $model->where('topic_id', $topicId);
        $model->with(['user']);
        return $model->paginate($size);
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
        $model = new Reply();
        $model->where('user_id', $userId);
        $model->with([
            'topic',
            'forum'
        ]);
        $model->field('content', true);
        return $model->paginate($size);
    }

    /**
     * 回复详情
     * @param int $replyId
     * @param int $userId
     * @return array|false|PDOStatement|string|Model
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function showWithTopicWithForumByUser($replyId, $userId)
    {
        $model = new Reply();
        $model->where('reply_id', $replyId);
        if (!empty($userId)) {
            $model->where('user_id', $userId);
        }
        $model->with(['topic', 'forum']);
        return $model->find();
    }
}