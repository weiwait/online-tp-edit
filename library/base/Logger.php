<?php
namespace base;
class Logger
{
    /**
     * 记录警告日志
     * @param $category   日志分类
     * @param $msg        日志内容
     *
     */
    public static function warn($category, $msg)
    {
        self::writeLog($category, self::WARN, $msg);
    }
    /**
     * 记录错误日志
     * @param $category   日志分类
     * @param $msg        日志内容
     *
     */
    public static function error($category, $msg)
    {
        self::writeLog($category, self::ERROR, $msg);
    }
    /**
     * 记录info日志
     * @param $category   日志分类
     * @param $msg        日志内容
     *
     */
    public static function info($category, $msg)
    {
        self::writeLog($category, self::INFO, $msg);
    }
    /**
     * 记录debug日志
     * @param $category   日志分类
     * @param $msg        日志内容
     *
     */
    public static function debug($category, $msg)
    {
        self::writeLog($category, self::DEBUG, $msg);
    }
    
    /**
     * 记录系统日志
     *
     * @param $category  日志分类
     * @param $level     日志等级
     * @param $msg       日志内容
     *
     */
    public static function writeLog($category, $level, $msg)
    {
        /*
        if (empty(self::$_conf))
        {
            throw new \Yaf_Exception_StartupError("Logger::writeLog empty config, plese init Logger in LoggerPluin");
        }
        if (!in_array($level, self::$_supportLogLevel))
        {
            // 不在配置以内的不记录
            return false;
        }
        if (self::$_conf['sys']['local'] == '1')
        {
            // 将日志写到本地
            $msg = date('Y-m-d H:i:s ') . " " . $category . ": " . $msg . "\n";
            file_put_contents(self::$_conf['sys']['local_path'] . '/' . strtolower($level) . '_' . date('Y-m-d').".log", $msg, FILE_APPEND);
        }
        if (self::$_conf['sys']['remote'] == '1')
        {
             // 当前日志的编号
            $levelNum = self::$level_mapper[$level];
            // 将日志写到远程
            openlog($category, LOG_PID | LOG_ODELAY,self::$_conf['sys']['facility']);
            syslog($levelNum, $msg);
            closelog();
        }
        */
        return true;
    }   
    /**
     * 记录业务日志
     */
    public static function writeAppLog($log,$type = '')
    {
        /*
        if (empty(self::$_conf))
        {
            throw new \Yaf_Exception_StartupError("Logger::writeAppLog empty config, plese init Logger in LoggerPluin");
        }
        self::_flushAppLog($log,$type);
        */
    }
    /**
     * 将日志传送到日志服务器
     */
    private static function _flushAppLog($msg,$type='')
    {
        /*
        if (self::$_conf['app']['local'] == '1')
        {
            // 将日志写到本地
            file_put_contents(self::$_conf['app']['local_path'] . '/' . $type . '_' . date('Y-m-d-H') . ".log", $msg . "\n", FILE_APPEND);
        }
        if (self::$_conf['app']['remote'] == '1')
        {
            // 将日志写到远程
            openlog(
                self::$_conf['app']['ident'], 
                LOG_PID | LOG_ODELAY,
                self::$_conf['app']['facility']
            );
            syslog(self::$_conf['app']['level'], $msg);
            closelog();
        }
        return true;
        */
    }
    /**
     * 传入配置
     */
    public static function init($conf)
    {
        /*
        self::$_conf = $conf;
        if (self::$_supportLogLevel === array())
        {
            self::$_supportLogLevel = explode(',', self::$_conf['sys']['level']) ;
        }
        */
    }

    private static $_conf           = array();
    private static $level_mapper    = array(
        self::DEBUG   => LOG_DEBUG,
        self::WARN    => LOG_WARNING,
        self::INFO    => LOG_INFO,
        self::ERROR   => LOG_ERR,
    );

    private static $_startTime;
    private static $_supportLogLevel = array();
    const DEBUG = "debug";
    const INFO  = "info";
    const WARN  = "warn";
    const ERROR = "error";
    const EVENT  = "event";
}
