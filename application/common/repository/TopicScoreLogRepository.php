<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;

use app\common\model\TopicScoreLog;
use think\Model;

class TopicScoreLogRepository extends Repository
{

    /**
     * 模型类
     * @return string|Model
     */
    protected function modelClass()
    {
        return TopicScoreLog::class;
    }
}