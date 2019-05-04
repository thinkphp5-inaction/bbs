<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;


use app\common\BaseObject;
use app\common\helper\ArrayHelper;
use app\common\model\Forum;
use app\common\repository\ForumRepository;
use PDOStatement;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;

/**
 * 版块业务
 * Class ForumService
 * @package app\common\service
 */
class ForumService extends BaseObject
{
    /**
     * @param array $data
     * @return mixed|Forum
     * @throws Exception
     * @throws DbException
     */
    public function add(array $data)
    {
        $forum = ForumRepository::Factory()->findOne(['title' => $data['title']]);
        if (!empty($forum)) {
            throw new Exception('版块已存在');
        }
        $data = ArrayHelper::filter($data, ['title', 'logo', 'desc', 'status']);
        return ForumRepository::Factory()->insert($data);
    }

    /**
     * 编辑版块
     * @param int $forumId
     * @param array $data
     * @return mixed|Forum
     * @throws DbException
     * @throws Exception
     */
    public function update($forumId, array $data)
    {
        $forum = ForumRepository::Factory()->findOne(['forum_id' => $forumId]);
        if (empty($forum)) {
            throw new Exception('版块不存在');
        }
        $data = ArrayHelper::filter($data, ['title', 'logo', 'desc', 'status']);
        return ForumRepository::Factory()->update($forum, $data);
    }

    /**
     * 版块列表
     * @param null|int $status
     * @return false|PDOStatement|string|Collection
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function all($status = null)
    {
        $condition = [];
        if (isset($status)) {
            $condition['status'] = $status;
        }
        return ForumRepository::Factory()->all($condition);
    }

    /**
     * 添加主题数
     * @param int $forumId
     * @param int $count
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function addTopicCount($forumId, $count)
    {
        /** @var Forum $forum */
        $forum = ForumRepository::Factory()->findOne(['forum_id' => $forumId]);
        if (empty($forum)) {
            throw new Exception('版块不存在');
        }
        $forum->topic_count += $count;
        return $forum->save();
    }

    /**
     * 更新回复数
     * @param int $forumId
     * @param int $count
     * @return mixed|Model
     * @throws DbException
     * @throws Exception
     */
    public function addReplyCount($forumId, $count)
    {
        /** @var Forum $forum */
        $forum = ForumRepository::Factory()->findOne(['forum_id' => $forumId]);
        if (empty($forum)) {
            throw new Exception('版块不存在');
        }
        $forum->thread_count += $count;
        return $forum->save();
    }

    /**
     * 查看版块
     * @param int $id
     * @param null $status
     * @return array|false|PDOStatement|string|Model|Forum
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function show($id, $status = null)
    {
        $model = new Forum();
        $model->where('forum_id', $id);
        if (isset($status)) {
            $model->where('status', $status);
        }
        $forum = $model->find();
        if (empty($forum)) {
            throw new Exception('版块不存在');
        }
        return $forum;
    }
}