<?php
namespace services;

use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

include_once "MCommonService.php";

class Light extends MCommonService
{
    public function __construct()
    {
    }

    /**
     * @desc 获取使用日志
     * fei 2015-04-10 不限制appid
     */
    public function getActionLogList($tpMachineid, $offset, $limit)
    {
        $ret = array();
        $sql = "select * from light_action_log where tp_machineid='" . $tpMachineid . "' order by id desc limit " . $offset . ", " . $limit . "";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return array();
        } else {
            foreach ($data as $item) {
                $ret[] = array(
                    "machineid" => $item['machineid'],
                    "operation" => $item['operation'],
                    "starttime" => date("Y-m-d H:i:s", $item['starttime']),
                    "costtime" => $item['costtime'],
                    "createtime" => $item['createtime'],
                    "lightness" => $item['lightness'],
                    "temperature" => $item['temperature'],
                    "red" => $item['red'],
                    "green" => $item['green'],
                    "blue" => $item['blue'],
                    "energy" => $item['energy'],
                );
            }
        }
        return $ret;
    }

    /**
     * @desc 添加使用记录
     */
    public function actionLog($tpMachineid, $machineid, $tpAppid, $appid, $operation, $starttime, $costtime, $energy, $lightness, $temperature, $red, $green, $blue)
    {
        if ($lightness == null) {
            $sql = "insert into light_action_log (tp_machineid, machineid, tp_appid, appid, operation, starttime, costtime, createtime) values('" . $tpMachineid . "', '" . $machineid . "', '" . $tpAppid . "', '" . $appid . "', '" . $operation . "', '" . $starttime . "', '" . $costtime . "', '" . time() . "')";
        } else {
            $sql = "insert into light_action_log (tp_machineid, machineid, tp_appid, appid, operation, starttime, costtime, createtime, energy, lightness, temperature, red, green, blue) values('" . $tpMachineid . "', '" . $machineid . "', '" . $tpAppid . "', '" . $appid . "', '" . $operation . "', '" . $starttime . "', '" . $costtime . "', '" . time() . "', '" . $energy . "', '" . $lightness . "', '" . $temperature . "', '" . $red . "', '" . $green . "', '" . $blue . "')";
        }
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 更新状态
     */
    public function updateState($tpMachineid, $machineid, $tpAppid, $appid, $lightness, $temperature, $red, $green, $blue, $state, $energy)
    {
        $sql = "select state, last_start_time from light_state where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        $time = time();
        if (empty($data)) {
            $sql = "insert into light_state (tp_machineid, machineid, lightness, temperature, red, green, blue, state, last_update_time) values('" . $tpMachineid . "', '" . $machineid . "', '" . $lightness . "', '" . $temperature . "', '" . $red . "', '" . $green . "', '" . $blue . "', '" . $state . "', '" . time() . "')";
        } else {
            $currentState = $data[0]['state'];
            if ('0' == $currentState && '1' == $state) {
                $sql = "update light_state set state='" . $state . "', last_update_time='" . $time . "', last_start_time='" . $time . "' where tp_machineid='" . $tpMachineid . "' limit 1";
            } else {
                if ('1' == $currentState && '0' == $state) {
                    $lastStartTime = $data[0]['last_start_time'];
                    $costtime = $time - $lastStartTime;
                    $this->actionLog($tpMachineid, $machineid, $tpAppid, $appid, '0', $lastStartTime, $costtime, $energy, $lightness, $temperature, $red, $green, $blue);
                }
                $sql = "update light_state set lightness='" . $lightness . "', temperature='" . $temperature . "', red='" . $red . "', green='" . $green . "', blue='" . $blue . "',  state='" . $state . "', last_update_time='" . time() . "' where tp_machineid='" . $tpMachineid . "' limit 1";
            }
        }
        //file_put_contents("/tmp/sql.log", $sql . "\n", FILE_APPEND);
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $ret = DaoFactory::getDao("Shard")->query($sql);
        return $ret;
    }

    /**
     * @desc 获取最新状态
     */
    public function getState($tpMachineid)
    {
        $sql = "select * from light_state where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            $lightness = $data[0]['lightness'];
            $temperature = $data[0]['temperature'];
            $red = $data[0]['red'];
            $green = $data[0]['green'];
            $blue = $data[0]['blue'];
            $state = $data[0]['state'];
            return array(
                "lightness" => $lightness,
                "temperature" => $temperature,
                "red" => $red,
                "blue" => $blue,
                "green" => $green,
                "state" => $state,
            );
        }
    }

    /**
     * @desc 获取附近的app
     */
    public function getNearApp($tpMachineid)
    {
        $sql = "select last_active_ip from machine_detail where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        }
        $lastActiveIp = $data[0]['last_active_ip'];

        //找出和该电器绑定的app
        $sql = "select * from bind where tp_machineid='" . $tpMachineid . "'";
        $data = DaoFactory::getDao("Main")->query($sql);
        $tpAppidArray = array();
        foreach ($data as $item) {
            $tpAppidArray[] = $item['tp_appid'];
        }

        $time = time() - 5 * 60;
        $okTpAppid = "";

        //判断是否有app相同ip在线的
        foreach ($tpAppidArray as $tpAppid) {
            $sql = "select * from app_detail where last_active_ip='" . $lastActiveIp . "' and tp_appid='" . $tpAppid . "' and last_active_time >" . $time . " limit 1";
            DaoFactory::getDao("Shard")->branchDb($tpAppid);
            $data = DaoFactory::getDao("Shard")->query($sql);
            if (!empty($data)) {
                $okTpAppid = $tpAppid;
                break;
            }
        }

        if (empty($okTpAppid)) {
            return false;
        }
        $sql = "select appid from app where id='" . $tpAppid . "' limit 1";
        $data = DaoFactory::getDao("Main")->query($sql);
        if (empty($data)) {
            return false;
        }
        return $data[0]['appid'];
    }

    public function getStateForAdmin($tpMachineid)
    {
        $sql = "select * from light_state where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return false;
        } else {
            return array(
                "state" => $data[0]['state'],
                "last_update_time" => $data[0]['last_update_time'],
                "lightness" => $data[0]['lightness'],
                "temperature" => $data[0]['temperature'],
                "red" => $data[0]['red'],
                "blue" => $data[0]['blue'],
                "green" => $data[0]['green']
            );
        }
    }

    private $DB_NAME_ACTION = "light";
    private $DB_NAME_ACTION_LOG = "light_action_log";

    /**
     * @desc 获取电器的操作记录(显示全部人的操作记录)
     */
    public function getActionLogNum($tpMachineid)
    {
        return $this->getCommonActionLogNum($tpMachineid, $this->DB_NAME_ACTION_LOG);
    }

    /**
     * @desc 获取使用记录统计
     * @param $tpMachineid
     * @return array
     * @throws \base\Yaf_Exception_StartupError
     */
    public function getUseStat($tpMachineid)
    {
        $dayArray = array();
        $useNum = 0;
        $data = $this->query($tpMachineid, "select * from " . $this->DB_NAME_ACTION_LOG . " where tp_machineid='" . $tpMachineid . "'");
        foreach ($data as $item) {
            $useNum++;
            $createtime = $item['createtime'];
            $date = date("Ymd", $createtime);
            if (!isset($dayArray[$date])) {
                $dayArray[$date] = 1;
            }
        }

        $useDay = count($dayArray);

        return array(
            "useDay" => $useDay,
            "useNum" => $useNum,
        );
    }

    /**
     * @desc 新增/更新
     */
    public function update($tpMachineid, $data)
    {
        $part1 = "";
        $part2 = "";
        $sep = "";
        $sql = "";
        foreach ($data as $key => $value) {
            $part1 .= $sep . $key;
            $part2 .= $sep . "'" . mysql_escape_string($value) . "'";
            $sep = ",";
        }
        $sql = "replace light_config (" . $part1 . ") values (" . $part2 . ")";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 获取配置
     */
    public function getConfig($tpMachineid)
    {
        $sql = "select * from light_config where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            $data = array(
                "city_id" => "101280101", //广州
            );
        } else {
            $data = $data[0];
        }

        $cityname = "";
        $citytemptop = "";
        $citytempbottom = "";
        $cityhumidity = "";
        $grade2humiditytop = "70%";
        $grade2humiditybottom = "40%";

        $cityId = $data['city_id'];
        if (!empty($cityId)) {
            $sql = "select * from city where city_id='" . $cityId . "' limit 1";
            $data1 = DaoFactory::getDao("Main")->query($sql);
            if (!empty($data1)) {
                $cityname = $data1[0]['city_name'];
                $citytemptop = intval($data1[0]['temp_top']) . "C";
                $citytempbottom = intval($data1[0]['temp_bottom']) . "C";
                $humidity = intval($data1[0]['humidity_top']);
                $cityhumidity = intval($data1[0]['humidity_top']) . "%";
                $grade2humiditytop = ($humidity + 15) . "%";
                $bottom = $humidity - 15;
                if ($bottom < 5) {
                    $bottom = "5";
                }
                $grade2humiditybottom = $bottom . "%";

                if ($citytemptop >= 27) {
                    $grade2humiditytop = "55%";
                    $grade2humiditybottom = "35%";
                } else {
                    $grade2humiditytop = "65%";
                    $grade2humiditybottom = "45%";
                }
            }
        }

        return array(
            "cityid" => $cityId,
            "cityname" => $cityname,
            "citytemptop" => $citytemptop,
            "citytempbottom" => $citytempbottom,
            "cityhumidity" => $cityhumidity,
            "grade2humiditytop" => $grade2humiditytop,
            "grade2humiditybottom" => $grade2humiditybottom,
            "drymode" => isset($data['drymode']) ? $data['drymode'] : "50%", //grade1
            "wetmode" => isset($data['wetmode']) ? $data['wetmode'] : "150%", //grade3
        );
    }

    /**
     * @desc 判断加湿器是否要工作，及工作的相关参数
     */
    public function getWork($tpMachineid)
    {
        //light_work里面，一台机器只有一条记录
        $sql = "select * from light_work where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        $noWorkFlag = false;
        if (empty($data)) {
            return "";
        }
        $data = $data[0];
        $lightness = intval($data['lightness']);
        $temperature = intval($data['temperature']);
        $red = intval($data['red']);
        $green = intval($data['green']);
        $blue = intval($data['blue']);
        $run = intval($data['run']);

        $flag1 = ServiceFactory::getService("Machine")->haveAppOnline($tpMachineid);
        if ($flag1) {
            $fastheartbeatmode = "1";
        } else {
            $fastheartbeatmode = "0";
        }

        return array(
            "run" => trim($run), //是关闭状态的
            "l" => trim($lightness),
            "t" => trim($temperature),
            "r" => trim($red),
            "g" => trim($green),
            "b" => trim($blue),
            "h" => trim($fastheartbeatmode),
        );
    }

    public function requestCallback($tpMachineid)
    {
        $sql = "delete from light_work where tp_machineid='" . $tpMachineid . "' order by id asc limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 新增任务
     */
    public function addWork($tpMachineid, $data)
    {
        $part1 = "";
        $part2 = "";
        $sep = "";
        $sql = "";
        foreach ($data as $key => $value) {
            $part1 .= $sep . $key;
            $part2 .= $sep . "'" . mysql_escape_string($value) . "'";
            $sep = ",";
        }
        $sql = "insert into light_work (" . $part1 . ") values (" . $part2 . ")";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 停止工作
     */
    public function stopWork($tpMachineid)
    {
        //这里不限制limit 1
        $sql = "delete from light_work where tp_machineid='" . $tpMachineid . "'";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 根据距离判断是否应该自动开启
     */
    public function canStartByXY($tpMachineid, $enableUserNearStart, $enableUserFarStop)
    {

        //获取操作这个电器的最后tpAppid
        $lastTpAppid = $this->getLastTpAppid($tpMachineid);

        //获取关联的appid
        $tpAppidArray = ServiceFactory::getService("Machine")->getAppidList($tpMachineid);
        if (empty($tpAppidArray)) {
            return 0;
        }

        //获取这个电器的坐标
        $machineDetail = ServiceFactory::getService("Machine")->getDetail($tpMachineid);
        if (empty($machineDetail)) {
            return 0;
        }
        $machineLongitude = $machineDetail['longitude'];
        $machineLatitude = $machineDetail['latitude'];
        $machineLongitudeAndroid = $machineDetail['longitude_android'];
        $machineLatitudeAndroid = $machineDetail['latitude_android'];

        file_put_contents("/tmp/fei.log", date("Y-m-d H:i:s") . " " . $tpMachineid . " " . $machineLongitude . ", " . $machineLatitude . ", " . $machineLongitudeAndroid . ", " . $machineLatitudeAndroid . "\n", FILE_APPEND);
        if (empty($machineLongitude) && empty($machineLatitude) && empty($machineLongitudeAndroid) && empty($machineLatitudeAndroid)) {
            return 0;
        }

        $machineLongitude = floatval($machineLongitude);
        $machineLatitude = floatval($machineLatitude);
        $machineLongitudeAndroid = floatval($machineLongitudeAndroid);
        $machineLatitudeAndroid = floatval($machineLatitudeAndroid);


        //距离阀值

        $distance = 1000; //这里区分不了ios和android
        $autoStartDistance = 1000;
        $autoStopDistance = 2000;
        $rawDistance = 999999999;
        $startMinLen = $rawDistance;
        $stopMinLen = $rawDistance;
        $autoStartTouchTpAppid = 0; //谁触发智能开的

        foreach ($tpAppidArray as $tpAppid) {
            //要有活跃的app
            //TODO app需要有后台定时请求，否则会不工作的
            //这里不是的，这里没有return,而只是continue,他会继续会上一次的状态
            if (!ServiceFactory::getService("App")->isActive($tpAppid)) {
                continue;
            }
            $appDetail = ServiceFactory::getService("App")->getDetail($tpAppid);
            $appLongitude = $appDetail['longitude'];
            $appLatitude = $appDetail['latitude'];


            file_put_contents("/tmp/len.log", date("Y-m-d H:i:s") . " tpMachineid=" . $tpMachineid . ", ios=(" . $machineLongitude . ", " . $machineLatitude . ") android=(" . $machineLongitudeAndroid . ", " . $machineLatitudeAndroid . "), tpAppid=" . $tpAppid . ", (" . $appLongitude . ", " . $appLatitude . ")\n", FILE_APPEND);

            if (empty($appLongitude) && empty($appLatitude)) {
                continue;
            }

            $startStopDetail = ServiceFactory::getService("App")->getHumidifierStartStop($tpAppid, $tpMachineid);
            $startFlag = $startStopDetail['enableUserNearStart'];
            $stopFlag = $startStopDetail['enableUserFarStop'];

            //计算2点间的距离
            $appLongitude = floatval($appLongitude);
            $appLatitude = floatval($appLatitude);

            if (2 == $appDetail['phone_type'] && !empty($machineLongitudeAndroid) && !empty($machineLatitudeAndroid)) {
                $p1 = ($machineLongitude - $appLongitude);
                $p2 = ($machineLatitude - $appLatitude);
            } else {
                $p1 = ($machineLongitudeAndroid - $appLongitude);
                $p2 = ($machineLatitudeAndroid - $appLatitude);
            }

            $len = sqrt($p1 * $p1 + $p2 * $p2) * 1000000;

            if ($startFlag) {
                if ($len < $startMinLen) {
                    $startMinLen = $len;
                    $autoStartTouchTpAppid = $tpAppid; //记录是谁触发他智能开的
                    file_put_contents("/tmp/len.log", "tpMachineid=" . $tpMachineid . ", autoStartTouchTpAppid=" . $tpAppid . ", len=" . $len . "\n", FILE_APPEND);
                }
            }

            //只用最后操作的用户来实现智能开关, 0 == lastTpAppid就是手动模式
            if ($stopFlag && ($lastTpAppid == $tpAppid || 0 == $lastTpAppid)) {
                file_put_contents("/tmp/len.log", "ssssssssssssssssssssssssssss\n", FILE_APPEND);
                if ($len < $stopMinLen) {
                    $stopMinLen = $len;
                }
            }

            file_put_contents("/tmp/len.log", "tpMachineid=" . $tpMachineid . ", tpAppid=" . $tpAppid . ", len=" . $len . "\n", FILE_APPEND);

        } //end of foreach


        // 1000 =60米
        //判断是否要开
        if ($startMinLen != $rawDistance) {
            //开的情况有一个满足就可以返回
            if ($startMinLen <= $autoStartDistance) {
                //11111111-0000-1111-1111-111111111111 智能的appid
                $sql = "update humidifier_state set last_tp_appid='" . $autoStartTouchTpAppid . "', last_appid='11111111-0000-1111-1111-111111111111' where tp_machineid='" . $tpMachineid . "' limit 1";
                DaoFactory::getDao("Shard")->branchDb($tpMachineid);
                DaoFactory::getDao("Shard")->query($sql);

                $data = array(
                    "tp_machineid" => $tpMachineid,
                    "run" => 1,
                    "starttime" => 0,
                    "grade" => 0,
                    "is_summer_mode" => 0,
                );
                //FIXME 这里是否会导致不断插入数据库
                file_put_contents("/tmp/len.log", "tpMachineid=" . $tpMachineid . ", autoStartTouchTpAppid=" . $tpAppid . ", add start work\n", FILE_APPEND);
                $flag = ServiceFactory::getService("Humidifier")->addWork($tpMachineid, $data);
                return 1;
            }
        }

        if (1) {
            //判断是否要关闭
            if ($stopMinLen != $rawDistance) {
                //关这里，需要所有app都是远离才能返回
                if ($stopMinLen > $autoStopDistance) {
                    $data = array(
                        "tp_machineid" => $tpMachineid,
                        "run" => 0,
                        "starttime" => 0,
                        "grade" => 0,
                        "is_summer_mode" => 0,
                    );
                    //FIXME 这里是否会导致不断插入数据库
                    $flag = ServiceFactory::getService("Humidifier")->addWork($tpMachineid, $data);
                    return 0;
                }
            }
        }

        return 2;
    }

    /**
     * @desc 更新最后启动的时间
     */
    public function updateLastStartTime($tpMachineid)
    {
        $sql = "update light_state set last_start_time='" . time() . "' where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 复位最后启动的时间
     */
    public function resetLastStartTime($tpMachineid)
    {
        $sql = "update light_state set last_start_time='0' where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 设置最后使用的挡数
     */
    public function setLastGrade($tpMachineid, $lastGrade = 2)
    {
        $sql = "update light_state set last_grade='" . $lastGrade . "' where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 获取最后使用的挡数
     */
    public function getLastGrade($tpMachineid)
    {
        $sql = "select last_grade from light_state where tp_machineid='" . $tpMachineid . "' limit 1";
        //file_put_contents("/tmp/sql.log", $sql . "\n", FILE_APPEND);
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return 2;
        } else {
            return intval($data[0]['last_grade']);
        }
    }

    /**
     * @desc 获取最后使用的挡数
     */
    public function getLastAppid($tpMachineid)
    {
        $sql = "select last_appid from light_state where tp_machineid='" . $tpMachineid . "' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return "";
        } else {
            return trim($data[0]['last_appid']);
        }
    }

    public function getLastTpAppid($tpMachineid)
    {
        $sql = "select last_tp_appid from humidifier_state where tp_machineid='" . $tpMachineid . "' limit 1";
        //file_put_contents("/tmp/sql.log", $sql . "\n", FILE_APPEND);
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            return "";
        } else {
            return trim($data[0]['last_tp_appid']);
        }
    }
}

