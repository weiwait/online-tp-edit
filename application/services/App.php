<?php
namespace services;

use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

include_once "MCommonService.php";

class App extends MCommonService
{
    public function __construct()
    {
    }

    public function getAppid($tpAppid)
    {
        $sql = "select appid from app where id='" . $tpAppid . "' limit 1";
        $data = DaoFactory::getDao("Main")->query($sql);
        return $data[0]['appid'];
    }

    public function getAppidsByTpAppids($tpAppidArray)
    {
        if (empty($tpAppidArray)) {
            return array();
        }
        $ret = array();
        $sql = "select id, appid from app where id in('" . implode("','", $tpAppidArray) . "')";
        $data = DaoFactory::getDao("Main")->query($sql);
        foreach ($data as $item) {
            $ret[] = $item['appid'];
        }
        return $ret;
    }

    public function getTpAppid($appid)
    {
        $sql = "select id from app where appid='" . $appid . "' limit 1";
        $data = DaoFactory::getDao("Main")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return $data[0]['id'];
        }
    }

    public function isExist($appid)
    {
        $sql = "select id from app where appid='" . $appid . "' limit 1";
        $data = DaoFactory::getDao("Main")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return true;
        }
    }

    public function getPhoneType($appid)
    {
        $len = strlen($appid);
        if (16 == $len) {
            return 1; //android
        } else if (36 == $len) {
            return 2; //ios
        } else {
            return 0;
        }
    }

    public function getDetail($tpAppid)
    {
        $sql = "select * from app_detail where tp_appid='" . $tpAppid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return array();
        }
        return $data[0];
    }

    public function getAppUseStat($tpAppid)
    {
        $sql = "select * from app_use_stat where tp_appid='" . $tpAppid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);

        if (empty($data)) {
            return array();
        }

        $useDay = $data[0]['use_day'];
        $loginTime = $data[0]['login_time'];
        $loginNum = $data[0]['login_num'];
        $lastUpdateTime = $data[0]['last_update_time'];

        return array(
            "useDay" => $useDay,
            "loginTime" => $loginTime,
            "loginNum" => $loginNum,
            "lastUpdateTime" => $lastUpdateTime,
        );
    }

    public function rebuildAllAppUseStat()
    {
        include APP_PATH . "/conf/shard.php";

        $shard = $db_shard_config['shard'];

        $machineArray = array();

        foreach ($shard as $key => $value) {
            $arr = explode("-", $key);
            $min = intval($arr[0]);
            $shardId = $min + 1;
            $sql = "select tp_appid from app_detail where 1=1";
            DaoFactory::getDao("Shard")->branchDb($shardId);
            $data = DaoFactory::getDao("Shard")->query($sql);;
            foreach ($data as $item) {
                $tpAppid = $item['tp_appid'];
                echo "======" . $tpAppid . "=======\n";
                $useStat = $this->updateAppUseStat($tpAppid);
                if (empty($useStat)) {
                    continue;
                }
                $newUseDay = $useStat['useDay'];
                $newLoginTime = $useStat['loginTime'];
                $newLoginNum = $useStat['loginNum'];

                //查询旧的数据
                $sql = "select * from app_use_stat where tp_appid='" . $tpAppid . "'";
                echo $sql . "\n";
                $oldData = DaoFactory::getDao("Shard")->query($sql);

                $oldUseDay = $oldData[0]['use_day'];
                $oldLoginTime = $oldData[0]['login_time'];
                $oldLoginNum = $oldData[0]['loginNum'];

                //合并新和旧
                $okUseDay = $newUseDay + $oldUseDay;
                $okLoginTime = $newLoginTime + $oldLoginTime;
                $okLoginNum = $newLoginNum + $oldLoginNum;

                //更新
                $sql = "replace into app_use_stat set last_update_time='" . time() . "', use_day='" . $okUseDay . "', login_time='" . $okLoginTime . "', login_num='" . $okLoginNum . "', tp_appid='" . $tpAppid . "'";
                echo $sql . "\n";
                DaoFactory::getDao("Shard")->query($sql);

                $sql = "delete from app_ping where tp_appid='" . $tpAppid . "'";
                echo $sql . "\n";
                DaoFactory::getDao("Shard")->query($sql);
            }
        }
    }

    /**
     * @desc 获取app的使用统计
     */
    public function updateAppUseStat($tpAppid)
    {
        $dayArray = array();
        $oldTime = 0;
        $loginNum = 0;
        $loginTime = 0;
        $sql = "select ctime from app_ping where tp_appid='" . $tpAppid . "' order by ctime asc";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        foreach ($data as $item) {
            $ctime = $item['ctime'];
            $date = date("Ymd", $ctime);
            if (!isset($dayArray[$date])) {
                $dayArray[$date] = 1;
            }

            if (!empty($oldTime)) {
                //大于1分钟算是离开
                if ($ctime - $oldTime > 60) {
                    ++$loginNum;
                } else {
                    $loginTime += $ctime - $oldTime;
                }
            } else {
                ++$loginNum;
            }
            $oldTime = $ctime;
        }

        $useDay = count($dayArray);

        return array(
            "useDay" => $useDay,
            "loginTime" => $loginTime,
            "loginNum" => $loginNum,
        );
    }

    public function active($tpAppid, $updateIp = false)
    {
        //fei 2014-11-20增加数据统计
        $sql = "insert into app_ping (tp_appid, ctime) values('" . $tpAppid . "', '" . time() . "');";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        DaoFactory::getDao("Shard")->query($sql);

        if ($_SERVER['REMOTE_ADDR']) {
            $sql = "update app_detail set last_active_ip='" . $_SERVER['REMOTE_ADDR'] . "', last_active_time='" . time() . "' where tp_appid='" . $tpAppid . "' limit 1";
        } else {
            $sql = "update app_detail set last_active_time='" . time() . "' where tp_appid='" . $tpAppid . "' limit 1";
        }
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function reg($appid)
    {
        $sql = "insert into app (appid) values('" . $appid . "')";
        if (DaoFactory::getDao("Main")->query($sql)) {
            $tpAppid = DaoFactory::getDao("Main")->insert_id();
            $phoneType = $this->getPhoneType($appid);
            $sql = "insert into app_detail(tp_appid, phone_type, createtime, last_active_time, create_ip, last_active_ip, isdelete) values ('" . $tpAppid . "', '" . $phoneType . "', '" . time() . "', '" . time() . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SERVER['REMOTE_ADDR'] . "', 0)";
            DaoFactory::getDao("Shard")->branchDb($tpAppid);
            return DaoFactory::getDao("Shard")->query($sql);
        }
    }

    public function isBind($tpAppid, $tpMachineid)
    {
        $sql = "select * from bind where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' limit 1";
        $data = DaoFactory::getDao("Main")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return true;
        }
    }

    public function bind($tpAppid, $tpMachineid)
    {
        ServiceFactory::getService("Machine")->bindLog($tpMachineid, $tpAppid, "bind");

        $sql = "insert into bind (tp_appid, tp_machineid) values('" . $tpAppid . "', '" . $tpMachineid . "')";
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function unBind($tpAppid, $tpMachineid)
    {
        ServiceFactory::getService("Machine")->bindLog($tpMachineid, $tpAppid, "unbind");
        $sql = "delete from bind where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "'";
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function getMachineNum($tpAppid)
    {
        $ret = 0;
        $sql = "select count(1) as num from bind where tp_appid='" . $tpAppid . "'";
        $data = DaoFactory::getDao("Main")->query($sql);
        $ret = intval($data[0]['num']);
        return $ret;
    }

    public function getMachineList($tpAppid, $offset, $limit, $phoneType = 2)
    {
        $ret = array();
        $sql = "select bind.tp_machineid, machine.machineid from bind, machine where bind.tp_appid='" . $tpAppid . "' and bind.tp_machineid=machine.id limit " . $offset . ", " . $limit . "";
        $data = DaoFactory::getDao("Main")->query($sql);
        foreach ($data as $item) {
            $tpMachineid = $item['tp_machineid'];
            $machineid = $item['machineid'];
            //FIXME，这里暂时只能读到teapot的状态
            $detail = ServiceFactory::getService("Teapot")->getState($tpMachineid);
            $isOnline = ServiceFactory::getService("Machine")->isActive($tpMachineid);
            $machineDetail = ServiceFactory::getService("Machine")->getDetail($tpMachineid);
            //查询machine类型
            $machinetype = ServiceFactory::getService("Machine")->getMachineType($machineid);
            //JP_add,查询智能开智能关是否同时关,同时关返回0，有一个为开返回1
            $geofence = ServiceFactory::getService("humidifier")->getGeofence($tpMachineid, $tpAppid);

            //FIXME orderid

            if (2 == $phoneType) {
                $longitude = $machineDetail['longitude'];
                $latitude = $machineDetail['latitude'];
            } else {
                $longitude = $machineDetail['longitude_android'];
                $latitude = $machineDetail['latitude_android'];
            }
            $temp = array(
                "machineid" => $machineid,
                "level" => isset($detail['level']) ? $detail['level'] : "",
                "temp" => isset($detail['temp']) ? $detail['temp'] : "",
                "hub" => isset($detail['hub']) ? $detail['hub'] : "",
                "state" => isset($detail['state']) ? $detail['state'] : "",
                "isonline" => $isOnline ? "online" : "offline",
                "longitude" => $longitude,
                "latitude" => $latitude,
                "geofence" => $geofence,
                //"orderid"=>"",
            );

            if ($machinetype != '02') {
                unset($temp['geofence']);
            }

            $ret[] = $temp;
        }
        return $ret;
    }


    public function deleteMachine($tpAppid, $tpMachineid)
    {
        ServiceFactory::getService("Machine")->bindLog($tpMachineid, $tpAppid, "unbind");
        $sql = "delete from bind where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "'";
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function getNewVersion($tpAppid, $version)
    {
        $sql = "select * from version where newversion > '" . $version . "' order by newversion desc limit 1";
        $data = DaoFactory::getDao("Main")->query($sql);
        if (empty($data)) {
            return array();
        } else {
            return $data[0];
        }
    }

    public function feedback($tpAppid, $content)
    {
        $sql = "insert into feedback(tp_appid, content, createtime, ip, isdelete) values('" . $tpAppid . "', '" . $content . "', '" . time() . "', '" . $_SERVER['REMOTE_ADDR'] . "', 0)";
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function getAllCount($appid, $status = '')
    {
        //status,在线状态，1在线，2不在线，默认全部.2015/11/30增加在线过滤
        $where = "1=1";
        if (!empty($status)) {
            include APP_PATH . "/conf/shard.php";
            $shard = $db_shard_config['shard'];
            foreach ($shard as $key => $value) {
                $arr = explode("-", $key);
                $min = intval($arr[0]);
                $shardId = $min + 1;
                if ($status == 1) {
                    $sql = "select tp_appid from app_detail where last_active_time > " . (time() - 60);
                } else {
                    $sql = "select tp_appid from app_detail where last_active_time <= " . (time() - 60);
                }
                DaoFactory::getDao("Shard")->branchDb($shardId);
                $data = DaoFactory::getDao("Shard")->query($sql);
                foreach ($data as $value) {
                    $status_id .= ',' . $value['tp_appid'];
                }
                if ($data) {
                    $where = "id in (" . trim($status_id, ',') . ")";
                } else {
                    $where = "1!=1";
                }
            }
        }


        if (empty($appid)) {
            $sql = "select count(1) as num from app where " . $where;
        } else if (is_array($appid)) {
            $sql = "select count(1) as num from app where id in ('" . implode("','", $appid) . "')";
        } else {
            $sql = "select count(1) as num from app where appid='" . $appid . "'";
        }
        $data = DaoFactory::getDao("Main")->query($sql);
        return $data[0]['num'];
    }

    //没有经纬度的app统计
    public function getAppNoJWCount()
    {
        include APP_PATH . "/conf/shard.php";

        $total = 0;
        $shard = $db_shard_config['shard'];

        foreach ($shard as $key => $value) {
            $arr = explode("-", $key);
            $min = intval($arr[0]);
            $shardId = $min + 1;
            $sql = "select count(1) as num from app_detail where longitude = '' and latitude = '' ";
            DaoFactory::getDao("Shard")->branchDb($shardId);
            $data = DaoFactory::getDao("Shard")->query($sql);;
            $num = $data[0]['num'];
            $total += $num;
        }
        return $total;
    }

    //统计app在线数，默认统计全部,nojw=true时，统计无经纬度的在线数
    public function getAppOnlineCount($nojw = false)
    {
        include APP_PATH . "/conf/shard.php";

        $total = 0;
        $shard = $db_shard_config['shard'];
        //少于1分钟，就认为是活的
        $time = time() - 60;

        $machineArray = array();

        foreach ($shard as $key => $value) {
            $arr = explode("-", $key);
            $min = intval($arr[0]);
            $shardId = $min + 1;
            if ($nojw) {
                $sql = "select count(1) as num from app_detail where longitude = '' and latitude = ''and last_active_time >" . $time;
            } else {
                $sql = "select count(1) as num from app_detail where last_active_time >" . $time;
            }
            DaoFactory::getDao("Shard")->branchDb($shardId);
            $data = DaoFactory::getDao("Shard")->query($sql);;
            $num = $data[0]['num'];
            $total += $num;
        }
        return $total;
    }

    public function getList($appid, $offset, $limit, $status = '')
    {
        //status,在线状态，1在线，2不在线，默认全部.2015/11/30增加在线过滤
        //少于1分钟，就认为是活的
        $where = "1=1";
        if (!empty($status)) {
            include APP_PATH . "/conf/shard.php";
            $shard = $db_shard_config['shard'];
            foreach ($shard as $key => $value) {
                $arr = explode("-", $key);
                $min = intval($arr[0]);
                $shardId = $min + 1;
                if ($status == 1) {
                    $sql = "select tp_appid from app_detail where last_active_time > " . (time() - 60);
                } else {
                    $sql = "select tp_appid from app_detail where last_active_time <= " . (time() - 60);
                }
                DaoFactory::getDao("Shard")->branchDb($shardId);
                $data = DaoFactory::getDao("Shard")->query($sql);
                foreach ($data as $value) {
                    $status_id .= ',' . $value['tp_appid'];
                }
                if ($data) {
                    $where = "id in (" . trim($status_id, ',') . ")";
                } else {
                    $where = "1!=1";
                }
            }
        }

        $ret = array();
        if (empty($appid)) {
            $sql = "select * from app where " . $where . " order by id desc limit " . $offset . ", " . $limit . "";
        } else if (is_array($appid)) {
            $sql = "select * from app where id in ('" . implode("','", $appid) . "') order by id desc limit " . $offset . ", " . $limit . "";
        } else {
            $sql = "select * from app where appid='" . $appid . "' order by id desc limit " . $offset . ", " . $limit . "";
        }


        $data = DaoFactory::getDao("Main")->query($sql);
        foreach ($data as $item) {
            $tpAppid = $item['id'];
            $appid = $item['appid'];
            DaoFactory::getDao("Shard")->branchDb($tpAppid);
            $sql = "select * from app_detail where tp_appid='" . $tpAppid . "' limit 1";
            $tmpData = DaoFactory::getDao("Shard")->query($sql);

            $sql = "select count(1) as num from bind where tp_appid ='" . $tpAppid . "'";
            $tmpData2 = DaoFactory::getDao("Main")->query($sql);
            $machineNum = $tmpData2[0]['num'];

            if (!empty($tmpData)) {
                $tmpData[0]['appid'] = $appid;
                if (1 == $tmpData[0]['phone_type']) {
                    $tmpData[0]['phone_type'] = "android";
                } else if (2 == $tmpData[0]['phone_type']) {
                    $tmpData[0]['phone_type'] = "ios";
                } else {
                    $tmpData[0]['phone_type'] = "unknown";
                }
                $tmpData[0]['machineNum'] = $machineNum;
                $ret[] = $tmpData[0];
            }
        }
        return $ret;
    }

    public function getMachineidList($tpAppid)
    {
        $ret = array();
        $sql = "select tp_machineid from bind where tp_appid='" . $tpAppid . "'";
        $data = DaoFactory::getDao("Main")->query($sql);
        foreach ($data as $item) {
            $ret[] = $item['tp_machineid'];
        }
        return $ret;
    }

    public function isActive($tpAppid)
    {
        $sql = "select last_active_time from app_detail where tp_appid = '" . $tpAppid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            $lastActiveTime = intval($data[0]['last_active_time']);
            if (time() - $lastActiveTime < 60) {
                //少于1分钟，就认为是活的
                return true;
            } else {
                return false;
            }
        }
    }

    public function updateLocation($tpAppid, $longitude, $latitude, $type = 0, $distance = 0)
    {
        if (empty($longitude) && empty($latitude)) {
            $sql = "update app_detail set location_type='" . $type . "', distance='" . $distance . "' where tp_appid='" . $tpAppid . "' limit 1";
        } else {
            $sql = "update app_detail set location_type='" . $type . "', distance='" . $distance . "', longitude='" . $longitude . "', latitude='" . $latitude . "' where tp_appid='" . $tpAppid . "' limit 1";
        }
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function getNearMachine($tpAppid, $onlineFlag, $bindFlag)
    {
        include APP_PATH . "/conf/shard.php";
        $sql = "select last_active_ip from app_detail where tp_appid='" . $tpAppid . "' limit 1 ";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return array();
        }
        $lastActiveIp = $data[0]['last_active_ip'];

        //根据ip找对应的机器

        $shard = $db_shard_config['shard'];
        //少于1分钟，就认为是活的
        $time = time() - 60;

        $machineArray = array();

        foreach ($shard as $key => $value) {
            $arr = explode("-", $key);
            $min = intval($arr[0]);
            $shardId = $min + 1;

            $sql = "select last_active_time, tp_machineid from machine_detail where last_active_ip='" . $lastActiveIp . "' and isdelete='0'";
            DaoFactory::getDao("Shard")->branchDb($shardId);
            $data = DaoFactory::getDao("Shard")->query($sql);
            foreach ($data as $item) {
                $lastActiveTime = $item['last_active_time'];
                $tpMachineid = $item['tp_machineid'];
                if ("online" == $onlineFlag) {
                    if ($lastActiveTime >= $time) {
                        $machineArray[] = $tpMachineid;
                    }
                } else if ("offline" == $onlineFlag) {
                    if ($lastActiveTime < $time) {
                        $machineArray[] = $tpMachineid;
                    }
                } else if ("all" == $onlineFlag) {
                    $machineArray[] = $tpMachineid;
                }
            }

        }
        if (empty($machineArray)) {
            return array();
        }

        //machineArray 是所有符合ip的

        //判断是否绑定
        if ("bind" == $bindFlag || "unbind" == $bindFlag) {
            $finalArray = array();
            $sql = "select * from bind where tp_appid='" . $tpAppid . "'";
            $data = DaoFactory::getDao("Main")->query($sql);
            foreach ($data as $item) {
                if (in_array($item['tp_machineid'], $machineArray)) {
                    $finalArray[] = $item['tp_machineid'];
                }
            }
            if ("unbind" == $bindFlag) {
                $finalArray = array_diff($machineArray, $finalArray);
            }
        } else {
            $finalArray = $machineArray;
        }

        $ret = array();
        $sql = "select machineid from machine where id in ('" . implode("','", $finalArray) . "')";
        $data = DaoFactory::getDao("Main")->query($sql);
        foreach ($data as $item) {
            $ret[] = $item['machineid'];
        }
        return $ret;
    }

    public function getAllMapData($status)
    {
        include_once APP_PATH . "/conf/shard.php";
        $shard = $db_shard_config['shard'];

        $ret = array();

        //$updateIpMapNum = 0;
        foreach ($shard as $key => $value) {
            $arr = explode("-", $key);
            $min = intval($arr[0]);
            $shardId = $min + 1;
            if (empty($status)) {
                $sql = "select longitude, latitude from app_detail where longitude != '' and latitude != ''";
            } elseif ($status == 1) {
                $sql = "select longitude, latitude from app_detail where longitude != '' and latitude != '' and last_active_time > " . (time() - 60);
            } elseif ($status == 2) {
                $sql = "select longitude, latitude from app_detail where longitude != '' and latitude != '' and last_active_time <= " . (time() - 60);
            }
            DaoFactory::getDao("Shard")->branchDb($shardId);
            $data = DaoFactory::getDao("Shard")->query($sql);

            foreach ($data as $item) {

                if (($item['longitude'] == 0 && $item['latitude'] == 0)) {
                    continue;
                }
                $item['longitude'] = floatval($item['longitude']) + 0.013;
                $item['latitude'] = floatval($item['latitude']) + 0.003;
                $ret[] = array(number_format($item['longitude'], 3, '.', ''), number_format($item['latitude'], 3, '.', ''), 1);
            }
        }
        return $ret;
    }

    public function getAppSettingSoundShock($tpAppid)
    {
        $sql = "select sound_remind, shock_remind from app_detail where tp_appid='" . $tpAppid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return array(
                "sound" => false,
                "shock" => false,
            );
        }
        $sound = true;
        if (empty($data[0]['sound_remind'])) {
            $sound = false;
        }
        $shock = true;
        if (empty($data[0]['shock_remind'])) {
            $shock = false;
        }
        return array(
            "sound" => $sound,
            "shock" => $shock,
        );
    }

    /**
     * @desc 保存配置(1台机器一个app一条记录)
     */
    public function saveHumidifierStartStop($tpAppid, $tpMachineid, $enableUserNearStart, $enableUserFarStop)
    {
        $sql = "update app_machine_config set enable_user_near_start='" . $enableUserNearStart . "', enable_user_far_stop='" . $enableUserFarStop . "' where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "'  limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 从统一配置哪里获取
     */
    public function getHumidifierStartStop($tpAppid, $tpMachineid)
    {
        $sql = "select * from app_machine_config where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' limit 1";
        //echo $sql."\n";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return array(
                "enableUserNearStart" => 0,
                "enableUserFarStop" => 0,
                "tooDryRemind" => 0,
                "noWaterRemind" => 0,
                "startAndStopRemind" => 0,
            );
        } else {
            return array(
                "enableUserNearStart" => $data[0]['enable_user_near_start'],
                "enableUserFarStop" => $data[0]['enable_user_far_stop'],
                "tooDryRemind" => $data[0]['humidifier_too_dry_remind'],
                "noWaterRemind" => $data[0]['humidifier_no_water_remind'],
                "startAndStopRemind" => $data[0]['humidifier_start_remind'],
            );
        }
    }

    /**
     * @desc 从统一配置哪里获取
     */
    public function getMachineConfig($tpAppid, $tpMachineid)
    {
        $sql = "select * from app_machine_config where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return array(
                "enable_user_near_start" => 0,
                "enable_user_far_stop" => 0,
                "start_stop_remind" => 0,
                "enable_night_mode" => 0
            );
        } else {
            return array(
                "enable_user_near_start" => $data[0]['enable_user_near_start'],
                "enable_user_far_stop" => $data[0]['enable_user_far_stop'],
                "start_stop_remind" => $data[0]['start_stop_remind'],
                "enable_night_mode" => $data[0]['enable_night_mode']
            );
        }
    }

    /**
     * @desc 从统一配置哪里获取
     */
    public function setMachineConfig($tpMachineid, $tpAppid, $data)
    {
        return $this->insertOrUpdate("app_machine_config", $tpMachineid, $tpAppid, $data);
    }

    public function update($tpAppid, $data)
    {
        $sep = "";
        $sql = "update app_detail set ";
        foreach ($data as $k => $v) {
            $sql .= $sep . $k . "='" . $v . "'";
            $sep = ",";
        }
        $sql .= " where tp_appid='" . $tpAppid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function updateConfig($tpAppid, $tpMachineid, $data)
    {
        $sql = "select 1 from app_machine_config where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $flag = DaoFactory::getDao("Shard")->query($sql);
        if ($flag) {
            $sep = "";
            $sql = "update app_machine_config set ";
            foreach ($data as $k => $v) {
                $sql .= $sep . $k . "='" . $v . "'";
                $sep = ",";
            }
            $sql .= " where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' limit 1";
            DaoFactory::getDao("Shard")->branchDb($tpAppid);
            return DaoFactory::getDao("Shard")->query($sql);
        } else {
            $sep = "";
            $sql = "insert into app_machine_config set ";
            foreach ($data as $k => $v) {
                $sql .= $sep . $k . "='" . $v . "'";
                $sep = ",";
            }
            $sql .= ", tp_appid='" . $tpAppid . "', tp_machineid='" . $tpMachineid . "'";
            DaoFactory::getDao("Shard")->branchDb($tpAppid);
            return DaoFactory::getDao("Shard")->query($sql);
        }
    }

    public function checkOpenGps($tpAppid)
    {
        //找出该appid绑定的电器id
        $ret = array();
        $sql = "select bind.tp_machineid, machine.machineid from bind, machine where bind.tp_appid='" . $tpAppid . "' and bind.tp_machineid=machine.id";
        $data = DaoFactory::getDao("Main")->query($sql);
        $tpMachineidArray = array();
        foreach ($data as $item) {
            $tpMachineid = $item['tp_machineid'];
            $tpMachineidArray[] = $tpMachineid;
        }
        $sql = "select 1 from app_machine_config where tp_appid='" . $tpAppid . "' and tp_machineid in ('" . implode("','", $tpMachineidArray) . "') and (enable_user_near_start='1' or enable_user_far_stop='1') limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return true;
        }
    }
}

