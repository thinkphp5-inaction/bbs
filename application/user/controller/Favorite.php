<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\user\controller;

use app\common\service\FavoriteService;
use think\exception\DbException;
use think\Request;

/**
 * 收藏
 * Class Favorite
 * @package app\user\controller
 */
class Favorite extends BaseController
{
    /**
     * 收藏
     * @return mixed
     * @throws DbException
     */
    public function index()
    {
        $list = FavoriteService::Factory()->listWithTopicByUser($this->userId());
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 删除收藏
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $topicId = $request->param('topic_id');
        if (empty($topicId)) {
            $this->error('您的请求有误');
        }
        FavoriteService::Factory()->remove($this->userId(), $topicId);
        $this->success('操作成功','/user/favorite');
    }
}