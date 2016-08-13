<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class HumidifierOrder extends Service
{
	public function __construct(){
	}

    /**
     * @desc 获取预约的类型
     */
    private function getOrderType($week)
    {
        $type = 0;
        $len = strlen($week); 
        for($i=0; $i<$len; $i++)
        {
            $c = substr($week, $i, 1); 
            if("0" != $c)
            {
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
        while($i<20)
        {
            $orderid = date("YmdHis").rand(10, 99);
            $sql = "select orderid from humidifier_order where tp_machineid='".$tpMachineid."' and orderid='".$orderid."' limit 1";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            $data = DaoFactory::getDao("Shard")->query($sql);
            if(empty($data))
            {
                return $orderid; 
            }
            ++$i;
        }
        return 0;
    }

    /**
     * @desc 获取预约的类型
     */
    public function add($tpMachineid, $machineid, $tpAppid, $appid, $orderid, $heattime, $week, $action, $grade, $startRemind, $endRemind, $noWaterRemind, $anion)
    {
        $type = $this->getOrderType($week);
        $sql = "insert into humidifier_order(tp_appid, appid, createtime, tp_machineid, orderid, type, machineid, heattime, week, action, start_remind, end_remind, no_water_remind, grade, anion) values ('".$tpAppid."','".$appid."','".time()."', '".$tpMachineid."', '".$orderid."', '".$type."', '".$machineid."', '".$heattime."', '".$week."', '".$action."', '".$startRemind."', '".$endRemind."', '".$noWaterRemind."', '".$grade."', '".$anion."')"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 判断预约是否存在
     */
    public function isExist($tpMachineid, $orderid)
    {
        $sql = "select orderid from humidifier_order where tp_machineid='".$tpMachineid."' and orderid='".$orderid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return false; 
        }
        else
        {
            return true;
        }
    }

    /**
     * @desc 获取预约的详情
     */
    public function getDetail($tpMachineid, $orderid)
    {
        $sql = "select * from humidifier_order where tp_machineid='".$tpMachineid."' and orderid='".$orderid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return false; 
        }
        else
        {
            return array(
                "orderid"=>$data[0]['orderid'],
                "heattime"=>$data[0]['heattime'],
                "week"=>$data[0]['week'],
                "action"=>$data[0]['action'],
                "startremind"=>$data[0]['start_remind'],
                "endremind"=>$data[0]['end_remind'],
                "nowaterremind"=>$data[0]['no_water_remind'],
                "grade"=>$data[0]['grade'],
                "anion"=>$data[0]['anion'],
            );
        }
    }

    /**
     * @desc 删除预约
     */
    public function cancelOrder($tpMachineid, $tpAppid, $orderid)
    {
        $sql = "delete from humidifier_order where tp_machineid='".$tpMachineid."' and tp_appid='".$tpAppid."' and orderid='".$orderid."' limit 1"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    /**
     * @desc 获取预约的数量
     */
    public function getOrderNum($tpMachineid, $tpAppid)
    {
        if(!empty($tpAppid))
        {
            $sql = "select count(1) as num from humidifier_order where tp_machineid='".$tpMachineid."' and tp_appid='".$tpAppid."'";
        }
        else
        {
            $sql = "select count(1) as num from teapot_order where tp_machineid='".$tpMachineid."'";
        }
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
     * @desc 获取预约列表
     */
    public function getOrderList($tpMachineid, $tpAppid, $offset, $limit)
    {
        $ret = array();
        if(!empty($tpAppid))
        {
            $sql = "select * from humidifier_order where tp_machineid='".$tpMachineid."' and tp_appid='".$tpAppid."' order by createtime asc limit ".$offset.",".$limit."";
        }
        else
        {
            $sql = "select * from humidifier_order where tp_machineid='".$tpMachineid."' order by createtime asc limit ".$offset.",".$limit."";
        }

        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        foreach($data as $item)
        {
            $ret[] = array(
                "orderid"=>$item['orderid'],
                "heattime"=>$item['heattime'],
                "week"=>$item['week'],
                "action"=>$item['action'],
                "startremind"=>$item['start_remind'],
                "endremind"=>$item['end_remind'],
                "nowaterremind"=>$item['no_water_remind'],
                "grade"=>$item['grade'],
                "anion"=>$item['anion'],
            ); 
        }
        return $ret;
    }

    /**
     * @desc 定时检查crontab
     */
    public function checkByCrontab()
    {
        $wday = date("w", time());
        $sql = "select * from humidifier_order where isdelete='0'"; 
        echo $sql."\n";
        $tpMachineid = 1;
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        echo "total task num = ".count($data)."\n";
        foreach($data as $item)
        {
            $week = $item['week']; 
            $grade = $item['grade']; 
            $machineid = $item['machineid']; 
            $tpMachineid = $item['tp_machineid']; 
            if(7 != strlen($week))
            {
                continue; 
            }
            $action = $item['action']; 
            if(!in_array($action, array("run", "stop")))
            {
                continue; 
            }
            $heattime = $item['heattime']; 
            if(6 != strlen($heattime))
            {
                continue; 
            }

            echo "check week tpMachineid=".$tpMachineid.", machineid=".$machineid."\n";

            //这一天的 
            $hour = substr($heattime, 0, 2);
            $mins = substr($heattime, 2, 2);
            $second = substr($heattime, 4, 2);

            $hour = intval($hour);
            $mins = intval($mins);
            $second = intval($second);
            
            $year = date("Y");
            $month = date("m");
            $day = date("d");

            $unixTime = mktime($hour, $mins, $second, $month, $day, $year); //开始时间
            $now = time();

            //不重复的情况 2015/12/30增加
            if($week=="0000000"){
                if($now > $unixTime && $now - $unixTime < 15)
                {
                    //需要有动作 
                    if("run" == $action)
                    {
                        $grade = ServiceFactory::getService("Humidifier")->getLastGrade($tpMachineid);
                        $data = array(
                            "tp_machineid"=>$tpMachineid,
                            "run"=>1,
                            "starttime"=>time(),
                            "grade"=>$grade,
                            "is_summer_mode"=>0,
                        );
            
                        $msg =  date("Y-m-d H:i:s")." start ".$tpMachineid.", ".$machineid;
                        echo $msg."\n";
                        file_put_contents("/tmp/HumidifierOrder.log", $msg."\n", FILE_APPEND);
                        saveWork($tpMachineid, "预约开");
                        $flag = ServiceFactory::getService("Humidifier")->addWork($tpMachineid, $data);
                        if($flag)
                        {    
                            ServiceFactory::getService("Humidifier")->setLastGrade($tpMachineid, $grade, 0, "11111111-1111-1111-1111-000000000000");
                            //推送开始消息
                            //ServiceFactory::getService("PushMsg")->pushHumidifierStart($tpMachineid);
                            //Result::showOk("ok");
                        }    
                        else 
                        {    
                            //Result::showError("system error");    
                        }
                    }
                    else
                    {
                        $msg = date("Y-m-d H:i:s")." stop ".$tpMachineid.", ".$machineid;
                        echo $msg."\n";
                        file_put_contents("/tmp/HumidifierOrder.log", $msg."\n", FILE_APPEND);
                        saveWork($tpMachineid, "关");
                        $flag = ServiceFactory::getService("Humidifier")->stopWork($tpMachineid);
                    }
                }
                //只执行一次后删除
                $this->cancelOrder($tpMachineid,$item['tp_appid'],$item['orderid']);
                continue;
            }

            for($i=0; $i<7; ++$i)
            {
                $c = intval($week[$i]); 
                if($i != $wday)
                {
                    continue; 
                }
                if(1 == $c)
                {
                    
                    //$msg = "time diff = ".($now - $unixTime);
                    //file_put_contents("/tmp/HumidifierOrder.log", $msg."\n", FILE_APPEND);
                    if($now > $unixTime && $now - $unixTime < 15)
                    {
                        //需要有动作 
                        if("run" == $action)
                        {
                            $grade = ServiceFactory::getService("Humidifier")->getLastGrade($tpMachineid);
                            $data = array(
                                "tp_machineid"=>$tpMachineid,
                                "run"=>1,
                                "starttime"=>time(),
                                "grade"=>$grade,
                                "is_summer_mode"=>0,
                            );
                
                            $msg =  date("Y-m-d H:i:s")." start ".$tpMachineid.", ".$machineid;
                            echo $msg."\n";
                            file_put_contents("/tmp/HumidifierOrder.log", $msg."\n", FILE_APPEND);
                            saveWork($tpMachineid, "预约开");
                            $flag = ServiceFactory::getService("Humidifier")->addWork($tpMachineid, $data);
                            if($flag)
                            {    
                                ServiceFactory::getService("Humidifier")->setLastGrade($tpMachineid, $grade, 0, "11111111-1111-1111-1111-000000000000");
                                //推送开始消息
                                //ServiceFactory::getService("PushMsg")->pushHumidifierStart($tpMachineid);
                                //Result::showOk("ok");
                            }    
                            else 
                            {    
                                //Result::showError("system error");    
                            }
                        }
                        else
                        {
                            $msg = date("Y-m-d H:i:s")." stop ".$tpMachineid.", ".$machineid;
                            echo $msg."\n";
                            file_put_contents("/tmp/HumidifierOrder.log", $msg."\n", FILE_APPEND);
                            saveWork($tpMachineid, "关");
                            $flag = ServiceFactory::getService("Humidifier")->stopWork($tpMachineid);
                        }
                    }
                } // if(1 == $c)
            }//for

        }//foreach
        
        return 0; 
    }
}

