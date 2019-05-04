<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\admin\controller;


use app\common\service\AdminService;
use think\Exception;
use think\exception\DbException;
use think\Request;

class   Index extends BaseController
{
    protected function _initialize()
    {
        $this->loginRequired();
    }

    public function index()
    {
        return $this->fetch();
    }

    public function changepassword()
    {
        return $this->fetch();
    }

    public function do_changepassword(Request $request)
    {
        $errmsg = $this->validate($request->post(), [
            'old_password|当前密码' => 'require',
            'new_password|新密码' => 'require',
            'new_password_confirm|确认密码' => 'require|confirm:new_password'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            AdminService::Factory()->changePassword($this->adminId(), $request->post('old_password'), $request->post('new_password'));
            $this->success('修改成功!');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}