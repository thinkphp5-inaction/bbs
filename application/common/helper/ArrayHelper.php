<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\helper;

/**
 * 数组工具类
 * Class ArrayHelper
 * @package app\common\helper
 */
class ArrayHelper
{
    /**
     * 数组字段过滤
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function filter(array $array, array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $result[$key] = $array[$key];
            }
        }
        return $result;
    }
}