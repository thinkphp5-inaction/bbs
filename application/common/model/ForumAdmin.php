<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\model;

/**
 * 版主表
 * Class ForumAdmin
 * @package app\common\model
 * @property int $user_id
 * @property int $forum_id
 * @property int $created_at
 * @property int $expired_at
 */
class ForumAdmin extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $type = [
        'expired_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}