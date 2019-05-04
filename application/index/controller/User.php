<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\index\controller;

use app\common\service\UserService;
use think\Exception;
use think\Request;

/**
 * Class User
 * @package app\index\controller
 */
class User extends BaseController
{
    public function signup()
    {
        return $this->fetch();
    }

    public function do_signup(Request $request)
    {
        $errmsg = $this->validate($request->post(), [
            'username|账号' => 'require|max:20',
            'password|密码' => 'require',
            'confirm_password|确认密码' => 'require|confirm:password',
            'captcha|验证码' => 'require|captcha'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            UserService::Factory()->register($request->post('username'), $request->post('password'));
            $this->success('注册成功!', 'signin');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function signin()
    {
        return $this->fetch();
    }

    public function do_signin(Request $request)
    {
        $errmsg = $this->validate($request->post(), [
            'username|账号' => 'require|max:20',
            'password|密码' => 'require',
            'captcha|验证码' => 'require|captcha'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            UserService::Factory()->login($request->post('username'), $request->post('password'), request()->ip());
            $this->redirect('/');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function logout()
    {
        UserService::Factory()->logout();
        $this->redirect('/');
    }
}