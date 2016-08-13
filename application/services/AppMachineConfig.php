<?php
/**
 * Created by PhpStorm.
 * User: AfirSraftGarrier
 * Date: 2016-05-07
 * Time: 17:06
 */
namespace services;

use base\ServiceFactory;
use utils\Tag;

include_once "MCommonService.php";

class AppMachineConfig extends MCommonService
{
    /**
     * @desc cron定时检查
     */
    public function cronCheck()
    {
        $activeTpMachineids = ServiceFactory::getService("Machine")->getActiveTpMachineid();
        $isNightMode = $this->isNightModeTime();
        foreach ($activeTpMachineids as $activeTpMachineid) {
            $activeTpMachineid = $activeTpMachineid["tp_machineid"];
            $machineid = ServiceFactory::getService("Machine")->getMachineid($activeTpMachineid);
            if (!$machineid) {
                continue;
            }
            $machineType = $this->getMachineType($machineid);
            if (!(getLightTag() == $machineType || getRgbTag() == $machineType || getMosquitokillerTag() == $machineType)) {
                continue;
            }
            $tpAppidArray = ServiceFactory::getService("Machine")->getAppidList($activeTpMachineid);
            if (empty($tpAppidArray)) {
                continue;
            }
            $machineDetail = ServiceFactory::getService("Machine")->getDetail($activeTpMachineid);
            if (empty($machineDetail)) {
                continue;
            }
            $machineLongitude = $machineDetail['longitude'];
            $machineLatitude = $machineDetail['latitude'];
            $machineLongitudeAndroid = $machineDetail['longitude_android'];
            $machineLatitudeAndroid = $machineDetail['latitude_android'];
            if (empty($machineLongitude) && empty($machineLatitude) && empty($machineLongitudeAndroid) && empty($machineLatitudeAndroid)) {
                continue;
            }
            $machineLongitude = floatval($machineLongitude);
            $machineLatitude = floatval($machineLatitude);
            $machineLongitudeAndroid = floatval($machineLongitudeAndroid);
            $machineLatitudeAndroid = floatval($machineLatitudeAndroid);
            $autoStartDistance = 1350;
            $autoStopDistance = 1650;
            $rawDistance = 999999999;
            $startMinLen = $rawDistance;
            $stopMinLen = $rawDistance;
            $enableNightMode = false;
            //$lastTpAppid = $this->getLastTpAppid($activeTpMachineid);
            foreach ($tpAppidArray as $tpAppid) {
                if (!ServiceFactory::getService("App")->isActive($tpAppid)) {
                    continue;
                }
                $appDetail = ServiceFactory::getService("App")->getDetail($tpAppid);
                $appLongitude = $appDetail['longitude'];
                $appLatitude = $appDetail['latitude'];
                if (empty($appLongitude) && empty($appLatitude)) {
                    continue;
                }
                $startStopDetail = ServiceFactory::getService("App")->getMachineConfig($tpAppid, $activeTpMachineid);
                $startFlag = $startStopDetail['enable_user_near_start'];
                $stopFlag = $startStopDetail['enable_user_far_stop'];
                $nightMode = $startStopDetail['enable_night_mode'];
                $appLongitude = floatval($appLongitude);
                $appLatitude = floatval($appLatitude);
                if ($nightMode) {
                    $enableNightMode = true;
                }
                if (2 == $appDetail['phone_type'] && !empty($machineLongitudeAndroid) && !empty($machineLatitudeAndroid)) {
                    $p1 = ($machineLongitude - $appLongitude);
                    $p2 = ($machineLatitude - $appLatitude);
                } else {
                    $p1 = ($machineLongitudeAndroid - $appLongitude);
                    $p2 = ($machineLatitudeAndroid - $appLatitude);
                }
                $len = sqrt($p1 * $p1 + $p2 * $p2) * 1000000;
                if ($len > 50000) {
                    continue;
                }
                if ($startFlag) {
                    if ($len < $startMinLen) {
                        $startMinLen = $len;
                    }
                }
                if ($stopFlag) {
                    if ($len < $stopMinLen) {
                        $stopMinLen = $len;
                    }
                }
            }
            $data = $machineDetail['data'];
            if ($data)
                $data = json_decode($data, true);
            if ($isNightMode && (getLightTag() == $machineType || getRgbTag() == $machineType) && $enableNightMode) {
                $this->enableNightMode($activeTpMachineid, $machineid, $data);
            }
            if ($startMinLen <= $autoStartDistance) {
                // 这里注释掉这个功能：进入自动开启
                //startMachine($data, $machineid);
                continue;
            }
            if ($rawDistance != $stopMinLen && $stopMinLen > $autoStopDistance) {
                stopMachine($data, $machineid);
                continue;
            }
        }
    }

    private function isNightModeTime()
    {
        $hour = date("G", time());
        return $hour < 6;
    }

    private function enableNightMode($tpMachineid, $machineid, $data)
    {
        $state = ServiceFactory::getService("Light")->getState($tpMachineid);
        if ($state && $state['state'] && $data['l'] > 40) {
            $data['l'] = '40';
            startMachine($data, $machineid);
        }
    }
}