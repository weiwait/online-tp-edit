<?php
namespace base;
use utils\Common;
class Service extends Object
{
    /**
     * 为每个类提供一个通用写log的方法
     */
    public function log($msg, $level = Logger::DEBUG)
    {
        $category = get_class($this);
        Logger::writeLog($category, $level, $msg);
    }
}
