<?php
namespace dal;

use base\Object;

error_reporting(0);

class Mysql extends Object
{
    /**
     * 获取单实例
     */
    public static function getInstance($config, $group)
    {
        #echo $group."|";
        if (empty(self::$_obj[$group])) {
            #echo "=".$group."|";
            self::$_obj[$group] = new self($config);
        }
        return self::$_obj[$group];
    }

    /**
     * 执行SQL语句(一般用来做update/delete/insert)
     *
     * @param string sql   require  需要执行的sql语句
     * @return
     * 成功返回true
     * 失败抛出MysqlProxyException异常
     */
    public function execute($sql)
    {
        if (!is_resource($this->link)) {
            $this->connect();
        }

        if (false === stripos($sql, "select") && false === stripos($sql, "ping")) {
            //file_put_contents("/tmp/sql.log", date("Y-m-d H:i:s")." ==".$sql."\n", FILE_APPEND);
        }
        $this->lastqueryid = mysql_query($sql, $this->link) or $this->halt(mysql_error(), $sql);

        $this->querycount++;
        return $this->lastqueryid;
    }

    /**
     * 查询数据
     *
     * @param string sql   require  需要查询的sql语句
     * @return
     * 成功返回 array
     * 失败抛出 MysqlProxyException异常
     */
    public function query($sql)
    {
        $this->execute($sql);
        if (!is_resource($this->lastqueryid)) {
            return $this->lastqueryid;
        }
        $datalist = array();
        while (($rs = $this->fetch_next()) != false) {
            $datalist[] = $rs;
        }
        $this->free_result();
        return $datalist;
    }

    /**
     * 初始化
     */
    public function __construct($config)
    {
        if (empty($config)) {
            throw new MysqlException("invalid mysql configs");
        }
        $this->open($config);
    }

    /**
     * 打开数据库连接,有可能不真实连接数据库
     * @param $config   数据库连接参数
     *
     * @return void
     */
    private function open($config)
    {
        $this->config = $config;
        if ($config['autoconnect'] == 1) {
            $this->connect();
        }
    }

    /**
     * 提示出错信息
     */
    private function halt($msg)
    {
        throw new MysqlException($msg);
    }

    /**
     * 真正开启数据库连接
     *
     * @return void
     */
    private function connect()
    {
        $func = $this->config['pconnect'] == 1 ? 'mysql_connect' : 'mysql_connect';
        if (!$this->link = $func($this->config['hostname'], $this->config['username'], $this->config['password'], 1)) {
            //var_dump($this->config);
            $this->halt('Can not connect to MySQL server');
            return false;
        }
        if ($this->version() > '4.1') {
            $charset = isset($this->config['charset']) ? $this->config['charset'] : '';
            $serverset = $charset ? "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary" : '';
            $serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',') . " sql_mode='' ") : '';
            $serverset && mysql_query("SET $serverset", $this->link);
        }
        if ($this->config['database'] && !mysql_select_db($this->config['database'], $this->link)) {
            $this->halt('Cannot use database ' . $this->config['database']);
            return false;
        }
        $this->database = $this->config['database'];
        return $this->link;
    }

    /**
     * 遍历查询结果集
     * @param $type     返回结果集类型
     *                  MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
     * @return array
     */
    public function fetch_next($type = MYSQL_ASSOC)
    {
        $res = mysql_fetch_array($this->lastqueryid, $type);
        if (!$res) {
            $this->free_result();
        }
        return $res;
    }

    /**
     * 释放查询资源
     * @return void
     */
    public function free_result()
    {
        if (is_resource($this->lastqueryid)) {
            mysql_free_result($this->lastqueryid);
            $this->lastqueryid = null;
        }
    }

    /**
     * 获取最后影响的行数
     */
    public function getAffectedRows()
    {
        return mysql_affected_rows($this->link);
    }

    /**
     * 获取上次插入的id
     */
    public function getInsertId()
    {
        //return mysql_insert_id($this->link);
        $ret = 0;
        $sql = "select LAST_INSERT_ID() as num";
        $res = mysql_query($sql, $this->link);
        $rs = mysql_fetch_array($res);
        $ret = isset($rs['num']) ? $rs['num'] : 0;
        mysql_free_result($res);
        unset($rs);
        return $ret;
    }

    /**
     * 检查字段是否存在
     * @param $table 表名
     * @return boolean
     */
    public function field_exists($table, $field)
    {
        $fields = $this->get_fields($table);
        return array_key_exists($field, $fields);
    }

    public function num_rows($sql)
    {
        $this->lastqueryid = $this->execute($sql);
        return mysql_num_rows($this->lastqueryid);
    }

    public function num_fields($sql)
    {
        $this->lastqueryid = $this->execute($sql);
        return mysql_num_fields($this->lastqueryid);
    }

    public function result($sql, $row)
    {
        $this->lastqueryid = $this->execute($sql);
        return @mysql_result($this->lastqueryid, $row);
    }

    public function error()
    {
        return @mysql_error($this->link);
    }

    public function errno()
    {
        return intval(@mysql_errno($this->link));
    }

    public function version()
    {
        if (!is_resource($this->link)) {
            $this->connect();
        }
        return mysql_get_server_info($this->link);
    }

    public function close()
    {
        if (is_resource($this->link)) {
            @mysql_close($this->link);
        }
    }


    /**
     * 对字段两边加反引号，以保证数据库安全
     * @param $value 数组值
     */
    public function add_special_char(&$value)
    {
        if ('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos($value, '`')) {
            //不处理包含* 或者 使用了sql方法。
        } else {
            $value = '`' . trim($value) . '`';
        }
        return $value;
    }

    /**
     * 对字段值两边加引号，以保证数据库安全
     * @param $value 数组值
     * @param $key 数组key
     * @param $quotation
     */
    public function escape_string(&$value, $key = '', $quotation = 1)
    {
        if ($quotation) {
            $q = '\'';
        } else {
            $q = '';
        }
        $value = $q . $value . $q;
        return $value;
    }

    private static $_obj;
    private $_AffectedRows = 0;
    private $_InsertId = 0;
    /**
     * 数据库配置信息
     */
    private $config = null;

    /**
     * 数据库连接资源句柄
     */
    public $link = null;

    /**
     * 最近一次查询资源句柄
     */
    public $lastqueryid = null;

    /**
     *  统计数据库查询次数
     */
    public $querycount = 0;
}
