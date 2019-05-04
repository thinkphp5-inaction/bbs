<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;

use app\common\BaseObject;
use app\common\model\ForumAdmin;
use app\common\repository\ForumAdminRepository;
use PDOStatement;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;

/**
 * 版块管理员
 * Class ForumAdminService
 * @package app\common\service
 */
class ForumAdminService extends BaseObject
{
    /**
     * 添加版主
     * @param int $userId
     * @param int $forumId
     * @return ForumAdmin|mixed|Model
     * @throws DbException
     */
    public function bind($userId, $forumId)
    {
        /** @var ForumAdmin $model */
        $model = ForumAdminRepository::Factory()->findOne(['user_id' => $userId, 'forum_id' => $forumId]);
        if (!empty($model)) {
            return $model;
        }
        return ForumAdminRepository::Factory()->insert([
            'user_id' => $userId,
            'forum_id' => $forumId,
            'expired_at' => 0
        ]);
    }

    /**
     * 移除版主
     * @param int $userId
     * @param int $forumId
     * @return int
     * @throws Exception
     */
    public function unbind($userId, $forumId)
    {
        return ForumAdminRepository::Factory()->delete(['user_id' => $userId, 'forum_id' => $forumId]);
    }

    /**
     * 获取版主列表
     * @param int $forumId
     * @return false|PDOStatement|string|Collection
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function listByForum($forumId)
    {
        return ForumAdminRepository::Factory()->listByForum($forumId);
    }

    /**
     * 获取板块管理员ID列表
     * @param int $forumId
     * @return array
     */
    public function getAllAdminIdByForum($forumId)
    {
        return ForumAdminRepository::Factory()->getAllAdminIdByForum($forumId);
    }

    /**
     * 检测是否版主
     * @param int $userId
     * @param int $forumId
     * @return bool
     * @throws DbException
     */
    public function isAdmin($userId, $forumId)
    {
        return ForumAdminRepository::Factory()->findOne(['user_id' => $userId, 'forum_id' => $forumId]) != null;
    }
}