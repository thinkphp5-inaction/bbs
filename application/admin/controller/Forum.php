<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\admin\controller;

use app\common\service\ForumAdminService;
use app\common\service\ForumService;
use app\common\service\UploadService;
use app\common\service\UserService;
use think\Exception;
use think\exception\DbException;
use think\Request;
use think\Validate;

/**
 * 版块
 * Class Forum
 * @package app\admin\controller
 */
class Forum extends BaseController
{
    protected function _initialize()
    {
        $this->loginRequired();
    }

    /**
     * 版块列表
     * @return mixed
     */
    public function index()
    {
        try {
            $list = ForumService::Factory()->all();
            $this->assign('list', $list);
            return $this->fetch();
        } catch (DbException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 新增版块
     * @return mixed
     */
    public function new()
    {
        return $this->fetch();
    }

    /**
     * 处理新增版块
     * @param Request $request
     */
    public function do_new(Request $request)
    {
        $errmsg = $this->validate($request->post(), [
            'title' => 'require|max:20',
            'desc' => 'require|max:100'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        $errmsg = $this->validate($request->file(), [
            'logo' => 'require|file'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            $data = $request->post();
            $data['logo'] = UploadService::Factory()->upload($request->file('logo'));
            ForumService::Factory()->add($data);
            $this->success('添加成功!', 'index');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 编辑版块
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        try {
            $forum = ForumService::Factory()->show($id);
            $this->assign('forum', $forum);
            return $this->fetch();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 处理版块编辑
     * @param Request $request
     */
    public function do_update(Request $request)
    {
        $errmsg = $this->validate($request->post(), [
            'id' => 'require',
            'title|名称' => 'require|max:20',
            'desc|简介' => 'require|max:100'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            $data = [
                'title' => $request->post('title'),
                'desc' => $request->post('desc'),
            ];
            $logo = $request->file('logo');
            if (!empty($logo)) {
                $data['logo'] = UploadService::Factory()->upload($logo);
            }
            ForumService::Factory()->update($request->post('id'), $data);
            $this->success('编辑成功', 'index');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 版主列表
     * @param Request $request
     * @return mixed
     */
    public function admins(Request $request)
    {
        $forumId = $request->param('id');
        if (empty($forumId)) {
            $this->error('您的请求有误');
        }
        try {
            $list = ForumAdminService::Factory()->listByForum($forumId);
            $this->assign('list', $list);
            $this->assign('forum_id', $forumId);
            return $this->fetch();
        } catch (DbException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 绑定版主
     * @param Request $request
     * @return mixed
     */
    public function bindadmin(Request $request)
    {
        $forumId = $request->param('id');
        if (empty($forumId)) {
            $this->error('您的请求有误');
        }
        try {
            $adminIds = ForumAdminService::Factory()->getAllAdminIdByForum($forumId);
            $users = UserService::Factory()->listWithout($adminIds, 30);
            $this->assign('users', $users);
            $this->assign('forum_id', $forumId);
            return $this->fetch();
        } catch (DbException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 绑定版主
     * @param Request $request
     */
    public function do_bindadmin(Request $request)
    {
        $errmsg = $this->validate($request->param(), [
            'id|版块ID' => 'require',
            'uid|用户ID' => 'require'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            ForumAdminService::Factory()->bind($request->param('uid'), $request->param('id'));
            $this->success('操作成功!');
        } catch (DbException $e) {
            $this->error($e->getMessage());
        }
    }

    public function do_unbindadmin(Request $request)
    {
        $errmsg = $this->validate($request->param(), [
            'id|版块ID' => 'require',
            'uid|用户ID' => 'require'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        try {
            ForumAdminService::Factory()->unbind($request->param('uid'), $request->param('id'));
            $this->success('操作成功!');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}