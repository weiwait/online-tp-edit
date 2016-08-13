<?php
namespace dal;
use base\Object;
class Memcached extends Object
{
    /**
     * 获取memcached操作对象
     * $obj->getResultCode() 获取响应码
     *
     * RES_SUCCESS = 0
     * RES_FAILURE = 1
     * RES_HOST_LOOKUP_FAILURE = 2
     * RES_UNKNOWN_READ_FAILURE = 7
     * RES_PROTOCOL_ERROR = 8
     * RES_CLIENT_ERROR = 9
     * RES_WRITE_FAILURE = 5
     * RES_DATA_EXISTS = 12
     * RES_NOTSTORED = 14
     * RES_NOTFOUND = 16
     * RES_PARTIAL_READ = 18
     * RES_SOME_ERRORS = 19
     * RES_NO_SERVERS = 20
     * RES_END = 21
     * RES_ERRNO = 26
     * RES_BUFFERED = 32
     * RES_TIMEOUT = 31
     * RES_BAD_KEY_PROVIDED = 33
     * RES_CONNECTION_SOCKET_CREATE_FAILURE = 11
     * RES_PAYLOAD_FAILURE = -1001
     */
    public function getConn()
    {
        if ($this->_conn == null)
        {
            // 长链接
            $this->_conn = new \Memcached($this->_group);
            if (!count($this->_conn->getServerList()))
            {
                    // 设置一致性Hash
                $this->_conn->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
                $this->_conn->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
                //$this->_conn->setOption(\Memcached::OPT_HASH, \Memcached::HASH_CRC);
                $this->_conn->setOption(\Memcached::OPT_COMPRESSION, $this->_compressed);
                // 设置timeout，必须设置为非阻塞连接方式，timeout才能生效
                $this->_conn->setOption(\Memcached::OPT_NO_BLOCK, true);
                // 设置connect超时间隔x毫秒，当Server挂掉时，业务只浪费x毫秒
                $this->_conn->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->_connecttimeout);
                $this->_conn->setOption(\Memcached::OPT_POLL_TIMEOUT, $this->_polltimeout);
                // 设置retry超时间隔，Server挂掉时，x秒才尝试1次，避免所有请求都浪费时间
                $this->_conn->setOption(\Memcached::OPT_RETRY_TIMEOUT, $this->_retrytimeout);
                $result = $this->_conn->addServers($this->_servers);
                if (!$result)
                {
                    $this->_conn = null;
                    throw new McException("can not connect to memcached server " . json_encode($this->_servers));
                }
            }
            // 判断当前的链接状态
            if (!$this->_conn->getStats())
            {
                // 链接memcache失效
                $this->log("getConn: get Stats fail, memcached server was down " . json_encode($this->_servers), "error");
            }
        }
        return $this->_conn;
    }
    /**
     * 初始化mc配置
     */
    public function init()
    {
        $config = \Yaf_Registry::get("config")->memcache;
        if (!isset($config[$this->_group]))
        {
            throw new McException("can not found group:" . $this->_group);
        }
        $host = $config[$this->_group]['host'];
        $hosts = explode(",", $host);
        foreach($hosts as $host)
        {
            $tmp   = explode(":", $host);
            $tmp[] = 0;
            $this->_servers[] = $tmp;
        }
        if (empty($this->_servers))
        {
            throw new McException("can not found servers for group:" . $this->_group);
        }
    }
    /**
     * 初始化
     */
    public function __construct($group)
    {
        $this->_group = $group;
        $this->init();
    }
    /**
     * 获取memcache操作对象
     *
     * @param string $group  - mc分组, 默认为nemo
     */
    public static function getInstance($group = 'nemo')
    {
        //
        if (!isset(self::$_obj[$group]))
        {
            self::$_obj[$group] = new self($group);
        }
        return self::$_obj[$group]->getConn();
    }
    private $_group;
    public static $_obj;
    private  $_conn;
    private $_compressed     = true;
    private $_connecttimeout = 100;
    private $_polltimeout    = 1000;
    private $_retrytimeout   = 1;
    private $_servers        = array();
}
