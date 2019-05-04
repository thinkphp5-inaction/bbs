<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;


use app\common\model\ForumAdmin;
use PDOStatement;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

/**
 * 版块管理员仓储
 * Class ForumAdminRepository
 * @package app\common\repository
 */
class ForumAdminRepository extends Repository
{
    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return ForumAdmin::class;
    }

    /**
     * 根据版块获取版主列表
     * @param int $forumId
     * @return false|PDOStatement|string|Collection
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function listByForum($forumId)
    {
        $model = new ForumAdmin();
        $model->where([
            'forum_id' => $forumId,
            'expired_at' => [
                ['eq', 0],
                ['gt', time()],
                'or'
            ]
        ]);
        $model->with(['user']);
        return $model->select();
    }

    /**
     * 获取版块管理员ID列表
     * @param int $forumId
     * @return array
     */
    public function getAllAdminIdByForum($forumId)
    {
        $model = new ForumAdmin();
        $model->where('forum_id', $forumId);
        return $model->column('user_id');
    }
}