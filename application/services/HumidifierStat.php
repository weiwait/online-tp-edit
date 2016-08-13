<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class HumidifierStat extends Service
{
	public function __construct(){
	}

    public function updateStat($tpMachineid)
    {
        //最近30天
        $dayArray = array();
        $humidityArray = array();
        $day = 30;
        $realUseDay = 0;
        $endtime = time();
        $starttime = $endtime - $day * 86400;
        $sql = "select createtime, starttime, endtime, start_level, end_level, middle_humidity from humidifier_action_log where tp_machineid='".$tpMachineid."' and createtime >= '".$starttime."' and createtime < ".$endtime.""; 

        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        $dailySecond = 0;
        $secondTotal = 0;
        $dailyLevel = 0;
        $levelTotal = 0;
        $maxHumidity = 0;
        $maxHumidityNum = 0;
        foreach($data as $item)
        {
            $createtime = $item['createtime'];
            $humidity = $item['middle_humidity'];
            $startlevel = $item['start_level'];
            $endlevel = $item['end_level'];
            $starttime = $item['starttime'];
            $endtime = $item['endtime'];

            $startlevel = strtolower($startlevel);
            $startlevel = str_replace("l", "", $startlevel);
            $endlevel = strtolower($endlevel);
            $endlevel = str_replace("l", "", $endlevel);

            $startsecond = $this->toSecond($starttime);
            $endsecond = $this->toSecond($endtime);
            $secondTotal += $endsecond - $startsecond;

            $levelTotal += abs(floatval($endlevel) - floatval($startlevel));


            $d = date("d", $createtime);
            if(!in_array($d, $dayArray))
            {
                $dayArray[] = $d;
            }
            if(isset($humidityArray[$humidity]))
            {
                $humidityArray[$humidity]++; 
                if($humidityArray[$humidity] > $maxHumidityNum)
                {
                    $maxHumidityNum = $humidityArray[$humidity];
                    $maxHumidity = $humidity;
                }
            }
            else
            {
                $humidityArray[$humidity] = 1; 
            }
        }
        if($secondTotal > 0)
        {
            $realUseDay = count($dayArray);
            //每天使用次数
            $dailySecond = ceil($secondTotal/$realUseDay);
            $dailyLevel = ceil($levelTotal/$realUseDay);

            $sql = "replace into humidifier_stat set daily_second='".$dailySecond."', daily_level='".$dailyLevel."', daily_humidity='".$maxHumidity."', tp_machineid='".$tpMachineid."'";
            DaoFactory::getDao("Shard")->branchDb($tpMachineid);
            DaoFactory::getDao("Shard")->query($sql);
        }
        return true;
    }

    private function toSecond($time)
    {
        $h = substr($time, 0, 2);
        $m = substr($time, 2, 2);
        $s = substr($time, 4, 2);

        return intval($h) * 3600 + intval($m) * 60 + intval($s);
    }

    public function getStat($tpMachineid)
    {
        $sql = "select daily_second as second, daily_level as level, daily_humidity as humidity from humidifier_stat where tp_machineid='".$tpMachineid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data))
        {
            return array(
                "second"=>"0",
                "level"=>"0",
                "humidity"=>"0",
            );
        }
        else
        {
            return array(
                "second"=>$data[0]['second'],
                "level"=>$data[0]['level'],
                "humidity"=>$data[0]['humidity'],
            );
        }
    }
}

