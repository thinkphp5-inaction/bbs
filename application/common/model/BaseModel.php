<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\model;

use think\Exception;
use think\Model;

/**
 * 模型基类
 * Class BaseModel
 * @package app\common\model
 */
class BaseModel extends Model
{
    /**
     * 删除
     * @return Model|mixed
     * @throws Exception
     */
    public function delete()
    {
        if (!parent::delete()) {
            throw new Exception('删除失败');
        }
        return $this;
    }

    /**
     * 保存数据
     * @param array $data
     * @param array $where
     * @param null $sequence
     * @return Model|mixed
     * @throws Exception
     */
    public function save($data = [], $where = [], $sequence = null)
    {
        if (false === parent::save($data, $where, $sequence)) {
            throw new Exception('保存失败');
        }
        return $this;
    }
}