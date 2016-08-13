<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class TeapotStat extends Service
{
	public function __construct(){
	}

    /**
     * @desc 获取上个月的开始时间
     */
    public function getPreMonthStartTime()
    {
        $month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $m = intval(date("m"))%12;
        $d = intval(date("d"));
        $now = time() - ($d + $month[$m]) * 86400;
        return $now; 
    }


    /**
     * @desc 获取上个月的结束时间
     */
    public function getPreMonthEndTime()
    {
        $d = intval(date("d"));
        $now = time() - $d * 86400;
        return $now; 
    }

    /**
     * @desc 更新统计
     */
    public function updateStat($tpMachineid)
    {
        //最近30天
        //fei 2015-01-06 要改成上个月(自然月)
        $dayArray = array();
        $tempArray = array();
        $day = 30;
        $realUseDay = 0;
        //$endtime = time();
        //$starttime = $endtime - $day * 86400;

        $endtime = $this->getPreMonthEndTime();
        $starttime = $this->getPreMonthStartTime();

        $sql = "select createtime, level, temp, energy from teapot_action_log where tp_machineid='".$tpMachineid."' and createtime >= '".$starttime."' and createtime < ".$endtime.""; 

        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        $useNumTotal = count($data);
        $dailyUseNum = 0;
        $dailyLevel = 0;
        $dailyEnergy = 0;
        $levelTotal = 0;
        $energyTotal = 0;
        $maxTemp = 0;
        $maxTempNum = 0;
        foreach($data as $item)
        {
            $createtime = $item['createtime'];
            $temp = $item['temp'];
            $level = $item['level'];
            $energy = $item['energy'];
            $energy = strtoupper($energy);
            $energy = str_replace("KW", "", $energy);
            $energy = str_replace("W", "", $energy);

            $temp = strtolower($temp);
            $level = strtolower($level);
            $temp = str_replace("c", "", $temp);
            $temp = str_replace("f", "", $temp);
            $level = str_replace("l", "", $level);

            $temp = intval($temp);
            $level = intval($level);
            $energy = floatval($energy);

            $d = date("d", $createtime);
            if(!in_array($d, $dayArray))
            {
                $dayArray[] = $d;
            }
            $levelTotal += $level;
            $energyTotal += $energy;
            if(isset($tempArray[$temp]))
            {
                $tempArray[$temp]++; 
                if($tempArray[$temp] > $maxTempNum)
                {
                    $maxTempNum = $tempArray[$temp];
                    $maxTemp = $temp;
                }
            }
            else
            {
                $tempArray[$temp] = 1; 
            }
        }
        if($useNumTotal > 0)
        {
            $realUseDay = count($dayArray);
            //每天使用次数
            $dailyUseNum = ceil($useNumTotal/$realUseDay);
            $dailyLevel = ceil($levelTotal/$realUseDay);
            $dailyEnergy = number_format($energyTotal/$realUseDay, 3, ".", "")."w";

            //fei 2015-01-06 将平均用电改成总用电
            $dailyEnergy = number_format($energyTotal, 3, ".", "")."w.h";

            $sql = "replace into  teapot_stat set daily_use_num='".$dailyUseNum."', daily_level='".$dailyLevel."', daily_temp='".$maxTemp."', daily_energy='".$dailyEnergy."', tp_machineid='".$tpMachineid."'";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            DaoFactory::getDao("Shard")->query($sql);
        }
        return true;
    }

    /**
     * @desc 获取电器的统计
     */
    public function getStat($tpMachineid)
    {
        $sql = "select daily_use_num as num, daily_level as level, daily_temp as temp, daily_energy  as energy from teapot_stat where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return array(
                "num"=>"0",
                "level"=>"0",
                "temp"=>"0",
                "energy"=>"0",
            );
        }
        else
        {
            return array(
                "num"=>$data[0]['num'],
                "level"=>$data[0]['level'],
                "temp"=>$data[0]['temp'],
                "energy"=>$data[0]['energy'],
            );
        }
    }
}

