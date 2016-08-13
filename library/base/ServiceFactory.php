<?php
namespace base;
class ServiceFactory
{
    /**
     * 获取指定module的service操作对象
     *
     * @param String $serviceName  - 服务对象名字
     * @param String $module       - 服务对象所属module的名字
     */
    public static function getService($serviceName, $module = 'Default')
    {
        if (empty($serviceName) || empty($module))
        {
            throw new \Yaf_Exception_StartupError("ServiceFactory::getService \$serviceName and \$module are required");
        }
        $module = ucfirst($module);
        if (!isset(self::$_objs[$module][$serviceName]))
        {
            if ($module == 'Default')
            {
                $servicePath = APP_PATH . "/application/services/";
                $fileName    = $servicePath . '/' . ucfirst($serviceName) . ".php";
                $className   = "\services\\" . ucfirst($serviceName);

            } else {
                $servicePath = APP_PATH . "/application/modules/" . $module . "/services/";
                $fileName    = $servicePath . '/' . ucfirst($serviceName) . ".php";
                $className   = "\\" . strtolower($module) . "\services\\" . ucfirst($serviceName);
            }
            if(!is_file($fileName))
            {
                echo "<pre>"; 
                debug_print_backtrace();
                echo "</pre>"; 
                die;
            }
            require $fileName;
            self::$_objs[$module][$serviceName]       = new $className();
        }
        return self::$_objs[$module][$serviceName];
    }
    private static $_objs = array();
}
