<?php
/**
 * @author xialeistudio <xialeistudio@gmail.com>
 */

namespace app\common\service;


use app\common\BaseObject;
use think\Exception;
use think\File;

/**
 * 上传
 * Class UploadService
 * @package app\common\service
 */
class UploadService extends BaseObject
{
    /**
     * 上传
     * @param File $file
     * @return string
     * @throws Exception
     */
    public function upload(File $file)
    {
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
        if (!$info) {
            throw new Exception($file->getError());
        }
        return '/upload' . DS . $info->getSaveName();
    }
}