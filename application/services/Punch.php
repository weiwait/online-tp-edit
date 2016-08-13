<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class Punch extends Service
{
	public function __construct(){
	}

    /**
     * @desc 获取预约的类型
     */
    public function add($tpAppid, $appid, $tpMachineid, $machineid, $range, $punchstatus, $phonenumber, $username, $punchtime, $apptype, $position, $is_accept, $operation)
    {
        
        $sql = "insert into attendance_punch(tp_appid, appid, tp_machineid, machineid, range, punchstatus, phonenumber, username, punchtime, apptype, position, is_accept, operation) values ('".$tpAppid."', '".$appid."', '".$tpMachineid."', '".$machineid."', '".$range."', '".$punchstatus."', '".$phonenumber."', '".$username."', '".time()."', '".$apptype."', '".$position."', '".$is_accept."', '".$operation."')"; 
        DaoFactory::getDao("Shard")->branchDb($tpMachineid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    
}

