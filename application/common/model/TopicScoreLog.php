<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\model;

/**
 * 主题积分日志
 * Class TopicScoreLog
 * @package app\common\model
 * @property int $log_id
 * @property int $score
 * @property string $msg
 * @property int $created_at
 * @property int $topic_id
 * @property int $user_id
 */
class TopicScoreLog extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
}