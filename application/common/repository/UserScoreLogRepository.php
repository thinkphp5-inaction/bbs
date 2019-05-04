<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;

use app\common\model\UserScoreLog;
use think\Model;

/**
 * 用户积分日志仓储
 * Class UserScoreLogRepository
 * @package app\common\repository
 */
class UserScoreLogRepository extends Repository
{
    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return UserScoreLog::class;
    }
}