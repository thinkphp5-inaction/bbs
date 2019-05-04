<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\admin\controller;

use app\common\service\AdminService;
use think\Exception;
use think\Request;

class Session extends BaseController
{
    public function signin()
    {
        return $this->fetch();
    }

    public function do_signin(Request $request)
    {
        $errmsg = $this->validate($request->post(), [
            'username' => 'require|max:20',
            'password' => 'require',
            'captcha' => 'require|captcha'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            AdminService::Factory()->login($request->post('username'), $request->post('password'), $request->ip());
            $this->redirect('/admin');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function logout()
    {
        AdminService::Factory()->logout();
        $this->redirect('/');
    }
}