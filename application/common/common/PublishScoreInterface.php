<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\common;

/**
 * 获取积分接口
 * Interface PublishScoreInterface
 * @package app\common\common
 */
interface PublishScoreInterface
{
    /**
     * 获取积分
     * @return int
     */
    public function publishScore(): int;
}