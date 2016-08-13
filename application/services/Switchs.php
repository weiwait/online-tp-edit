<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class Switchs extends Service
{
	public function __construct(){
		
	}
	
	public function bindSwitch($tpSwitchid, $tpLightid)
    {
		$sql = "delete from bind_switch where tp_switchid='".$tpSwitchid."'";
        $ret1 = DaoFactory::getDao("Main")->query($sql);
		
		foreach($tpLightid as $key=>$value){
			$sql = "insert into bind_switch (tp_switchid, tp_lightid) values('".$tpSwitchid."', '".$value."')";
        	$ret2 = DaoFactory::getDao("Main")->query($sql);
		}
		
		return $ret2;
    }
	
	public function getLightNum($tpAppid, $tpSwitchid, $ip)
    {
		//APP绑定的灯
		$sql = "select tp_machineid,machineid from bind a ";
		$sql .= "left join machine b on b.id=a.tp_machineid ";
		$sql .= "where tp_appid='".$tpAppid."' and (machineid like '03%' or machineid like '05%') ";
		$data1 = DaoFactory::getDao("Main")->query($sql);//print_r($data1);
		
		foreach($data1 as $key=>$value){
			$tp_machineid[] = $value['tp_machineid'];
		}
		$tp_machineids = implode(',', $tp_machineid);
		
		//APP同一路由器下绑定的灯
		$sql = "select tp_machineid from machine_detail where tp_machineid in (".$tp_machineids.") and last_active_ip='".$ip."'";
		DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data3 = DaoFactory::getDao("Shard")->query($sql);//print_r($data3);
		foreach($data3 as $key=>$value){
			$lightids1[] = $value['tp_machineid'];
		}
		
		//开关绑定的灯
        $sql = "select tp_lightid from bind_switch where tp_switchid='".$tpSwitchid."'";
		$data2 = DaoFactory::getDao("Main")->query($sql);//print_r($data2);
		foreach($data2 as $key=>$value){
			$lightids[] = $value['tp_lightid'];
		}//print_r($lightids);
		
		$data = array();$total = 0;
		foreach($data1 as $key=>$value){
			if(in_array($value['tp_machineid'],$lightids1) ){
				$total++;
				if(in_array($value['tp_machineid'],$lightids) ){
					$data[] = array(
						'machineid'=>$value['machineid'],
						'status'=>1,
					);
				}else{
					$data[] = array(
						'machineid'=>$value['machineid'],
						'status'=>0,
					);
				}
			}
		}//print_r($data);
		$res = array(
			'total'=>$total,
			'data'=>$data
		);
        
        return $res;
    }
	
	/**
     * @desc 获取开关同一路由器下所有的灯
     */
    public function getLightList($tpAppid, $tpSwitchid, $ip, $offset, $limit)
    {
		//APP绑定的灯
		$sql = "select tp_machineid,machineid from bind a ";
		$sql .= "left join machine b on b.id=a.tp_machineid ";
		$sql .= "where tp_appid='".$tpAppid."' and (machineid like '03%' or machineid like '05%') ";
		$sql .= "limit ".$offset.",".$limit."";
		$data1 = DaoFactory::getDao("Main")->query($sql);//print_r($data1);
		
		foreach($data1 as $key=>$value){
			$tp_machineid[] = $value['tp_machineid'];
		}
		$tp_machineids = implode(',', $tp_machineid);
		
		//APP绑定的同一路由器下的在线的灯
		$sql = "select tp_machineid from machine_detail where tp_machineid in (".$tp_machineids.") and last_active_ip='".$ip."' and unix_timestamp(now())-last_active_time<60";
		DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data3 = DaoFactory::getDao("Shard")->query($sql);//print_r($data3);
		
		foreach($data3 as $key=>$value){
			$lightids1[] = $value['tp_machineid'];
		}
		
		//开关绑定的灯
        $sql = "select tp_lightid from bind_switch where tp_switchid='".$tpSwitchid."'";
		$data2 = DaoFactory::getDao("Main")->query($sql);//print_r($data2);
		
		foreach($data2 as $key=>$value){
			$lightids[] = $value['tp_lightid'];
		}//print_r($lightids);
		
		$data = array();$total = 0;
		foreach($data1 as $key=>$value){
			if(in_array($value['tp_machineid'],$lightids1) ){
				$total++;
				if(in_array($value['tp_machineid'],$lightids) ){
					$data[] = array(
						'machineid'=>$value['machineid'],
						'status'=>1,
					);
				}else{
					$data[] = array(
						'machineid'=>$value['machineid'],
						'status'=>0,
					);
				}
			}
		}//print_r($data);
		$res = array(
			'total'=>$total,
			'data'=>$data
		);
        
        return $res;
    }
}

