<?php
namespace services;

use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class LightOrder extends Service
{
    public function __construct()
    {
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
     * @desc 获取预约的id
     */
    public function createOrderid($tpMachineid)
    {
        $i = 0;
        while ($i < 20) {
            $orderid = date("YmdHis") . rand(10, 99);
            $sql = "select orderid from light_order where tp_machineid='" . $tpMachineid . "' and orderid='" . $orderid . "' limit 1";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            $data = DaoFactory::getDao("Shard")->query($sql);
            if (empty($data)) {
                return $orderid;
            }
            ++$i;
        }
        return 0;
    }

    /**
     * @desc 获取预约的类型
     */
    public function add($tpMachineid, $machineid, $tpAppid, $appid, $orderid, $heattime, $week, $action, $lightness, $temperature, $red, $green, $blue)
    {
        $type = $this->getOrderType($week);
        $sql = "insert into light_order(tp_appid, appid, createtime, tp_machineid, orderid, type, machineid, heattime, week, action, lightness, temperature, red, green, blue) values ('" . $tpAppid . "','" . $appid . "','" . time() . "', '" . $tpMachineid . "', '" . $orderid . "', '" . $type . "', '" . $machineid . "', '" . $heattime . "', '" . $week . "', '" . $action . "', '" . $lightness . "', '" . $temperature . "', '" . $red . "', '" . $green . "', '" . $blue . "')";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 判断预约是否存在
     */
    public function isExist($tpMachineid, $orderid)
    {
        $sql = "select orderid from light_order where tp_machineid='" . $tpMachineid . "' and orderid='" . $orderid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @desc 获取预约的详情
     */
    public function getDetail($tpMachineid, $orderid)
    {
        $sql = "select * from light_order where tp_machineid='" . $tpMachineid . "' and orderid='" . $orderid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return array(
                "orderid" => $data[0]['orderid'],
                "heattime" => $data[0]['heattime'],
                "week" => $data[0]['week'],
                "action" => $data[0]['action'],
                "lightness" => $data[0]['lightness'],
                "temperature" => $data[0]['temperature'],
                "red" => $data[0]['red'],
                "green" => $data[0]['green'],
                "blue" => $data[0]['blue'],
            );
        }
    }

    /**
     * @desc 删除预约
     */
    public function cancelOrder($tpMachineid, $tpAppid, $orderid)
    {
        $sql = "delete from light_order where tp_machineid='" . $tpMachineid . "' and tp_appid='" . $tpAppid . "' and orderid='" . $orderid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 获取预约的数量
     */
    public function getOrderNum($tpMachineid, $tpAppid)
    {
        if (!empty($tpAppid)) {
            $sql = "select count(1) as num from light_order where tp_machineid='" . $tpMachineid . "' and tp_appid='" . $tpAppid . "'";
        } else {
            $sql = "select count(1) as num from teapot_order where tp_machineid='" . $tpMachineid . "'";
        }
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return 0;
        } else {
            return $data[0]['num'];
        }
    }

    /**
     * @desc 获取预约列表
     */
    public function getOrderList($tpMachineid, $tpAppid, $offset, $limit)
    {
        $ret = array();
        if (!empty($tpAppid)) {
            $sql = "select * from light_order where tp_machineid='" . $tpMachineid . "' and tp_appid='" . $tpAppid . "' order by createtime asc limit " . $offset . "," . $limit . "";
        } else {
            $sql = "select * from light_order where tp_machineid='" . $tpMachineid . "' order by createtime asc limit " . $offset . "," . $limit . "";
        }

        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        foreach ($data as $item) {
            $ret[] = array(
                "orderid" => $item['orderid'],
                "heattime" => $item['heattime'],
                "week" => $item['week'],
                "action" => $item['action'],
                "lightness" => $item['lightness'],
                "temperature" => $item['temperature'],
                "red" => $item['red'],
                "green" => $item['green'],
                "blue" => $item['blue'],
            );
        }
        return $ret;
    }
}

