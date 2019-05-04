<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\user\controller;

use app\common\service\TopicService;
use app\common\service\UploadService;
use app\common\service\UserService;
use think\exception\DbException;
use think\Request;

/**
 * 个人中心
 * Class Index
 * @package app\user\controller
 */
class Index extends BaseController
{
    /**
     * 主题列表
     * @throws DbException
     */
    public function index()
    {
        $list = TopicService::Factory()->listWithForumByUser($this->userId());
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function profile()
    {
        $user = UserService::Factory()->show($this->userId());
        $this->assign('user', $user);
        return $this->fetch();
    }

    public function do_profile(Request $request)
    {
        $post = array_filter($request->post());
        $errmsg = $this->validate($post, [
            'nickname|昵称' => 'require|max:20'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        $avatar = $request->file('avatar');
        if (!empty($avatar)) {
            $post['avatar'] = UploadService::Factory()->upload($avatar);
        }
        UserService::Factory()->updateProfile($this->userId(), $post);
        $this->success('操作成功', 'profile');
    }
}