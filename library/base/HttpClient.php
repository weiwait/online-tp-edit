<?php
declare(encoding = 'UTF-8');
namespace base;
class HttpClient
{

    /**
     * 获取http请求实例
     * @param boolean $isEnableStatis - 是否需要启用统计
     * @return HttpClient
     */
    public static function getInstance($isEnableStatis = true)
    {
        return new self("gzip", "", $isEnableStatis);
    }
    /**
     * 发起一个post请求
     * 
     * @param $url    - 请求的url
     * @param $data   - post过去的数据 两种格式 key=value&key=value 或者 数组 array('key' => 'value')
     * @example
     * \ucf\libraries\http\HttpClient::getInstance()->post('http://www.baidu.com', array('key' => 'value'));
     *
     * @return String http body
     */
    public function post($url, $data = array())
    {
        $this->url           = $url;
        $this->requestMehtod = "post";
        $this->requestParam  = $data;
        $res = $this->_parseRes();
        return $res;
    }
    /**
     * 发起一个get请求
     * @param String  $url  - get请求的url
     * @example
     * \ucf\libraries\http\HttpClient::getInstance()->get('http://www.baidu.com')
     *
     * @return string http body (不带header)
     */
    public function get($url)
    {
        if (empty($url))
        {
            throw new \Exception("HttpClient get Url can not empty");
        }
        $this->requestMehtod = "get";
        $this->url           = $url;
        $res = $this->_parseRes();
        return $res;
    }
    /**
     * 为每次请求手工设置超时时间
     *
     * @param $timeout  - 超时时间, 单位是秒
     * @return void
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
    }
    /**
     * set shutdown times for every request
     * @param type $num
     */
    public function setShutdownNum($num)
    {
        $this->_shutdownNum = $num;
        return true;
    }
    /**
     * 获取指定的header信息
     * @param $key 获取的Header的Key， 如：HTTP_USER_AGENT
     * @return String
     */
    public function getHeaders($key = '')
    {
        return empty($key) ? $this->responseHeader : (isset($this->responseHeader[$key]) ? $this->responseHeader[$key] : '');
    }

    /**
     * 手工设置请求头
     * @param $key    自定义Header的key
     * @param $value  自定义Header的值
     * @return boolean 是否设置成功
     */
    public function setHeaders($key, $value)
    {
        $this->headers[] = $key . ": " . $value;
        return true;
    }
    /**
     * 设置是否重试试
     *
     * @param boolean  - 是否重试, 如果是false，则不重试
     * @return void
     */
    public function setIsRetry($isRetry)
    {
        if (!$isRetry)
        {
            $this->_retryNum = 1;
        }
        return true;
    }

