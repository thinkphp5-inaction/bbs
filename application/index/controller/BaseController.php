<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\index\controller;

use app\common\service\UserService;
use think\Controller;

class BaseController extends Controller
{
    protected $guestActions = [];

    protected function loginRequired()
    {
        $user = UserService::Factory()->getLoggedUser();
        if (empty($user) && !in_array(request()->action(), $this->guestActions)) {
            $this->redirect('/index/user/signin');
        }
        return $user;
    }

    protected function userId()
    {
        $user = $this->loginRequired();
        return $user['user_id'];
    }
}