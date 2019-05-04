<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common;

/**
 * Class BaseObject
 * @package app\common
 */
class BaseObject
{
    private static $_instances = [];

    /**
     * @return static
     */
    public final static function Factory()
    {
        if (!isset(self::$_instances[static::class])) {
            self::$_instances[static::class] = new static();
        }
        return self::$_instances[static::class];
    }
}