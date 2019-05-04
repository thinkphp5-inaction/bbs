<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\model;

/**
 * 用户积分日志
 * Class UserScoreLog
 * @package app\common\model
 * @property int $log_id
 * @property int $remain
 * @property string $msg
 * @property int $created_at
 * @property int $user_id
 */
class UserScoreLog extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
}