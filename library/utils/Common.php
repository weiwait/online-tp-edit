<?php
namespace utils;
use base\Config;
use base\ServiceFactory;
class Common {
    public static function testItem($name, $key)
    {
        if(empty($key))
        {
            $key = $name;
        }
        return "<tr style='background:#ffffff;'><td style='width:60px;'>".$name."</td><td><input type='text' name='".$key."' style='width:500px' value='".$_REQUEST[$key]."'></td></tr>"; 
    }

    public static function getRequirePath(){
        return \Yaf_Registry::get('resource')->getRequirePath();
    }

    /**
     * 发送Email,支持群发
     */
    public static function sendEmail($to, $subject, $content, $from='') {
        return ServiceFactory::getService('Mailer')->sendEmail($to, $subject, $content, $from);
    }  
    /**
     * 发送Email,支持群发
     */
    public static function sendEmailAll($uid, $subject, $content,$arrEmail = array()) {
        return ServiceFactory::getService ( 'User' )->sendEmailAll($uid,$subject,$content,$arrEmail);
    }  
}
