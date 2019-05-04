<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\model;

/**
 * 收藏表
 * Class Favorite
 * @package app\common\model
 * @property int $user_id
 * @property int $topic_id
 * @property int $created_at
 */
class Favorite extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $createTime         = 'created_at';
    protected $updateTime         = false;

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id')->field('content', true);
    }
}