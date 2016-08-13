<?php
namespace base;
use dal\WapkaMc;

class Session extends Object
{
    /**
     * 初始化
     */
    public function __construct()
    {
        //pass
    }
    /**
     * 获取单实例
     */
    public static function getInstance()
    {
        if (self::$_obj === null)
        {
            self::$_obj = new self();
        }
        return self::$_obj;
    }
    
    /**
     * 通过 memcached 的特性, 实现session的共享
     */
    public function init()
    {
        
    }
    /**
     * 获取wapka的session id
     */
    public static function getWapKaSid()
    {
        $sid = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
        $sid = isset($_GET['PHPSESSID']) ? $_GET['PHPSESSID'] : $sid;
        $sid = isset($_POST['PHPSESSID']) ? $_POST['PHPSESSID'] : $sid;
        return $sid;
    }
    /**
     * 直接读取Wapka的SESSION数据
     */
    public static function getWapkaSessDirectly()
    {
        $sid = self::getWapKaSid();
        if (!empty($sid))
        {
            $content = WapkaMc::conn("wapka_sess", 'get', $sid);
            return self::unserialize($content);
        }
        return null;
    }
    /**
     * 解析wapka 的session数据
     * @param unknown_type $session_data
     * @throws Exception
     */
    public static function unserialize($session_data) {
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php":
                return self::_unserialize_php($session_data);
                break;
            case "php_binary":
                return self::_unserialize_phpbinary($session_data);
                break;
            default:
                throw new \Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
        }
    }
    private static function _unserialize_phpbinary($session_data) {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            $num     = ord($session_data[$offset]);
            $offset += 1;
            $varname = substr($session_data, $offset, $num);
            $offset += $num;
            $data    = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
    private static function _unserialize_php($session_data) {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new \Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
    /**
     * 清除session数据
     */
    public function flush()
    {
        // 清除数据
    }
    private $_conf;
    private static $_obj;
}
