<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\user\controller;


use app\common\service\UserService;
use think\Controller;

class BaseController extends Controller
{
    protected $user;

    protected function _initialize()
    {
        $this->user = UserService::Factory()->getLoggedUser();
        if (empty($this->user)) {
            $this->redirect('/index/user/signin');
        }
    }

    protected function userId()
    {
        return $this->user['user_id'];
    }
}