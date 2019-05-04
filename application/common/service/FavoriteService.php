<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;

use app\common\BaseObject;
use app\common\model\Favorite;
use app\common\repository\FavoriteRepository;
use app\common\repository\ForumRepository;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\Model;
use think\Paginator;

/**
 * 收藏
 * Class FavoriteService
 * @package app\common\service
 */
class FavoriteService extends BaseObject
{
    /**
     * 添加收藏
     * @param int $userId
     * @param int $topicId
     * @return Favorite|mixed
     * @throws DbException
     */
    public function add($userId, $topicId)
    {
        $favorite = FavoriteRepository::Factory()->findOne(['user_id' => $userId, 'topic_id' => $topicId]);
        if (!empty($favorite)) {
            return $favorite;
        }
        return Db::transaction(function () use ($userId, $topicId) {
            $favorite = FavoriteRepository::Factory()->insert(['user_id' => $userId, 'topic_id' => $topicId]);
            TopicService::Factory()->addFavoriteCount($topicId, 1);
            return $favorite;
        });
    }

    /**
     * 取消收藏
     * @param int $userId
     * @param int $topicId
     * @return int
     */
    public function remove($userId, $topicId)
    {
        return Db::transaction(function () use ($userId, $topicId) {
            $count = FavoriteRepository::Factory()->delete(['user_id' => $userId, 'topic_id' => $topicId]);
            if (!$count) {
                throw new Exception('取消收藏失败');
            }
            TopicService::Factory()->addFavoriteCount($topicId, -1);
            return $count;
        });
    }

    /**
     * 检查是否收藏
     * @param int $userId
     * @param int $topicId
     * @return bool
     * @throws DbException
     */
    public function isFavorite($userId, $topicId)
    {
        return FavoriteRepository::Factory()->findOne(['user_id' => $userId, 'topic_id' => $topicId]) != null;
    }

    /**
     * 根据用户获取收藏列表
     * @param int $userId
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithTopicByUser($userId, $size = 10)
    {
        return FavoriteRepository::Factory()->listWithTopicByUser($userId, $size);
    }
}