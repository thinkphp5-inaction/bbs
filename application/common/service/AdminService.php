<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;


use app\common\BaseObject;
use app\common\model\Admin;
use app\common\repository\AdminRepository;
use think\Exception;
use think\exception\DbException;
use think\Model;
use think\Session;

/**
 * 管理员业务
 * Class AdminService
 * @package app\common\service
 */
class AdminService extends BaseObject
{
    const SESSION_ADMIN = 'admin';
    const SESSION_LOGIN_INFO = 'admin.login';

    public function getLoggedAdmin()
    {
        return session(self::SESSION_ADMIN);
    }

    /**
     * 注册
     * @param string $username
     * @param string $password
     * @return mixed|Admin
     * @throws DbException
     * @throws Exception
     */
    public function register($username, $password)
    {
        $admin = AdminRepository::Factory()->findOne(['username' => $username]);
        if (!empty($admin)) {
            throw new Exception('用户名已存在');
        }
        return AdminRepository::Factory()->insert([
            'username' => $username,
            'password' => $password
        ]);
    }

    /**
     * 登录
     * @param string $username
     * @param string $password
     * @param string $ip
     * @return Admin|null
     * @throws DbException
     * @throws Exception
     */
    public function login($username, $password, $ip)
    {
        /** @var Admin $admin */
        $admin = AdminRepository::Factory()->findOne(['username' => $username]);
        if (empty($admin) || !password_verify($password, $admin->password)) {
            throw new Exception('用户名或密码错误');
        }
        session(self::SESSION_LOGIN_INFO, ['login_at' => $admin->login_at, 'login_ip' => $admin->login_ip]);
        session(self::SESSION_ADMIN, $admin);
        AdminRepository::Factory()->update($admin, ['login_at' => time(), 'login_ip' => $ip]);
        return $admin;
    }

    /**
     * 修改密码
     * @param int $adminId
     * @param string $oldPassword
     * @param string $newPassword
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function changePassword($adminId, $oldPassword, $newPassword)
    {
        /** @var Admin $admin */
        $conditions = ['admin_id' => $adminId];
        $admin = AdminRepository::Factory()->findOne($conditions);
        if (empty($admin)) {
            throw new Exception('管理员不存在');
        }
        if (!password_verify($oldPassword, $admin->password)) {
            throw new Exception('旧密码错误');
        }
        return AdminRepository::Factory()->update($admin, ['password' => $newPassword]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Session::clear();
    }
}