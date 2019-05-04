<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;

use app\common\model\Admin;

/**
 * 管理员
 * Class AdminRepository
 * @package app\common\repository
 */
class AdminRepository extends Repository
{
    /**
     * 模型类
     * @return string
     */
    protected function modelClass()
    {
        return Admin::class;
    }
}