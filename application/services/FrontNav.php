<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class FrontNav extends Service
{
	public function __construct(){
	}

    public function getAll($app_ver = 1.01)
    {
        $data = DaoFactory::getDao("FrontNav")->select("isdelete='0' and min_ver <= " . $app_ver, "*", "", "rank desc");
        return $data;
    }

    public function getById($id)
    {
        $data = DaoFactory::getDao("FrontNav")->get_one("id='".$id."'"); 
        return $data;
    }
   
    public function getByCode($code)
    {
        $data = DaoFactory::getDao("FrontNav")->get_one("code='".$code."'"); 
        return $data;
    }

    public function getLastUpdateTime($app_ver)
    {
        //注意这个不能加isdelete条件的
        $lastupdatetime = 0;
        $sql = "select max(lastupdatetime) as lastupdatetime from nav_info where min_ver <= '".$app_ver."'"; 
        $data = DaoFactory::getDao("FrontNav")->query($sql);
        $lastupdatetime = intval($data[0]['lastupdatetime']);
        return $lastupdatetime;
        
    }

}