    /**
     * 设置参数是否需要http_build_query
     * @param boolean  - 是否http_build_query, 如果是false，则不需要
     * @return void
     */
    public function setBuildQuery($isBuildQuery)
    {
        if ($isBuildQuery)
        {
            $this->buildQuery = 1;
        }
        else
        {
            $this->buildQuery = 0;
        }
        return true;
    }
    /**
     * 解析结果
     */
    private function _parseRes()
    {
        //TODO 这里有问题，重试的时候没有断开链接, 待修复
        $return = $this->_exec();
        $this->proecess = '';
        return $return;
    }
    /**
     * 执行请求
     */
    private function _exec()
    {
        $res = '';
        // 支持每次请求手工指定超时时间/故障判断的失败次数
        if (!$this->_checkIsAvaliable())
        {
            // 记录失败日志
            $this->_log('exec:notAvaliable`' . $this->url . "`" . json_encode($this->requestParam), 'info');
            return false;
        }
        $num = 1;
        while (!self::$requestStatus && $num <= $this->_retryNum)
        {
            if ($num > 1)
            {
                // 第一次不行之后，休息0.5秒再次请求
                $this->_log("exec:retry`" . $this->url . "`" . $num . "`" . $this->_sleep, 'info');
                usleep($this->_sleep);
            }
            $res = $this->_doHttpRequest();
            $num++;
        }
        // 计数器清零
        self::$requestStatus = false;
        return $res;
    }
    /**
     * 发出http请求, 并且记录需要用到的log
     */
    private function _doHttpRequest()
    {
        $t = microtime(true);
        $this->proecess = curl_init($this->url);
        if ($this->requestMehtod == "post" && $this->buildQuery == 1)
        {
            $this->requestParam = is_array($this->requestParam) ? http_build_query($this->requestParam) : $this->requestParam;
        }
        if (!empty($this->headers))
        {
            curl_setopt($this->proecess, CURLOPT_HTTPHEADER, $this->headers);
        }
        
        // 如果请求的为https协议地址，则默认设置为不检查证书
        if (strpos($this->url, 'https://') === 0)
        {
            curl_setopt($this->proecess, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->proecess, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        
        curl_setopt($this->proecess, CURLOPT_HEADER, 0);
        curl_setopt($this->proecess, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->proecess, CURLOPT_ENCODING, $this->compression);
        curl_setopt($this->proecess, CURLOPT_HEADERFUNCTION, array(&$this, '_readHeader'));
        curl_setopt($this->proecess, CURLOPT_HTTPHEADER, array("Expect:"));
        // Response will be read in chunks of 64000 bytes
        curl_setopt($this->proecess, CURLOPT_BUFFERSIZE, 64000);
        curl_setopt($this->proecess, CURLOPT_TIMEOUT, $this->_timeout);
        if ($this->requestMehtod == "post")
        {
            curl_setopt($this->proecess, CURLOPT_POST, 1);
            curl_setopt($this->proecess, CURLOPT_POSTFIELDS, $this->requestParam);
        }
        curl_setopt($this->proecess, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->proecess, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($this->proecess);
        $res    = array();
        $info   = curl_getinfo($this->proecess);
        $t      = microtime(true) - $t;
        $this->status = array(
            'http_code'          => $info['http_code'],
            'total_time'         => $info['total_time'],
            'namelookup_time'    => $info['namelookup_time'],
            'connect_time'       => $info['connect_time'],
            'pretransfer_time'   => $info['pretransfer_time'],
            'starttransfer_time' => $info['starttransfer_time'],
        );
        if ($this->status['http_code'] != '200')
        {
            self::$requestStatus      = false;
            $this->status['error']    = curl_errno($this->proecess);
            $this->status['errorMsg'] = curl_error($this->proecess);
            $this->_log("parseStatus`" . $this->url . "`" . json_encode($this->status), 'warn');
            // 更新不可用的状态
            $this->_setUnAvaliable();
            // 非200返回false
            $return = false;
        }
        else
        {
            self::$requestStatus = true;
        }
        $this->_log("parseStatus`" . $return . "`" . json_encode($this->status));
        $this->_log('parseHeader`' . json_encode($this->responseHeader));
        curl_close($this->proecess);
        return $return;
    }
    /**
     * check domain is avaliable
     *
     * @return boolean  - 是否可用
     */
    private function _checkIsAvaliable()
    {
        $domain = $this->_getDomainByUrl();
        if (empty($domain))
        {
            return false;
        }
        if (!isset(self::$isAvaliable[$domain]) 
            || (defined('UZONE_COMMAND') && UZONE_COMMAND == true))
        {
            $this->_refreshAvaliableFlag($domain);
        }
        
        return self::$isAvaliable[$domain];
    }
    private function _refreshAvaliableFlag($domain)
    {
        // 检测是否有关闭的标记
        $key = 'utils.HttpClient.down.' . $domain;
        $res = $this->mc->get($key);
        // debug log
        $res = $res ? false : true;
        $str = $res ? 'true' : 'false';
        $this->_log("checkIsAvaliable`" . $key . "`" . $str);
        self::$isAvaliable[$domain] = $res;
    }
    /**
     * 设置当前不可用的情况
     *
     * @return boolean  - 是否设置成功
     */
    private function _setUnAvaliable()
    {
        // 获取当前url的domain
        $domain = $this->_getDomainByUrl();
        if (empty($domain))
            return false;
        $key = 'utils.HttpClient.downNum.' . $domain;
        $res = $this->mc->get($key);
        if ($res)
        {
            // 递增
            $res++;
            $newShutDownNum = $this->mc->increment($key);
            // 能执行到此过程，说明前提已经是：domain is avaliable('utils.HttpClient.down.$domain'=0)
            // 所以如果最新的数目大于最大允许错误数，说明以从上次错误恢复过来
            if ($newShutDownNum >= $this->_shutdownNum)
            {
                $this->mc->set($key, 1, $this->_shutdownNumLife);
            }
        }
        else
        {
            // 初始化, 记录5分钟之内连续失败的次数
            $res = 1;
            $this->mc->set($key, 1, $this->_shutdownNumLife);
        }
        // debug log
        $this->_log("setUnAvaliable`downNum`" . $key . "`" . $res . "`" . $this->_shutdownNumLife);
        if (intval($res) >= intval($this->_shutdownNum))
        {
            // 失败次数超过阀值
            $this->_log("setUnAvaliable: " . $domain, "error");
            $key = 'utils.HttpClient.down.' . $domain;
            $this->mc->set($key, 1, $this->_shutdownLife);
            self::$isAvaliable[$domain] = false;
        }
        return true;
    }

    /**
     * 从请求的url里面抽取 host & port
     */
    private function _getDomainByUrl()
    {
        if (empty($this->url))
            return '';
        $info = parse_url($this->url);
        $host = isset($info['host']) ? $info['host'] : '';
        $port = isset($info['port']) ? $info['port'] : '';
        return $host . $port;
    }

    /**
     * CURL callback function for reading and processing headers
     * Override this for your needs
     *
     * @param object $ch
     * @param string $header
     * @return integer
     */
    private function _readHeader($ch, $header)
    {
        //extracting example data: filename from header field Content-Disposition
        $this->_parseHeader($header);
        return strlen($header);
    }
    /**
     * 解析头部
     * @param string $header
     */
    private function _parseHeader($header)
    {
        if (empty($header))
            return false;
        $tmp = explode(': ', $header);
        if (!empty($tmp[0]) && !empty($tmp[1]))
        {
            $this->responseHeader[trim($tmp[0])] = trim($tmp[1]);
        }
        return true;
    }

    /**
     * 记录log
     * @param string $msg
     * @param string $level
     */
    private function _log($msg, $level = 'debug')
    {
        Logger::writeLog('utils_HttpClient', $level, $msg);
    }
    /**
     * 初始化
     * @param $cookies
     * @param $cookie
     * @param $compression
     * @param $proxy
     */
    private function __construct($compression = 'gzip', $proxy = '', $isEnableStatis = true)
    {
        $this->proxy = $proxy;
        $this->isEnableStatis = $isEnableStatis;
        $this->mc = \dal\Memcached::getInstance();
    }

    private $_timeout           = 5;
    private $_sleep             = 100000;
    private $_retryNum          = 1;
    private $_shutdownLife      = 30;
    private $_shutdownNum       = 100;
    private $_shutdownNumLife   = 300;
    private static $config          = array();
    private static $isAvaliable     = array();
    private static $requestStatus   = false;
    private $responseHeader         = array();
    private $isEnableStatis         = true;
    private $buildQuery             = 1;
    private $requestMehtod;
    private $requestParam;
    private $time;
    private $status;
    private $url;
    private $proecess;
    private $headers;
    private $user_agent;
    private $compression;
}
