<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class AppMsg extends Service
{
	public function __construct(){
	}

    public function getCount($tpAppid, $unreadFlag=false)
    {
        if($unreadFlag)
        {
            //未读
            $sql = "select count(1) as num from app_msg where tp_appid='".$tpAppid."' and isread='0' and isdelete='0'";
        }
        else
        {
            $sql = "select count(1) as num from app_msg where tp_appid='".$tpAppid."' and isdelete='0'";
        }

        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        return $data[0]['num']; 
    }

    public function getList($tpAppid, $offset, $limit, $unreadFlag=false)
    {
        if($unreadFlag)
        {
            $sql = "select id, machineid, content, isread, createtime from app_msg where tp_appid='".$tpAppid."' and isread='0' and isdelete='0' order by id desc limit ".$offset.", ".$limit.""; 
        }
        else
        {
            $sql = "select id, machineid, content, isread, createtime from app_msg where tp_appid='".$tpAppid."' and isdelete='0' order by id desc limit ".$offset.", ".$limit.""; 
        }
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function addMsg($tpAppid, $appid, $tpMachineid, $machineid, $content)
    {
        $sql = "insert into app_msg (tp_appid, appid, tp_machineid, machineid, content, createtime, isread, isdelete) values ('".$tpAppid."', '".$appid."', '".$tpMachineid."', '".$machineid."', '".mysql_escape_string($content)."', '".time()."', '0', '0')"; 
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function updateMsgStatus($tpAppid, $msgIdArray)
    {
        $sql = "update app_msg set isread='1' where tp_appid='".$tpAppid."' and id in ('".implode("','", $msgIdArray)."')";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }

    public function deleteMsg($tpAppid, $msgIdArray)
    {
        $sql = "update app_msg set isdelete='1' where tp_appid='".$tpAppid."' and id in ('".implode("','", $msgIdArray)."')";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        return DaoFactory::getDao("Shard")->query($sql);
    }
}

