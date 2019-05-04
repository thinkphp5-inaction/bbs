<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\admin\controller;


use app\common\service\UserService;
use think\exception\DbException;
use think\Request;

class User extends BaseController
{
    protected function _initialize()
    {
        $this->loginRequired();
    }

    /**
     * 用户列表
     * @param Request $request
     * @return mixed
     * @throws DbException
     */
    public function index(Request $request)
    {
        $list = UserService::Factory()->listByPageByKeyword(30, $request->param('keyword'));
        $this->assign('list', $list);
        return $this->fetch();
    }
}