<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class Humidifier extends Service
{
	public function __construct(){
	}

    public function getActionLogNumForAdmin($tpMachineid)
    {
        $sql = "select count(1) as num from humidifier_action_log where tp_machineid='".$tpMachineid."'"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return 0;
        }
        else
        {
            return $data[0]['num'];
        }
    }

    public function getActionLogNum($tpMachineid, $tpAppid)
    {
        $sql = "select count(1) as num from humidifier_action_log where tp_machineid='".$tpMachineid."'"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return 0;
        }
        else
        {
            return $data[0]['num'];
        }
    }

    /**
     * @desc 获取使用日志
             fei 2015-04-10 不限制appid
     */
    public function getActionLogList($tpMachineid, $tpAppid, $offset, $limit)
    {
        $ret = array();
        $sql = "select * from humidifier_action_log where tp_machineid='".$tpMachineid."' order by id desc limit ".$offset.", ".$limit.""; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return array();
        }
        else
        {
            foreach($data as $item)
            {
                $ret[] = array(
                    "operation"=>$item['operation'], 
                    "starttime"=>date("Y-m-d H:i:s", $item['starttime']), 
                    "costtime"=>$item['costtime'],
                    "humidity"=>$item['humidity'], //30%~60%
                ); 
            }
        }
        return $ret;
    }

    //这个接口是不区分使用者的
    //TODO
    public function getActionLogListForAdmin($tpMachineid, $offset, $limit)
    {
        $ret = array();
        $sql = "select * from humidifier_action_log where tp_machineid='".$tpMachineid."' order by id desc limit ".$offset.", ".$limit.""; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return array();
        }
        else
        {
            foreach($data as $item)
            {
                $ret[] = array(
                    "machineid"=>$item['machineid'], 
                    "operation"=>$item['operation'], 
                    "starttime"=>date("Y-m-d H:i:s", $item['starttime']), 
                    "costtime"=>$item['costtime'],
                    "humidity"=>$item['humidity'], //30%~60%
                ); 
            }
        }
        return $ret;
    }

    /**
     * @desc 添加使用记录
     */
    public function actionLog($tpMachineid, $machineid, $tpAppid, $appid, $operation, $starttime, $costtime, $humidity, $energy)
    {
        $sql = "insert into humidifier_action_log (tp_machineid, machineid, tp_appid, appid, operation, starttime, costtime, humidity, createtime, energy) values('".$tpMachineid."', '".$machineid."', '".$tpAppid."', '".$appid."', '".$operation."', '".$starttime."', '".$costtime."', '".$humidity."', '".time()."', '".$energy."')"; 

        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 只做更新状态,不会去更新水位
     */
    public function updateStateOnly($tpMachineid, $state, $tpAppid='', $appid='')
    {
        $state = intval($state);
        //fei 2015-04-26 不更新状态
        $sql = "update humidifier_state set last_tp_appid='".$tpAppid."', last_appid='".$appid."' where tp_machineid='".$tpMachineid."' limit 1"; 
        //saveSql($sql);
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }
  
    /**
     * @desc 更新状态
     */
    public function updateState($tpMachineid, $machineid, $humidity, $level, $state)
    {
        $state = intval($state);
        $sql = "select level, state from humidifier_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);

        $lastState = 0;
        $lastLevel = 0;
        $lastHumidity = 0;
        $newLevel = 0;

        if(empty($data))
        {
            $sql = "insert into humidifier_state (tp_machineid, machineid, humidity, level, state, last_update_time) values('".$tpMachineid."', '".$machineid."', '".$humidity."', '".$level."', '".$state."', '".time()."')"; 

        }
        else
        {
            $lastState = intval($data[0]['state']);
            $lastLevel = $data[0]['level'];
            $lastLevel = str_replace(array("L", "l"), array("", ""), $lastLevel);
            $lastLevel = intval($lastLevel);

            $lastHumidity = $data[0]['humidity'];
            $lastHumidity = str_replace("%", "", $lastHumidity);
            $lastHumidity = intval($lastHumidity);

            $newLevel = $level;
            $newLevel = str_replace(array("L", "l"), array("", ""), $newLevel);
            $newLevel = intval($newLevel);

            $sql = "update humidifier_state set humidity='".$humidity."', level='".$level."', state='".$state."', last_update_time='".time()."' where tp_machineid='".$tpMachineid."' limit 1"; 
        }
        //saveSql($sql);
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $ret = DaoFactory::getDao("Shard")->query($sql);

        //缺水提醒
        if(1 === $lastLevel && 0 === $newLevel)
        {
            file_put_contents("/tmp/qushui.log", date("Y-m-d H:i:s")." ".$tpMachineid."\n", FILE_APPEND);
            //缺水提示
            ServiceFactory::getService("PushMsg")->pushHumidifierNoWater($tpMachineid); 
        }

        //上一次的湿度是大于40
        if($lastHumidity >= 40)
        {
            $humidity = str_replace("%", "", $humidity);
            $humidity = intval($humidity);
            if($humidity < 40 && $humidity > 0)
            {
                //干燥提醒
                file_put_contents("/tmp/toodry.log", date("Y-m-d H:i:s")." ".$tpMachineid."\n", FILE_APPEND);
                ServiceFactory::getService("PushMsg")->pushHumidifierTooDry($tpMachineid); 
            }
        }

        //启动关闭提醒
        if(in_array($state, array(1, 2)) && 0 == $lastState)
        {
            ServiceFactory::getService("PushMsg")->pushHumidifierStart($tpMachineid); 

            $sql = "delete from humidifier_work where tp_machineid='".$tpMachineid."' and run='1'";
            DaoFactory::getDao("Shard")->query($sql);
        }
        else if(in_array($state, array(0, 3)) && in_array($lastState, array(1, 2)))
        {
            ServiceFactory::getService("PushMsg")->pushHumidifierStop($tpMachineid); 
            $sql = "delete from humidifier_work where tp_machineid='".$tpMachineid."' and run='0'";
            DaoFactory::getDao("Shard")->query($sql);
        }

        return $ret;
    }

    /**
     * @desc 获取最新状态
     */
    public function getState($tpMachineid)
    {
        $sql = "select * from humidifier_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return false;
        }
        else
        {
            return array(
                "humidity"=>$data[0]['humidity'],
                "level"=>$data[0]['level'],
                "state"=>$data[0]['state'],
                "anion"=>$data[0]['anion'],
                "laststarttime"=>$data[0]['last_start_time'],
                "lasttpappid"=>$data[0]['last_tp_appid'],
                "lastappid"=>$data[0]['last_appid'],
            );
        }
    }

    public function getNearApp($tpMachineid)
    {
        $sql = "select last_active_ip from machine_detail where tp_machineid='".$tpMachineid."' limit 1"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return false;
        }
        $lastActiveIp = $data[0]['last_active_ip'];

        //找出和该电器绑定的app
        $sql = "select * from bind where tp_machineid='".$tpMachineid."'"; 
        $data = DaoFactory::getDao("Main")->query($sql);
        $tpAppidArray = array();
        foreach($data as $item)
        {
            $tpAppidArray[] = $item['tp_appid'];
        }

        $time = time() - 5 * 60;
        $okTpAppid = "";

        //判断是否有app相同ip在线的
        foreach($tpAppidArray as $tpAppid)
        {
            $sql = "select * from app_detail where last_active_ip='".$lastActiveIp."' and tp_appid='".$tpAppid."' and last_active_time >".$time." limit 1";
            DaoFactory::getDao("Shard")->branchDb($tpAppid);
            $data = DaoFactory::getDao("Shard")->query($sql);   
            if(!empty($data))
            {
                $okTpAppid = $tpAppid;
                break;
            }
        }

        if(empty($okTpAppid))
        {
            return false;
        }
        $sql = "select appid from app where id='".$tpAppid."' limit 1";
        $data = DaoFactory::getDao("Main")->query($sql);   
        if(empty($data))
        {
            return false;
        }
        return $data[0]['appid'];
    }

    public function getStateForAdmin($tpMachineid)
    {   
        $sql = "select * from humidifier_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {   
            return false;
        }   
        else
        {   
            return array(
                "level"=>$data[0]['level'],
                "humidity"=>$data[0]['humidity'],
                "state"=>$data[0]['state'],
                "last_update_time"=>$data[0]['last_update_time'],
            );  
        }   
    } 

    /**
     * @desc 获取使用统计
     */
    public function getHumidifierUseStat($tpMachineid)
    {
        $dayArray = array();
        $tempArray = array();
        $totalLevel = 0;
        $totalTime = 0;
        $maxTemp = "";
        $maxTempNum = 0;
        $useNum = 0;
        $sql = "select * from humidifier_action_log where tp_machineid='".$tpMachineid."'"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        foreach($data as $item)
        {
            $useNum++;
            $createtime = $item['createtime']; 
            $temp = $item['middle_humidity']; 
            if(empty($maxTempNum))
            {
                $maxTempNum = 1; 
                $maxTemp = $temp;
            }

            if(!isset($tempArray[$temp]))
            {
                $tempArray[$temp] = 1;
            }
            else
            {
                $tempArray[$temp]++;
                if($tempArray[$temp] > $maxTempNum)
                {
                    $maxTempNum = $tempArray[$temp]; 
                    $maxTemp = $temp;
                }
            }

            $startLevel = $item['start_level']; 
            $startLevel = strtolower($startLevel);
            $startLevel = str_replace("l", "", $startLevel);
            $startLevel = floatval($startLevel);

            $endLevel = $item['end_level']; 
            $endLevel = strtolower($endLevel);
            $endLevel = str_replace("l", "", $endLevel);
            $endLevel = floatval($endLevel);

            $totalLevel += $startLevel - $endLevel; 

            $startTime = strtotime($item['starttime']);
            $endTime = strtotime($item['endtime']);
            $totalTime += $endTime - $startTime;

            $date = date("Ymd", $createtime);
            if(!isset($dayArray[$date]))
            {
                $dayArray[$date] = 1;    
            }
        }

        $useDay = count($dayArray);

        return array(
            "useDay"=>$useDay,
            "useNum"=>$useNum,
            "totalLevel"=>$totalLevel,
            "totalTime"=>$totalTime,
            "maxHumidity"=>$maxTemp,
            "maxHumidityNum"=>$maxTempNum,
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
        foreach($data as $key=>$value)
        {
            $part1 .= $sep.$key;
            $part2 .= $sep."'".mysql_escape_string($value)."'";
            $sep = ",";
        }
        $sql = "replace humidifier_config (".$part1.") values (".$part2.")";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 获取配置
     */
    public function getConfig($tpMachineid)
    {
        $sql = "select * from humidifier_config where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            $data = array(
                    "city_id" => "101280101", //广州
                );
        }
        else
        {
            $data = $data[0];
        }

        $cityname = "";
        $citytemptop = "";
        $citytempbottom = "";
        $cityhumidity = "";
        $grade2humiditytop = "70%";
        $grade2humiditybottom = "40%";

        $cityId = $data['city_id'];
        if(!empty($cityId))
        {
            $sql = "select * from city where city_id='".$cityId."' limit 1";   
            $data1 = DaoFactory::getDao("Main")->query($sql);
            if(!empty($data1))
            {
                $cityname = $data1[0]['city_name'];
                $citytemptop = intval($data1[0]['temp_top'])."C";
                $citytempbottom = intval($data1[0]['temp_bottom'])."C";
                $humidity = intval($data1[0]['humidity_top']);
                $cityhumidity = intval($data1[0]['humidity_top'])."%";
                $grade2humiditytop = ($humidity + 15)."%";
                $bottom = $humidity - 15;
                if($bottom <5)
                {
                    $bottom = "5"; 
                }
                $grade2humiditybottom = $bottom."%"; 

                /*
                if($citytemptop >= 27)
                {
                    $grade2humiditytop = "55%";
                    $grade2humiditybottom = "35%";
                }
                else
                {
                */
                    $grade2humiditytop = "65%";
                    $grade2humiditybottom = "45%";
                /*
                }
                */
            }
        }

        return array(
            "cityid"=>$cityId,
            "cityname"=>$cityname,
            "citytemptop"=>$citytemptop,
            "citytempbottom"=>$citytempbottom,
            "cityhumidity"=>$cityhumidity,
            "grade2humiditytop"=>$grade2humiditytop,
            "grade2humiditybottom"=>$grade2humiditybottom,
            "drymode"=>isset($data['drymode'])?$data['drymode']:"50%", //grade1
            "wetmode"=>isset($data['wetmode'])?$data['wetmode']:"150%", //grade3
        ); 
    }

    /**
     * @desc 判断加湿器是否要工作，及工作的相关参数
     */
    public function getWork($tpMachineid)
    {
        //humidifier_work里面，一台机器只有一条记录
        $sql = "select * from humidifier_work where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        //$noWorkFlag = false;
        $run = 0;
        if(empty($data))
        {
            saveWork($tpMachineid, "in getWork db have no task");
            //$noWorkFlag = true;
            //无记录就获取最后的状态
            $state = $this->getState($tpMachineid); 
            if(in_array($state['state'], array(1, 2)))
            {
                $run = 1; 
            }
            else
            {
                $run = 0;
            }

        }
        else
        {
            $data = $data[0];
            $grade = intval($data['grade']);
            $anion = intval($data['anion']);
            $run = intval($data['run']);
        }

        if(0 == $run)
        {
            saveWork($tpMachineid, "in getWork db have no task2, run=0");
            //$noWorkFlag = true; 
        }

        $flag1 = ServiceFactory::getService("Machine")->haveAppOnline($tpMachineid);
        if($flag1)
        {
            $fastheartbeatmode = "1"; 
        }
        else
        {
            $fastheartbeatmode = "0"; 
        }

        //获取配置
        $config = $this->getConfig($tpMachineid);
        $starttme = intval($data['starttme']);

        $flag = 0;

        //开了智能
        $flag = $this->canStartByXY($tpMachineid, $config['enableusernearstart'], $config['enableuserfarstop']); 
        saveWork($tpMachineid, "in getWork canStartByXY return flag=".$flag."");
        //关闭状态

        //智能的级别是最高
        if(0 == $flag)
        {
            return array(
                    "step"=>"2",
                    "run"=>"0", //是关闭状态的
                    "anion"=>trim($anion),
                    "top"=>"",
                    "bottom"=>"",
                    "fastheartbeatmode"=>trim($fastheartbeatmode),
                );

        }
        else if(1 == $flag)
        {
            return array(
                    "step"=>"2.1",
                    "run"=>"1", //是开启状态的
                    "anion"=>trim($anion),
                    "top"=>"",
                    "bottom"=>"",
                    "fastheartbeatmode"=>trim($fastheartbeatmode),
                );
        }
        else
        {
            //保存原来的 
        }

        /*
        if($noWorkFlag)
        {
            return array(
                    "step"=>"4",
                    "run"=>"0", //是关闭状态的
                    "anion"=>trim($anion),
                    "top"=>"",
                    "bottom"=>"",
                    "fastheartbeatmode"=>trim($fastheartbeatmode),
                    );
        
        }
        */

        if(empty($grade))
        {
            //假如没有grade,就去获取最后使用的grade
            $grade = $this->getLastGrade($tpMachineid); 
            $anion = $this->getLastAnion($tpMachineid); 
        }
        
        $top = "";
        $bottom = "";
        
        $drymode = trim($config['drymode']);
        $drymode = intval(str_replace("%", "", $drymode));
        $wetmode = trim($config['wetmode']);
        $wetmode = intval(str_replace("%", "", $wetmode));
	
        $grade2humiditytop = trim($config['grade2humiditytop']);
        $grade2humiditytop = intval(str_replace("%", "", $grade2humiditytop));

        $grade2humiditybottom = trim($config['grade2humiditybottom']);
        $grade2humiditybottom = intval(str_replace("%", "", $grade2humiditybottom));

        switch($grade)
        {
            case 1:
                $top = intval($grade2humiditytop*$drymode/100);
                $bottom = $top - 20;
                if($bottom < 0)
                {
                    $bottom = 0;
                }
            break;
            case 3:
                $top = intval($grade2humiditytop*$wetmode/100);
                $bottom = $top - 20;
                if($bottom < 0)
                {
                    $bottom = 0;
                }
            break;
            default:
                $top = $grade2humiditytop;
                $bottom = $top - 20;
                if($bottom < 0)
                {
                    $bottom = 0;
                }
            break;
        }
   
        return array(
            "step"=>"3",
            "run"=>"".$run."",
            "anion"=>trim($anion),
            "top"=>trim($top)."%",
            "bottom"=>trim($bottom)."%",
            "fastheartbeatmode"=>trim($fastheartbeatmode),
        );
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
        foreach($data as $key=>$value)
        {
            $part1 .= $sep.$key;
            $part2 .= $sep."'".mysql_escape_string($value)."'";
            $sep = ",";
        }
        $sql = "replace humidifier_work (".$part1.") values (".$part2.")";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 停止工作
     */
    public function stopWork($tpMachineid)
    {
        //这里不限制limit 1
        $sql = "replace humidifier_work (tp_machineid,run) values ($tpMachineid,0)";
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
        if(empty($tpAppidArray))
        {
            return 0; 
        }

        //获取这个电器的坐标
        $machineDetail = ServiceFactory::getService("Machine")->getDetail($tpMachineid);
        if(empty($machineDetail))
        {
            return 0; 
        }
        $machineLongitude = $machineDetail['longitude'];
        $machineLatitude = $machineDetail['latitude'];
        $machineLongitudeAndroid = $machineDetail['longitude_android'];
        $machineLatitudeAndroid = $machineDetail['latitude_android'];

        file_put_contents("/tmp/fei.log", date("Y-m-d H:i:s")." ".$tpMachineid." ".$machineLongitude.", ".$machineLatitude.", ".$machineLongitudeAndroid.", ".$machineLatitudeAndroid."\n", FILE_APPEND);
        if(empty($machineLongitude) && empty($machineLatitude) && empty($machineLongitudeAndroid) && empty($machineLatitudeAndroid))
        {
            return 0; 
        }

        $machineLongitude = floatval($machineLongitude);
        $machineLatitude = floatval($machineLatitude);
        $machineLongitudeAndroid = floatval($machineLongitudeAndroid);
        $machineLatitudeAndroid = floatval($machineLatitudeAndroid);


        //距离阀值

        $distance = 1000; //这里区分不了ios和android
        $autoStartDistance = 1350;
        $autoStopDistance = 1650;
        $rawDistance = 999999999;
        $startMinLen = $rawDistance; 
        $stopMinLen = $rawDistance;
        $autoStartTouchTpAppid = 0; //谁触发智能开的

        foreach($tpAppidArray as $tpAppid)
        {
            //要有活跃的app
            //TODO app需要有后台定时请求，否则会不工作的
            //这里不是的，这里没有return,而只是continue,他会继续会上一次的状态
            if(!ServiceFactory::getService("App")->isActive($tpAppid))
            {
                continue;  
            }
            $appDetail = ServiceFactory::getService("App")->getDetail($tpAppid);
            $appLongitude = $appDetail['longitude'];
            $appLatitude = $appDetail['latitude'];


            file_put_contents("/tmp/len.log", date("Y-m-d H:i:s")." tpMachineid=".$tpMachineid.", ios=(".$machineLongitude.", ".$machineLatitude.") android=(".$machineLongitudeAndroid.", ".$machineLatitudeAndroid."), tpAppid=".$tpAppid.", (".$appLongitude.", ".$appLatitude.")\n", FILE_APPEND);

            if(empty($appLongitude) &&  empty($appLatitude))
            {
                continue; 
            }

            $startStopDetail = ServiceFactory::getService("App")->getHumidifierStartStop($tpAppid, $tpMachineid);
            $startFlag = $startStopDetail['enableUserNearStart'];
            $stopFlag = $startStopDetail['enableUserFarStop'];

            //计算2点间的距离
            $appLongitude = floatval($appLongitude);
            $appLatitude = floatval($appLatitude);

            if(2 == $appDetail['phone_type'] && !empty($machineLongitudeAndroid) && !empty($machineLatitudeAndroid))
            {
                $p1 = ($machineLongitude - $appLongitude);
                $p2 = ($machineLatitude - $appLatitude);
            }
            else
            {
                $p1 = ($machineLongitudeAndroid - $appLongitude);
                $p2 = ($machineLatitudeAndroid - $appLatitude);
            }

            $len = sqrt($p1 * $p1 + $p2 * $p2) * 1000000;

            if($len > 50000)
            {
                continue; 
            }

            if($startFlag)
            {
                if($len < $startMinLen)
                {
                    $startMinLen = $len; 
                    $autoStartTouchTpAppid = $tpAppid; //记录是谁触发他智能开的
                    file_put_contents("/tmp/len.log", date("Y-m-d H:i:s")." tpMachineid=".$tpMachineid.", autoStartTouchTpAppid=".$tpAppid.", len=".$len."\n", FILE_APPEND);
                }
            }

            //只用最后操作的用户来实现智能开关, 0 == lastTpAppid就是手动模式
            if($stopFlag && ($lastTpAppid == $tpAppid || 0 == $lastTpAppid))
            {
                file_put_contents("/tmp/len.log", date("Y-m-d H:i:s")." ssssssssssssssssssssssssssss\n", FILE_APPEND);
                if($len < $stopMinLen)
                {
                    $stopMinLen = $len; 
                }
            }

            file_put_contents("/tmp/len.log", date("Y-m-d H:i:s")." tpMachineid=".$tpMachineid.", tpAppid=".$tpAppid.", len=".$len."\n", FILE_APPEND);

        } //end of foreach


        // 1000 =60米
        //判断是否要开
        if($startMinLen != $rawDistance)
        {
            //开的情况有一个满足就可以返回
            if($startMinLen <= $autoStartDistance)
            {
                //11111111-0000-1111-1111-111111111111 智能的appid
                $sql = "update humidifier_state set last_tp_appid='".$autoStartTouchTpAppid."', last_appid='11111111-0000-1111-1111-111111111111' where tp_machineid='".$tpMachineid."' limit 1";
                DaoFactory::getDao("Shard")->branchDb($tpMachineid);
                DaoFactory::getDao("Shard")->query($sql);

                $data = array(
                        "tp_machineid"=>$tpMachineid,
                        "run"=>1,
                        "starttime"=>0,
                        "grade"=>0,
                        "is_summer_mode"=>0,
                        );
                //FIXME 这里是否会导致不断插入数据库
                file_put_contents("/tmp/len.log", "tpMachineid=".$tpMachineid.", autoStartTouchTpAppid=".$tpAppid.", add start work\n", FILE_APPEND);
                saveWork($tpMachineid, "距离".$startMinLen." < ".$autoStartDistance.", 自动开");
                //$flag = ServiceFactory::getService("Humidifier")->addWork($tpMachineid, $data);
                return 1; 
            }
        }

        if(1)
        {
            //判断是否要关闭
            if($stopMinLen != $rawDistance)
            {
                //关这里，需要所有app都是远离才能返回
                if($stopMinLen > $autoStopDistance)
                {
                    $data = array(
                            "tp_machineid"=>$tpMachineid,
                            "run"=>0,
                            "starttime"=>0,
                            "grade"=>0,
                            "is_summer_mode"=>0,
                            );
                    //FIXME 这里是否会导致不断插入数据库
                    saveWork($tpMachineid, "距离".$stopMinLen." > ".$autoStopDistance.", 自动关");
                    //$flag = ServiceFactory::getService("Humidifier")->addWork($tpMachineid, $data);
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
        $sql = "update humidifier_state set last_start_time='".time()."' where tp_machineid='".$tpMachineid."' limit 1"; 
        //saveSql($sql);
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 复位最后启动的时间
     */
    public function resetLastStartTime($tpMachineid)
    {
        $sql = "update humidifier_state set last_start_time='0' where tp_machineid='".$tpMachineid."' limit 1"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 设置最后使用的挡数和是否开启负离子
     */
    public function setLastGrade($tpMachineid, $lastGrade=3, $anion=0, $lastAppid='')
    {
        if(empty($lastAppid))
        {
            $sql = "update humidifier_state set last_grade='".$lastGrade."', anion='".$anion."', last_anion='".$anion."' where tp_machineid='".$tpMachineid."' limit 1"; 
        }
        else
        {
            $sql = "update humidifier_state set last_appid='".$lastAppid."', last_grade='".$lastGrade."', anion='".$anion."', last_anion='".$anion."' where tp_machineid='".$tpMachineid."' limit 1"; 
        }
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 获取最后使用的挡数
     */
    public function getLastGrade($tpMachineid)
    {
        $sql = "select last_grade from humidifier_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data =  DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return 2;
        }
        else
        {
            return intval($data[0]['last_grade']);
        }
    }

    public function getLastAnion($tpMachineid)
    {
        $sql = "select last_anion from humidifier_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data =  DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return 2;
        }
        else
        {
            return intval($data[0]['last_anion']);
        }
    }

    /**
     * @desc 获取最后使用的挡数
     */
    public function getLastAppid($tpMachineid)
    {
        $sql = "select last_appid from humidifier_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data =  DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return "";
        }
        else
        {
            return trim($data[0]['last_appid']);
        }
    }

    public function getLastTpAppid($tpMachineid)
    {
        $sql = "select last_tp_appid from humidifier_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data =  DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return "";
        }
        else
        {
            return trim($data[0]['last_tp_appid']);
        }
    }

    public function updateCityId()
    {
        $sql = "select tp_machineid from humidifier_config where city_id_last_updatetime < '".(time() - 86400)."'"; 
        echo $sql."\n";
        DaoFactory::getDao("Shard")->branchDb(1);
        $data =  DaoFactory::getDao("Shard")->query($sql);
        foreach($data as $item)
        {
            $tpMachineid = $item['tp_machineid'];
            $sql = "select last_active_ip from machine_detail where tp_machineid='".$tpMachineid."'";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            $data1=  DaoFactory::getDao("Shard")->query($sql);
            if(empty($data1))
            {
                continue;
            }
            $lastActiveIp = $data1[0]['last_active_ip'];
            if(empty($lastActiveIp))
            {
                continue; 
            }

            $url = "http://api.map.baidu.com/location/ip?ak=vH9exg7e34GZms3W15roBomy&ip=".$lastActiveIp."&coor=bd09ll";
            echo $url."\n";
            $content = file_get_contents($url);
            if(empty($content))
            {
                continue;
            }
            $ipDetail = json_decode($content, true);
            $address = $ipDetail['address'];
            $arr = explode("|", $address);
            $cityName = $arr[2];
            $cityId = 0;
            
            $sql = "select city_id from city where city_name='".$cityName."' limit 1";
            $data2 = DaoFactory::getDao("Main")->query($sql);
            if(!empty($data2))
            {
                $cityId = $data2[0]['city_id'];
            }

            $sql = "update  humidifier_config set city_id='".$cityId."', city_name='".$cityName."', city_id_last_updatetime='".time()."' where tp_machineid='".$tpMachineid."'";
            echo $sql."\n";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            DaoFactory::getDao("Shard")->query($sql);

        }
    }

    //JP_add,查询智能开智能关是否同时关,同时关返回0，有一个为开返回1
    public function getGeofence($tpMachineid,$tpAppid)
    {
        $sql = "select enable_user_near_start,enable_user_far_stop from app_machine_config where tp_machineid='".$tpMachineid."' AND tp_appid ='".$tpAppid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data =  DaoFactory::getDao("Shard")->query($sql);
        if(empty($data)){
            return "";
        }
        if($data[0]['enable_user_near_start'] == 0 && $data[0]['enable_user_far_stop'] == 0)
        {
            return 0;
        }
        else
        {
            return 1;
        }
    }
}

