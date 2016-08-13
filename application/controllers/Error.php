<?php
use base\Logger;
use base\ServiceFactory;
class ErrorController extends \Yaf_Controller_Abstract 
{
    /**
     * 初始化
     */
    public function init()
    {
    }
    /**
     * 显示提示页面
     */
    public function tipsAction()
    {
    }
    /**
     * 显示系统错误页面
     */
    public function errorAction($exception) {
        echo "<pre>";
        print_r($exception);
        echo "</pre>";
    }
}
