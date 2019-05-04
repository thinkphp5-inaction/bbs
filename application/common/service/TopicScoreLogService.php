<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;


use app\common\BaseObject;
use app\common\model\TopicScoreLog;
use app\common\repository\TopicScoreLogRepository;

/**
 * 帖子积分日志
 * Class TopicScoreLogService
 * @package app\common\service
 */
class TopicScoreLogService extends BaseObject
{
    /**
     * 写入帖子积分日志
     * @param int $adminId
     * @param int $topicId
     * @param int $score
     * @param string $msg
     * @return mixed|TopicScoreLog
     */
    public function log($adminId, $topicId, $score, $msg)
    {
        return TopicScoreLogRepository::Factory()->insert([
            'score' => $score,
            'msg' => $msg,
            'topic_id' => $topicId,
            'user_id' => $adminId
        ]);
    }
}