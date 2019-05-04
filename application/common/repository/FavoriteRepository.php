<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;

use app\common\model\Favorite;
use think\exception\DbException;
use think\Model;
use think\Paginator;

/**
 * 收藏
 * Class FavoriteRepository
 * @package app\common\repository
 */
class FavoriteRepository extends Repository
{
    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return Favorite::class;
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
        $model = new Favorite();
        $model->where('user_id', $userId);
        $model->with(['topic']);
        $model->order(['created_at' => 'desc']);
        return $model->paginate($size);
    }
}