<?php
namespace services;
use base\Service;
use dal\Memcached;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class User extends Service
{
	public function __construct(){
	}

    public function check($username, $password)
    {
        $sql = "select * from user where username='".$username."' and password='".md5($password)."' limit 1"; 
        $data = DaoFactory::getDao("Main")->query($sql);
        if(empty($data))
        {
            return false;
        }
        else
        {
            return $data[0];
        }
    }

    public function updatePassword($id, $password)
    {
        $sql = "update user set password='".md5($password)."' where id='".$id."' limit 1"; 
        return DaoFactory::getDao("Main")->query($sql);
    }
}
