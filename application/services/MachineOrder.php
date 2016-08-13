<?php
/**
 * Created by PhpStorm.
 * User: AfirSraftGarrier
 * Date: 2016-05-07
 * Time: 17:06
 */
namespace services;

use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

include_once "MCommonService.php";

class MachineOrder extends MCommonService
{
    private $tableName = "machine_order";

    /**
     * @desc 添加机器预约
     */
    public function add($tpMachineid, $machineid, $tpAppid, $appid, $orderid, $heattime, $week, $action, $data = "")
    {
        $type = $this->getOrderType($week);
        $sql = "insert into " . $this->tableName . "(tp_appid, appid, createtime, tp_machineid, orderid, type, machineid, heattime, week, action, data) values ('" . $tpAppid . "','" . $appid . "','" . time() . "', '" . $tpMachineid . "', '" . $orderid . "', '" . $type . "', '" . $machineid . "', '" . $heattime . "', '" . $week . "', '" . $action . "', '" . $data . "')";
        return $this->query($tpMachineid, $sql);
    }

    /**
     * @desc 获取预约的类型
     */
    private function getOrderType($week)
    {
        $type = 0;
        $len = strlen($week);
        for ($i = 0; $i < $len; $i++) {
            $c = substr($week, $i, 1);
            if ("0" != $c) {
                $type = 1;
                break;
            }
        }
        return $type;
    }

    /**
     * @desc 删除预约
     */
    public function delete($tpMachineid, $orderid)
    {
        $sql = "delete from " . $this->tableName . " where orderid='" . $orderid . "' and tp_machineid='" . $tpMachineid . "'";
        return DaoFactory::query($tpMachineid, $sql);
    }

    /**
     * @desc 判断预约是否存在
     */
    public function isExist($tpMachineid, $orderid)
    {
        $sql = "select orderid from " . $this->tableName . " where tp_machineid='" . $tpMachineid . "' and orderid='" . $orderid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @desc 获取预约的数量
     */
    public function getOrderNum($tpMachineid, $tpAppid)
    {
        if (!empty($tpAppid)) {
            $sql = "select count(1) as num from " . $this->tableName . " where tp_machineid='" . $tpMachineid . "' and tp_appid='" . $tpAppid . "'";
        } else {
            $sql = "select count(1) as num from " . $this->tableName . " where tp_machineid='" . $tpMachineid . "'";
        }
        $data = $this->query($tpMachineid, $sql);
        if (empty($data)) {
            return 0;
        } else {
            return $data[0]['num'];
        }
    }

    /**
     * @desc 获取预约列表
     */
    public function getOrderList($tpMachineid, $offset, $limit)
    {
        $ret = array();
        if (!empty($tpAppid)) {
            $sql = "select * from " . $this->tableName . " where tp_machineid='" . $tpMachineid . "' and tp_appid='" . $tpAppid . "' order by createtime asc limit " . $offset . "," . $limit . "";
        } else {
            $sql = "select * from " . $this->tableName . " where tp_machineid='" . $tpMachineid . "' order by createtime asc limit " . $offset . "," . $limit . "";
        }
        $data = $this->query($tpMachineid, $sql);
        foreach ($data as $item) {
            $ret[] = array(
                "orderid" => $item['orderid'],
                "heattime" => $item['heattime'],
                "week" => $item['week'],
                "action" => $item['action'],
                "data" => $item['data']
            );
        }
        return $ret;
    }

    /**
     * @desc 获取预约的详情
     */
    public function getOrder($tpMachineid, $orderid)
    {
        $sql = "select * from " . $this->tableName . " where tp_machineid='" . $tpMachineid . "' and orderid='" . $orderid . "' limit 1";
        $data = $this->query($tpMachineid, $sql);
        if (empty($data)) {
            return false;
        } else {
            return array(
                "orderid" => $data[0]['orderid'],
                "heattime" => $data[0]['heattime'],
                "week" => $data[0]['week'],
                "action" => $data[0]['action'],
                "data" => $data['0']['data']
            );
        }
    }

    /**
     * @desc cron定时检查
     */
    public function cronCheck()
    {
        $wday = date("w", time());
        $sql = "select * from " . $this->tableName . " where isdelete='0'";
        $data = $this->query('1', $sql);
        foreach ($data as $item) {
            $week = $item['week'];
            $machineid = $item['machineid'];
            $tpMachineid = $item['tp_machineid'];
            $heattime = $item['heattime'];
            $action = $item['action'];
            $orderid = $item['orderid'];
            $data = $item['data'];
            if (7 != strlen($week)) {
                continue;
            }
            if (!in_array($action, array("run", "stop"))) {
                continue;
            }
            if (6 != strlen($heattime)) {
                continue;
            }
            $hour = intval(substr($heattime, 0, 2));
            $mins = intval(substr($heattime, 2, 2));
            $second = intval(substr($heattime, 4, 2));
            $year = date("Y");
            $month = date("m");
            $day = date("d");
            $unixTime = mktime($hour, $mins, $second, $month, $day, $year);
            $now = time();
            $c = intval($week[$wday - 1]);
            $isOneTime = $week == "0000000";
            if ((1 == $c || $isOneTime) && $now > $unixTime && $now - $unixTime < 15) {
                if ("run" == $action) {
                    if ($data)
                        $data = json_decode($data, true);
                    startMachine($data, $machineid);
                } else {
                    stopMachine(null, $machineid);
                }
                if ($isOneTime) {
                    $this->delete($tpMachineid, $orderid);
                }
            }
        }
    }
}