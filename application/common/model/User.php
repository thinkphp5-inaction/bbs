<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\model;

/**
 * Class User
 * @package app\common\model
 * @property int $user_id
 * @property string $username
 * @property string $password
 * @property string $nickname
 * @property string $avatar
 * @property int $thread_count
 * @property int $score
 * @property int $status
 * @property int $created_at
 * @property string $created_ip
 * @property int $login_at
 * @property string $login_ip
 */
class User extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $createTime         = 'created_at';
    protected $updateTime         = false;

    protected $type = [
        'login_at' => 'timestamp'
    ];

    const STATUS_ALLOW_LOGIN = 1 << 0; // 允许登录

    protected static function init()
    {
        self::beforeInsert(function (User $user) {
            $user->password = password_hash($user->password, PASSWORD_DEFAULT);
        });
    }

    protected $insert = ['created_ip'];

    protected function setCreatedIpAttr()
    {
        if (isRunningConsole()) {
            return 'cli';
        }

        return request()->ip();
    }

    /**
     * 是否允许登录
     * @return int
     */
    public function isAllowLogin()
    {
        return $this->status & self::STATUS_ALLOW_LOGIN;
    }

    /**
     * 设置是否允许登录
     * @param bool $allow
     */
    public function setAllowLogin($allow)
    {
        $this->status |= self::STATUS_ALLOW_LOGIN;
        if (!$allow) {
            // 不允许则将该位置0
            $this->status ^= self::STATUS_ALLOW_LOGIN;
        }
    }
}