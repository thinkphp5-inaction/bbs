<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\repository;


use app\common\BaseObject;
use PDOStatement;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;
use think\Paginator;

/**
 * 仓储层
 * Class Repository
 * @package app\common\repository
 */
abstract class Repository extends BaseObject
{
    /**
     * 模型类
     * @return string|Model
     */
    abstract protected function modelClass();

    /**
     * 新增数据
     * @param array $data
     * @return mixed|Model
     */
    public function insert(array $data)
    {
        $className = $this->modelClass();
        /** @var Model $model */
        $model = new $className();
        $model->data($data);
        return $model->save();
    }

    /**
     * 查找一条数据
     * @param array $conditions
     * @return Model
     * @throws DbException
     */
    public function findOne(array $conditions)
    {
        $className = $this->modelClass();
        return $className::get($conditions);
    }

    /**
     * 更新数据
     * @param Model $model
     * @param array $data
     * @return mixed|Model
     */
    public function update(Model $model, array $data)
    {
        return $model->save($data);
    }

    /**
     * 删除数据
     * @param array $conditions
     * @return int
     * @throws Exception
     */
    public function delete(array $conditions)
    {
        $className = $this->modelClass();
        /** @var Model $model */
        $model = new $className();
        $deleteCount = $model->where($conditions)->delete();
        if (!$deleteCount) {
            throw new Exception('删除失败');
        }
        return $deleteCount;
    }

    /**
     * 分页数据
     * @param int $size
     * @param array $conditions
     * @return Paginator
     * @throws DbException
     */
    public function listByPage($size = 10, array $conditions = [])
    {
        $className = $this->modelClass();
        /** @var Model $model */
        $model = new $className();
        return $model->where($conditions)->paginate($size);
    }

    /**
     * 获取所有数据
     * @param array $conditions
     * @return false|PDOStatement|string|Collection
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function all(array $conditions = [])
    {
        $className = $this->modelClass();
        /** @var Model $model */
        $model = new $className();
        if (!empty($conditions)) {
            $model->where($conditions);
        }
        return $model->select();
    }
}