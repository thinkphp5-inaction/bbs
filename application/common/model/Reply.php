<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\model;

use traits\model\SoftDelete;

/**
 * 回帖表
 * Class Reply
 * @package app\common\model
 * @property int $reply_id
 * @property string $content
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $topic_id
 * @property int $forum_id
 * @property int $user_id
 */
class Reply extends BaseModel
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
    protected $createTime         = 'created_at';
    protected $updateTime         = 'updated_at';
    protected $deleteTime         = 'deleted_at';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id')->field(['password'], true);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id')->field(['content'], true);
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id', 'forum_id');
    }
}