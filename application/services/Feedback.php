<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class Feedback extends Service
{
	public function __construct(){
	}

    public function reply($tpAppidi, $content)
    {
        $sql = "insert into feedback (createtime, content, ip, tp_appid, isdelete, type) values('".time()."', '".mysql_escape_string($content)."', '".$_SERVER['REMOTE_ADDR']."', '".$tpAppidi."', 0, '1')"; 
        DaoFactory::getDao("Main")->query($sql);
    }

    public function getAllCount($tpAppid)
    {
        if(empty($tpAppid))
        {
            $sql = "select count(distinct tp_appid) as num from feedback where 1=1 "; 
        }
        else if(is_array($tpAppid))
        {
            $sql = "select count(distinct tp_appid) as num from feedback where tp_appid in ('".implode("','", $tpAppid)."') "; 
        }
        else
        {
            $sql = "select count(distinct tp_appid) as num from feedback where tp_appid='".$tpAppid."' "; 
        }
        $data = DaoFactory::getDao("Main")->query($sql);
        return $data[0]['num'];
    }

    public function getCountByTpappid($tpAppid)
    {
        if(empty($tpAppid))
        {
            $sql = "select count(tp_appid) as num from feedback where 1=1 "; 
        }
        else if(is_array($tpAppid))
        {
            $sql = "select count(tp_appid) as num from feedback where tp_appid in ('".implode("','", $tpAppid)."') "; 
        }
        else
        {
            $sql = "select count(tp_appid) as num from feedback where tp_appid='".$tpAppid."' "; 
        }
        $data = DaoFactory::getDao("Main")->query($sql);
        return $data[0]['num'];
    }

    public function getList($tpAppid, $offset, $limit)
    {
        $ret = array();
        if(empty($tpAppid))
        {
            $sql = "select distinct tp_appid, max(id) as id from feedback where 1=1 group by tp_appid order by id desc limit ".$offset.", ".$limit."";
        }
        else if(is_array($tpAppid))
        {
            $sql = "select distinct tp_appid, max(id) as id from feedback where tp_appid in ('".implode("','",$tpAppid)."') order by id desc limit ".$offset.", ".$limit."";
        }
        else
        {
            $sql = "select distinct tp_appid, max(id) as id from feedback where tp_appid='".$tpAppid."' order by id desc limit ".$offset.", ".$limit."";
        }

        $data = DaoFactory::getDao("Main")->query($sql);

        $idArray = array();
        foreach($data as $item)
        {
            $idArray[] = $item['id'];
        }
       
        $sql = "select feedback.*, app.appid from feedback,app where feedback.id in ('".implode("','",$idArray)."') and feedback.tp_appid=app.id group by tp_appid order by id desc ";
        $data = DaoFactory::getDao("Main")->query($sql);

        return $data; 
    }

    public function getUnReadNum($tpAppid)
    {
        $sql = "select count(1) as num from feedback where tp_appid='".$tpAppid."' and isread='0'"; 
        $data = DaoFactory::getDao("Main")->query($sql);
        return $data[0]['num'];
    }

    public function updateIsRead($tpAppid)
    {
        $sql = "update feedback set isread='1' where tp_appid='".$tpAppid."'"; 
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function getDetail($tpAppid)
    {
        $sql = "select * from feedback where tp_appid='".$tpAppid."' order by id desc"; 
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function getDetailList($tpAppid, $offset, $limit)
    {
        $ret = array();
        $sql = "select * from feedback where tp_appid='".$tpAppid."' order by id desc limit ".$offset.", ".$limit.""; 
        $data = DaoFactory::getDao("Main")->query($sql);
        foreach($data as $item)
        {
            $ret[] = array(
                "createtime"=>$item['createtime'],
                "content"=>$item['content'],
                "type"=>$item['type'],
            );
        }
        return $ret;
    }
}

