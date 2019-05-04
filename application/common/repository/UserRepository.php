<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;

use app\common\model\User;
use think\exception\DbException;
use think\Model;
use think\Paginator;

/**
 * 用户仓储
 * Class UserRepository
 * @package app\common\repository
 */
class UserRepository extends Repository
{
    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return User::class;
    }

    /**
     * 排除指定用户ID的列表
     * @param array $without
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithout(array $without = [], $size = 10)
    {
        $model = new User();
        if (!empty($without)) {
            $model->whereNotIn('user_id', $without);
        }
        return $model->paginate($size);
    }

    /**
     * 用户列表
     * @param int $size
     * @param null $keyword
     * @return Paginator
     * @throws DbException
     */
    public function listByPageByKeyword($size = 10, $keyword = null)
    {
        $model = new User();
        if (!empty($keyword)) {
            $model->where('nickname|username', 'like', '%' . $keyword . '%');
        }
        $model->order(['user_id' => 'desc']);
        return $model->paginate($size);
    }
}