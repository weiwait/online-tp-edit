<?php
namespace services;

use base\Service;
use base\DaoFactory;

/**
 * Created by PhpStorm.
 * User: AfirSraftGarrier
 * Date: 2016-03-31
 * Time: 14:36
 */
abstract class MCommonService extends Service
{
    /**
     * @desc 请求
     */
    protected function query($tpMachineid, $sql)
    {
        return DaoFactory::query($tpMachineid, $sql);
    }

    /**
     * @desc 获取电器使用记录的数量
     */
    protected function getCommonActionLogNum($tpMachineid, $actionLogName)
    {
        $sql = "select count(1) as num from " . $actionLogName . " where tp_machineid='" . $tpMachineid . "'";
        $data = $this->query($tpMachineid, $sql);
        if (empty($data)) {
            return 0;
        } else {
            return $data[0]['num'];
        }
    }

    /**
     * @desc 保存或增加记录
     */
    protected function insertOrUpdate($tableName, $tpMachineid, $tpAppid, $data)
    {
        $sql = "select 1 from " . $tableName . " where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' limit 1";
        $flag = $this->query($tpAppid, $sql);
        if ($flag) {
            $sep = "";
            $sql = "update app_machine_config set ";
            foreach ($data as $k => $v) {
                $sql .= $sep . $k . "='" . $v . "'";
                $sep = ",";
            }
            $sql .= " where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' limit 1";
        } else {
            $sep = "";
            $sql = "insert into app_machine_config set ";
            foreach ($data as $k => $v) {
                $sql .= $sep . $k . "='" . $v . "'";
                $sep = ",";
            }
            $sql .= ", tp_appid='" . $tpAppid . "', tp_machineid='" . $tpMachineid . "'";
        }
        return $this->query($tpAppid, $sql);
    }

    protected function getMachineType($machineid)
    {
        return substr($machineid, 0, 2);
    }
}