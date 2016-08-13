<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class Admin extends Service
{
	public function __construct(){
	}

    public function deleteMachine($machineId)
    {
        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineId);
        if(empty($tpMachineid))
        {
            return false;
        }
        //删除绑定信息
        $sql = "delete from bind where tp_machineid='".$tpMachineid."'";
        DaoFactory::getDao("Main")->query($sql);


        $tableNameArray = array(
            "app_machine_config",
            "app_msg",
            "bind_log",
            "humidifier_action_log",
            "humidifier_config",
            "humidifier_order",
            "humidifier_stat",
            "humidifier_state",
            "humidifier_work",
            "light_config",
            "light_order",
            "light_state",
            "light_work",
            "teapot_action_log",
            "teapot_order",
            "teapot_runtime_feedback",
            "teapot_stat",
            "teapot_state",
        );

        foreach($tableNameArray as $tableName)
        {
            $sql = "delete from ".$tableName." where tp_machineid='".$tpMachineid."'";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            DaoFactory::getDao("Shard")->query($sql);
        }


        return true;

    }
}
