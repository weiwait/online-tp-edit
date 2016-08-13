<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class Teapot extends Service
{
	public function __construct(){
	}

    /**
     * @desc 获取使用记录
     */
    public function getActionLogNumForAdmin($tpMachineid)
    {
        $sql = "select count(1) as num from teapot_action_log where tp_machineid='".$tpMachineid."'"; 
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
        //fei 2015-03-23 显示全部
        //$sql = "select count(1) as num from teapot_action_log where tp_machineid='".$tpMachineid."' and tp_appid='".$tpAppid."'"; 
        $sql = "select count(1) as num from teapot_action_log where tp_machineid='".$tpMachineid."'"; 
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

    public function getActionLogList($tpMachineid, $tpAppid, $offset, $limit)
    {
        $ret = array();
        //fei 2015-03-23 显示全部
        //$sql = "select * from teapot_action_log where tp_machineid='".$tpMachineid."' and tp_appid='".$tpAppid."' order by id desc limit ".$offset.", ".$limit.""; 
        $sql = "select * from teapot_action_log where tp_machineid='".$tpMachineid."' order by id desc limit ".$offset.", ".$limit.""; 
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
                    "starttime"=>$item['starttime'], 
                    "endtime"=>$item['endtime'], 
                    "level"=>$item['level'], 
                    "temp"=>$item['temp'], 
                    "boil"=>$item['boil'], 
                    "purify"=>$item['purify'], 
                    "keepwarm"=>$item['keepwarm'], 
                    "createtime"=>$item['createtime'], 
                    "energy"=>$item['energy'], 
                ); 
            }
        }
        return $ret;
    }

    //这个接口是不区分使用者的
    public function getActionLogListForAdmin($tpMachineid, $offset, $limit)
    {
        $ret = array();
        $sql = "select * from teapot_action_log where tp_machineid='".$tpMachineid."' order by id desc limit ".$offset.", ".$limit.""; 
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
                    "starttime"=>$item['starttime'], 
                    "endtime"=>$item['endtime'], 
                    "level"=>$item['level'], 
                    "temp"=>$item['temp'], 
                    "boil"=>$item['boil'], 
                    "purify"=>$item['purify'], 
                    "keepwarm"=>$item['keepwarm'], 
                    "createtime"=>$item['createtime'], 
                    "energy"=>$item['energy'], 
                    "ip"=>$item['ip'], 
                ); 
            }
        }
        return $ret;
    }

    public function actionLog($tpMachineid, $machineid, $tpAppid, $appid, $operation, $starttime, $level, $temp, $boil, $purify, $keepwarm, $endtime, $energy)
    {
        $sql = "insert into teapot_action_log (ip, tp_machineid, machineid, tp_appid, appid, operation, starttime, endtime, level, temp, boil, purify, keepwarm, createtime, energy) values('".$_SERVER['REMOTE_ADDR']."','".$tpMachineid."', '".$machineid."', '".$tpAppid."', '".$appid."', '".$operation."', '".$starttime."', '".$endtime."', '".$level."', '".$temp."', '".$boil."', '".$purify."', '".$keepwarm."', '".time()."', '".$energy."')"; 

        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function updateStateToHeating($tpMachineid, $machineid)
    {
        $sql = "select tp_machineid from teapot_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            $sql = "insert into teapot_state (tp_machineid, machineid, level, temp, hub, state, last_update_time) values('".$tpMachineid."', '".$machineid."', '0L', '0C', '1', '1', '".time()."')"; 
        }
        else
        {
            $sql = "update teapot_state set state='1', last_update_time='".time()."' where tp_machineid='".$tpMachineid."' limit 1"; 

        }
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function updateState($tpMachineid, $machineid, $level, $temp, $hub, $state)
    {
        $state = intval($state);
        if(1 == $state)
        {
            $sql = "replace into teapot_state (tp_machineid, machineid, level, temp, hub, state, last_update_time, lasttime) values('".$tpMachineid."', '".$machineid."', '".$level."', '".$temp."', '".$hub."', '".$state."', '".time()."', '".date("YmdHis")."')"; 
        }
        else
        {
            $sql = "select tp_machineid from teapot_state where tp_machineid='".$tpMachineid."' limit 1";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            $data = DaoFactory::getDao("Shard")->query($sql);
            if(empty($data))
            {
                $sql = "insert into teapot_state (tp_machineid, machineid, level, temp, hub, state, last_update_time) values('".$tpMachineid."', '".$machineid."', '".$level."', '".$temp."', '".$hub."', '".$state."', '".time()."')"; 
            }
            else
            {
                $sql = "update teapot_state set level='".$level."', temp='".$temp."', hub='".$hub."', state='".$state."', last_update_time='".time()."' where tp_machineid='".$tpMachineid."' limit 1"; 

            }
        }
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function updateLastHeatTime($tpMachineid)
    {
        $sql = "update teapot_state set lasttime='".date("YmdHis")."' where tp_machineid='".$tpMachineid."' limit 1"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function getState($tpMachineid)
    {
        $sql = "select * from teapot_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return false;
        }
        else
        {
            if(empty($data[0]['lasttime']))
            {
                $data[0]['lasttime'] = 0;
            }

            return array(
                "level"=>$data[0]['level'],
                "temp"=>$data[0]['temp'],
                "hub"=>$data[0]['hub'],
                "state"=>$data[0]['state'],
                "lasttime"=>$data[0]['lasttime'],
            );
        }
    }

    private function getBitSetPos( $int ) 
    { 
        $str = strval ( decbin ( $int ) ) ; 
        $str = strrev ( $str ) ; 
        $arr = array ( ) ; 
        for ( $i = 0; $i < strlen($str); $i++ ) { 
            if ( $str{$i}) { 
                $arr[] = $i + 1; 
            } 
        } 
        return $arr; 
    } 

    public function canStartToHeat($tpMachineid)
    {
        $stateDetail = $this->getState($tpMachineid);  
        if(false === $stateDetail)
        {
            return false; 
        }
        else
        {
            $state = $stateDetail['state'];
            //fei 2014-10-12 热水壶只有在空闲的状态下才能立即加热
            if("0" == $state || "4" == $state)
            {
                return true; 
            }
            else
            {
                return false;
            }
        }
        return false;
    }


    public function getStateForAdmin($tpMachineid)
    {
        $sql = "select * from teapot_state where tp_machineid='".$tpMachineid."' limit 1";
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
                "temp"=>$data[0]['temp'],
                "hub"=>$data[0]['hub'],
                "state"=>$data[0]['state'],
                "last_update_time"=>$data[0]['last_update_time'],
            );
        }
    }

    public function getStateExt($tpMachineid)
    {
        $ret = array();
        $sql = "select * from teapot_state where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return false;
        }
        else
        {
           $ret = array(
                "level"=>$data[0]['level'],
                "temp"=>$data[0]['temp'],
                "hub"=>$data[0]['hub'],
                "state"=>$data[0]['state'],
            );
           $ret['order'] = ServiceFactory::getService("TeapotOrder")->getOrderList($tpMachineid, "",0, 999); 
           return $ret;
        }
    }

    public function getTeapotUseStat($tpMachineid)
    {
        $dayArray = array();
        $tempArray = array();
        $totalLevel = 0;
        $totalEnergy = 0;
        $maxTemp = "";
        $maxTempNum = 0;
        $useNum = 0;
        $sql = "select * from teapot_action_log where tp_machineid='".$tpMachineid."'"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        foreach($data as $item)
        {
            $useNum++;
            $createtime = $item['createtime']; 
            $temp = $item['temp']; 
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

            $level = $item['level']; 
            $level = strtolower($level);
            $level = str_replace("l", "", $level);
            $level = floatval($level);
            $totalLevel += $level; 

            $energy = $item['energy']; 
            $energy = strtolower($energy);
            $energy = str_replace("w", "", $energy);
            $energy = floatval($energy);
            $totalEnergy += $energy; 

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
            "totalEnergy"=>$totalEnergy,
            "maxTemp"=>$maxTemp,
            "maxTempNum"=>$maxTempNum,
        );
    }

    public function updateLastTpappid($tpMachineid, $tpAppid, $orderid)
    {
        if($tpAppid > 0)
        {
            $cooltime = time();
            $sql = "update machine_detail set last_tp_appid='".$tpAppid."', last_orderid='".$orderid."', cooltime='".$cooltime."' where tp_machineid='".$tpMachineid."' limit 1"; 
        }
        else
        {
            $cooltime = time() - 30;
            $sql = "update machine_detail set last_tp_appid='0', last_orderid='' where tp_machineid='".$tpMachineid."' and cooltime < ".$cooltime." limit 1"; 
        }

        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 获取最后操作的tpAppid
     */
    public function getLastTpAppid($tpMachineid)
    {
        $sql = "select last_tp_appid from machine_detail where tp_machineid='".$tpMachineid."'"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);

        if(empty($data))
        {
            return false; 
        }
        else
        {
            $data = $data[0];
            return $data['last_tp_appid'];
        }
    }

    /**
     * @desc 获取最后操作的orderid
     */
    public function getLastOrderid($tpMachineid)
    {
        $sql = "select last_orderid from machine_detail where tp_machineid='".$tpMachineid."'"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);

        if(empty($data))
        {
            return false; 
        }
        else
        {
            $data = $data[0];
            return $data['last_orderid'];
        }
    }

    //记录实时运行信息
    public function runtime($tpMachineid, $tpAppid, $orderid, $state)
    {
        $sql = "insert into teapot_runtime_feedback(tp_machineid, tp_appid, orderid, state, ctime) values('".$tpMachineid."', '".$tpAppid."', '".$orderid."', '".$state."', '".time()."')"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

}

