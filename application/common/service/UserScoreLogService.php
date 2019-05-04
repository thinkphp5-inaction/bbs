<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;


use app\common\BaseObject;
use app\common\model\User;
use app\common\repository\UserScoreLogRepository;
use think\Model;

/**
 * 用户积分
 * Class UserScoreLogService
 * @package app\common\service
 */
class UserScoreLogService extends BaseObject
{
    /**
     * 添加积分(score为负数时减少积分)
     * @param int $userId
     * @param int $score
     * @param int $remain
     * @param string $msg
     * @return mixed|Model
     */
    public function log($userId, $score, $remain, $msg)
    {
        $log = [
            'score' => $score,
            'remain' => $remain,
            'msg' => $msg,
            'user_id' => $userId,
        ];
        return UserScoreLogRepository::Factory()->insert($log);
    }
}