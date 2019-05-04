<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;


use app\common\model\Forum;
use think\Model;

/**
 * 版块仓储
 * Class ForumRepository
 * @package app\common\repository
 */
class ForumRepository extends Repository
{
    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return Forum::class;
    }
}