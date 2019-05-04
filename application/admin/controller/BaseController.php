<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\admin\controller;


use app\common\service\AdminService;
use think\Controller;

class BaseController extends Controller
{
    protected function loginRequired()
    {
        $admin = AdminService::Factory()->getLoggedAdmin();
        if (empty($admin)) {
            $this->redirect('/admin/session/signin');
        }
        return $admin;
    }

    protected function adminId()
    {
        $admin = $this->loginRequired();
        return $admin['admin_id'];
    }
}