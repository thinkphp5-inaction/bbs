<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\index\controller;

use app\common\service\ReplyService;
use think\Exception;
use think\Request;

/**
 * 回复
 * Class Reply
 * @package app\index\controller
 */
class Reply extends BaseController
{
    protected function _initialize()
    {
        $this->loginRequired();
    }

    public function update(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        $reply = ReplyService::Factory()->showWithTopicWithForumByUser($id, $this->userId());
        if (empty($reply)) {
            $this->error('您无权操作!');
        }
        $this->assign('reply', $reply);
        return $this->fetch();
    }

    public function do_update(Request $request)
    {
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('您的请求有误');
        }
        $errmsg = $this->validate($request->post(), [
            'content|回复内容' => 'require'
        ]);
        if ($errmsg !== true) {
            $this->error($errmsg);
        }
        $reply = ReplyService::Factory()->update($this->userId(), $id, $request->post('content'));
        $this->success('编辑成功', url('topic/show', ['id' => $reply['topic_id']]));
    }
}