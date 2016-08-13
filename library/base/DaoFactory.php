<?php
namespace base;
class DaoFactory
{
    public static function query($tpMachineid, $sql)
    {
        DaoFactory::getShardDao()->branchDb($tpMachineid);
        return DaoFactory::getShardDao()->query($sql);
    }

    private static function getShardDao()
    {
        return DaoFactory::getDao("Shard");
    }

    /**
     * 获取dao对象
     *
     * @param String $daoName - 数据对象的名字
     * @param String $module - 模块
     */
    public static function getDao($daoName, $module = 'Default')
    {
        if (empty($daoName)) {
            throw new Yaf_Exception_StartupError("DaoFactory::getDao \$daoName and \$module are required");
        }
        $module = ucfirst($module);
        if (!isset(self::$_objs[$module][$daoName])) {
            if ($module == 'Default') {
                $servicePath = APP_PATH . "/application/dao";
                $fileName = $servicePath . '/' . ucfirst($daoName) . ".php";
                $className = "\dao\\" . ucfirst($daoName);
            } else {
                $servicePath = APP_PATH . "/application/modules/" . $module . "/dao/";
                $fileName = $servicePath . '/' . ucfirst($daoName) . ".php";
                $className = "\\" . strtolower($module) . "\dao\\" . ucfirst($daoName);
            }
            require $fileName;
            self::$_objs[$module][$daoName] = new $className();
        }
        return self::$_objs[$module][$daoName];
    }

    private static $_objs = array();
}
