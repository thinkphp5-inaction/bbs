<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;


use app\common\BaseObject;
use app\common\helper\ArrayHelper;
use app\common\model\User;
use app\common\repository\UserRepository;
use think\Exception;
use think\exception\DbException;
use think\File;
use think\Model;
use think\Paginator;
use think\Session;

/**
 * 用户业务
 * Class UserService
 * @package app\common\service
 */
class UserService extends BaseObject
{
    const SESSION_KEY = 'user';
    const SESSION_LOGIN = 'user.login';

    /**
     * 注册
     * @param string $username
     * @param string $password
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function register($username, $password)
    {
        $admin = UserRepository::Factory()->findOne(['username' => $username]);
        if (!empty($admin)) {
            throw new Exception('用户名已存在');
        }
        return UserRepository::Factory()->insert([
            'username' => $username,
            'password' => $password
        ]);
    }

    /**
     * 登录
     * @param string $username
     * @param string $password
     * @param $ip
     * @return User
     * @throws DbException
     * @throws Exception
     */
    public function login($username, $password, $ip)
    {
        /** @var User $user */
        $user = UserRepository::Factory()->findOne(['username' => $username]);
        if (empty($user) || !password_verify($password, $user->password)) {
            throw new Exception('用户名或密码错误');
        }

        session(self::SESSION_LOGIN, [$user->login_at, $user->login_ip]);
        session(self::SESSION_KEY, $user);

        UserRepository::Factory()->update($user, ['login_at' => time(), 'login_ip' => $ip]);
        return $user;
    }

    /**
     * 修改密码
     * @param int $userId
     * @param string $oldPassword
     * @param string $newPassword
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        /** @var User $user */
        $conditions = ['user_id' => $userId];
        $user = UserRepository::Factory()->findOne($conditions);
        if (empty($user)) {
            throw new Exception('用户不存在');
        }
        if (!password_verify($oldPassword, $user->password)) {
            throw new Exception('旧密码错误');
        }
        return UserRepository::Factory()->update($user, ['password' => $newPassword]);
    }

    /**
     * 用户列表
     * @param int $size
     * @param null $keyword
     * @return Paginator
     * @throws DbException
     */
    public function listByPageByKeyword($size = 10, $keyword = null)
    {
        return UserRepository::Factory()->listByPageByKeyword($size, $keyword);
    }

    /**
     * 排除指定用户的列表
     * @param array $userIds
     * @param int $size
     * @return Paginator
     * @throws DbException
     */
    public function listWithout(array $userIds = [], $size = 10)
    {
        return UserRepository::Factory()->listWithout($userIds, $size);
    }

    /**
     * 获取已登录用户
     * @return mixed
     */
    public function getLoggedUser()
    {
        return session(self::SESSION_KEY);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Session::delete(self::SESSION_KEY);
    }

    /**
     * 编辑资料
     * @param string $userId
     * @param array $data
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function updateProfile($userId, array $data)
    {
        $user = UserRepository::Factory()->findOne(['user_id' => $userId]);
        if (empty($user)) {
            throw new Exception('用户不存在');
        }
        $data = ArrayHelper::filter($data, ['nickname', 'avatar', 'password']);
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return UserRepository::Factory()->update($user, $data);
    }

    public function show($userId)
    {
        return UserRepository::Factory()->findOne(['user_id' => $userId]);
    }
}