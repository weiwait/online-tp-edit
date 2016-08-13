<?php

class Bootstrap extends \Yaf_Bootstrap_Abstract
{
    /**
     * 初始化配置
     */
    public function _initVar(Yaf_Dispatcher $dispatcher)
    {
        // 引入共用的全局函数
        /*
        $_POST = \utils\Util::addQuotes($_POST);
        $_GET = \utils\Util::addQuotes($_GET);
        $_COOKIE = \utils\Util::addQuotes($_COOKIE);
        */
    }

    /**
     * 初始化配置
     */
    public function _initConfig()
    {
        // 引入共用的全局函数
        require APP_PATH . '/library/func/global.func.php';
        $config = \Yaf_Application::app()->getConfig();
        \Yaf_Registry::set("config", $config);
    }

    /**
     * 初始化session
     * 在这个初始化的动作完成之后,才能使用 $_SESSION 这个变量
     *
     */
    public function _initSession()
    {
        //session_start();
    }

    /**
     * 初始化模板资源
     * 后面需要用到资源的类,通过 Yaf_Registry::get('resource') 的方式获取单例
     */
    public function _initResource()
    {
        $resource = base\Resource::getInstance();
        $resource->setPreviewPath(APP_PATH . "/application");
        $resource->setRequirePath(APP_PATH . "/application/views/require");
        \Yaf_Registry::set("resource", $resource);
    }

    /**
     * 初始化路由配置
     */
    public function _initRouter(\Yaf_Dispatcher $dispatcher)
    {
        $router = $dispatcher->getRouter();
        $router->addConfig(\Yaf_Registry::get("config")->routes);
    }

    /**
     * 注册插件
     */
    public function _initPlugin(\Yaf_Dispatcher $dispatcher)
    {
        // 注册各个插件
        Yaf_Registry::set("dispatcher", $dispatcher);
    }

    /**
     * 初始化layout
     */
    public function _initLayout(\Yaf_Dispatcher $dispatcher)
    {
        $layout = new LayoutPlugin('layout.phtml');
        \Yaf_Registry::set('layout', $layout);
        $dispatcher->registerPlugin($layout);
    }

    /**
     * 程序结束回调函数，框架唯一出口
     */
    public function _initShutdown()
    {
        register_shutdown_function(array(&$this, 'shutdown'));
    }

    public function shutdown()
    {
        //避免重复执行，已执行过一次就直接退出了
        if (!$this->_isShutDown) {
        }
    }

    private $_isShutDown = false;
}
