<?php
namespace base;
use dal\MysqlException;
use dal\Mysql;
use dal\Memcached;
class Dao extends Object
{
    /**
     * 执行sql查询
     *
     * @param $where        查询条件[例`name`='$name']
     * @param $data         需要查询的字段值[例`name`,`gender`,`birthday`]
     * @param $limit        返回结果范围[例：10或10,10 默认为空]
     * @param $order        排序方式    [默认按数据库默认方式排序]
     * @param $group        分组方式    [默认为空]
     * @param $key          返回数组按键名排序
     * @param $cache		是否需要sql缓存array('status'=>true,'time'=>3600)[慎用该功能]
     * @return array        查询结果集数组
     */
    final public function select($where = '', $data = '*', $limit = '', $order = '', $group = '', $key='', $cache=array()) {
        if (is_array($where)) $where = $this->sqls($where);
        $where = $where == '' ? '' : ' WHERE '.$where;
		$order = $order == '' ? '' : ' ORDER BY '.$order;
		$group = $group == '' ? '' : ' GROUP BY '.$group;
		$limit = $limit == '' ? '' : ' LIMIT '.$limit;
		$field = explode(',', $data);
		array_walk($field, array($this, 'add_special_char'));
		$data = implode(',', $field);
		$sql = 'SELECT '.$data.' FROM `'.$this->table_name.'`'.$where.$group.$order.$limit;
		
    	//加入sql缓存
		if($cache['status']==true && $this->use_cache == true){
			return $this->cache($sql,$cache);
		}
		
		return $this->query($sql);
    }
    /**
     * 查询多条数据并分页
     *
     * @param $where
     * @param $order
     * @param $page
     * @param $pagesize
     * @param $cache		是否需要sql缓存array('status'=>true,'time'=>3600)[慎用该功能]
     * @return unknown_type
     */
    final public function listinfo($where = '', $order = '', $page = 1, $pagesize = 20, $key='', $setpages = 10,$array = array(), $cache=array()) {
        $this->number = $this->count($where, $cache);
        $page         = max(intval($page), 1);
        $offset       = $pagesize*($page-1);
        $this->pages  = pages($this->number, $page, $pagesize, $array, $setpages);
        $array        = array();
        if ($this->number > 0) {
            return $this->select($where, '*', "$offset, $pagesize", $order, '', $key, $cache);
        } else {
            return array();
        }
    }
    /**
     * 获取单条记录查询
     * @param $where        查询条件
     * @param $data         需要查询的字段值[例`name`,`gender`,`birthday`]
     * @param $order        排序方式    [默认按数据库默认方式排序]
     * @param $group        分组方式    [默认为空]
     * @param $cache		是否需要sql缓存array('status'=>true,'time'=>3600)[慎用该功能]
     * @return array/null   数据查询结果集,如果不存在，则返回空
     */
    final public function get_one($where = '', $data = '*', $order = '', $group = '', $cache=array()) {
        if (is_array($where)) $where = $this->sqls($where);
        $where = $where == '' ? '' : ' WHERE '.$where;
        $order = $order == '' ? '' : ' ORDER BY '.$order;
        $group = $group == '' ? '' : ' GROUP BY '.$group;
        $limit = ' LIMIT 1';
        $field = explode( ',', $data);
        array_walk($field, array($this, 'add_special_char'));
        $data = implode(',', $field);

        $sql = 'SELECT '.$data.' FROM `'.$this->table_name.'`'.$where.$group.$order.$limit;

        //加入sql缓存
        $cache['status'] = isset($cache['status']) ? $cache['status'] : '';
		if($cache['status']==true && $this->use_cache == true){
			$result = $this->cache($sql,$cache);
			return isset($result[0]) ? $result[0] : array();
		}
        
        $result = $this->query($sql);
        return isset($result[0]) ? $result[0] : array();
    }
    /**
     * 获取单条记录单个字段查询,直接返回第一字段的信息
     * @param $where        查询条件
     * @param $data         需要查询的字段值[例`name`]
     * @param $order        排序方式    [默认按数据库默认方式排序]
     * @param $group        分组方式    [默认为空]
     * @return array/null   数据查询结果集,如果不存在，则返回空
     */
    final public function get_one_field($where = '', $data = '*', $order = '', $group = '') {
        $result = $this->get_one($where, $data, $order, $group);
        return $result[$data];
    }
    /**
     * 执行添加记录操作
     * @param $data         要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
     * @param $return_insert_id 是否返回新建ID号
     * @param $replace 是否采用 replace into的方式添加数据
     * @return boolean
     */
    final public function insert($data, $return_insert_id = false, $replace = false) {
        if(!is_array( $data ) || $this->table_name == '' || count($data) == 0) {
            return false;
        }
       
		$this->check_read_only();

        $fielddata = array_keys($data);
        $valuedata = array_values($data);
        array_walk($fielddata, array($this, 'add_special_char'));
        array_walk($valuedata, array($this, 'escape_string'));
        
        $field = implode (',', $fielddata);
        $value = implode (',', $valuedata);

        $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
        $sql = $cmd.' '.$this->table_name.'('.$field.') VALUES ('.$value.')';
        $return = $this->execute($sql);
        $ret = $return_insert_id ? $this->insert_id() : $return;

		if($this->double_write)
		{
			if(in_array($this->table_name, array(
				"access_logs", "hash_buffer"
			)))
			{
				if(in_array($this->table_name, array("access_logs")))
				{
					if(!$return_insert_id)
					{
						$nemo_last_insert_id = $this->insert_id();	
					}
					else
					{
						$nemo_last_insert_id = $ret;
					}
					$sql = 'replace into '.$this->table_name.'('."id, ".$field.') VALUES ('.$nemo_last_insert_id.', '.$value.')';	
				}
				else if(in_array($this->table_name, array("hash_buffer")))
				{
					$sql = 'replace into '.$this->table_name.'('.$field.') VALUES ('.$value.')';	
				}
				//fei 2012-12-27 这里先取巧先
				NEMO_insert_log_pk($sql, "insert", $data['projekt']);	
			}
		}
		return $ret;
    }
        
    /**
     * 直接执行sql查询
     * @param $sql                          查询sql语句
     * @return  boolean/query resource      如果为查询语句，返回资源句柄，否则返回true/false
     *
     * @throw   DaoException
     */
    final public function query($sql) {
        try{
            $this->init();
            self::$_sql_counts++;
            $SQL_time_start = microtime(true);
            $ret = self::$_dbs[$this->server_name]->query($sql);
            $SQL_time_ex = microtime(true) - $SQL_time_start;
            self::$_sql_times += $SQL_time_ex;
            $msg = $this->server_name.'`'.round($SQL_time_ex,4).'`'.round(self::$_sql_times,4).'`'.round(self::$_sql_counts,4).'`'.$sql;
            $this->log("sql_query:".$msg);
	        if($SQL_time_ex > self::$_sql_slow_time) {//sql执行时间大于0.5的会被记录下来
	      	}
            return $ret;
        }
        catch (MysqlException $e)
        {
            throw new DaoException("DAO: query exception: " . $e->getMessage() . " sql is " . $sql);
        }
    }

    /**
     * 直接执行sql查询
     *
     * @param $sql                          查询sql语句
     * @return  boolean/query resource      如果为查询语句，返回资源句柄，否则返回true/false
     *
     * @throw   DaoException
     */
    final public function execute($sql) {
        try{
            $this->init();
            self::$_sql_counts++;
            $SQL_time_start = microtime(true);
            $ret = self::$_dbs[$this->server_name]->execute($sql);
            $SQL_time_ex = microtime(true) - $SQL_time_start;
            self::$_sql_times += $SQL_time_ex;
        	$msg = $this->server_name.'`'.round($SQL_time_ex,4).'`'.round(self::$_sql_times,4).'`'.round(self::$_sql_counts,4).'`'.$sql;
            $this->log("sql_execute:".$msg);
	        if($SQL_time_ex > self::$_sql_slow_time) {//sql执行时间大于0.5的会被记录下来
	      	}
            return $ret;
        }
        catch (MysqlException $e)
        {
            throw new DaoException("DAO: execute exception: " . $e->getMessage() . " sql is " . $sql);
        }
    }
    final public static function get_sql_counts(){
        return self::$_sql_counts;
    }
    final public static function get_sql_times(){
        return self::$_sql_times;
    }
    /**
     * 获取最后一次添加记录的主键号
     * @return int 
     */
    final public function insert_id() {
        return self::$_dbs[$this->server_name]->getInsertId();
    }
    /**
     * 获取最后影响的行数
     * @return int 
     */
    final public function affected_rows() {
        return self::$_dbs[$this->server_name]->getAffectedRows();
    }   
    /**
     * 执行更新记录操作
     * @param $data         要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
     *                      为数组时数组key为字段值，数组值为数据取值
     *                      为字符串时[例：`name`='nemo',`hits`=`hits`+1]。
     *                      为数组时[例: array('name'=>'nemo','password'=>'123456')]
     *                      数组的另一种使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1
     * @param $where        更新数据时的条件,可为数组或字符串
     * @return boolean
     */
    final public function update($data, $where = '') {
        if (is_array($where)) $where = $this->sqls($where);
        if($this->table_name == '' or $where == '') {
            return false;
        }
		$this->check_read_only();

        $where = ' WHERE '.$where;
        $field = '';
        if(is_string($data) && $data != '') {
            $field = $data;
        } elseif (is_array($data) && count($data) > 0) {
            $fields = array();
            foreach($data as $k=>$v) {
                switch (substr($v, 0, 2)) {
                    case '+=':
                        $v = substr($v,2);
                        if (is_numeric($v)) {
                            $fields[] = $this->add_special_char($k).'='.$this->add_special_char($k).'+'.$this->escape_string($v, '', false);
                        } else {
                            continue;
                        }
                        
                        break;
                    case '-=':
                        $v = substr($v,2);
                        if (is_numeric($v)) {
                            $fields[] = $this->add_special_char($k).'='.$this->add_special_char($k).'-'.$this->escape_string($v, '', false);
                        } else {
                            continue;
                        }
                        break;
                    default:
                        $fields[] = $this->add_special_char($k).'='.$this->escape_string($v);
                }
            }
            $field = implode(',', $fields);
        } else {
            return false;
        }

        $sql = 'UPDATE `'.$this->table_name.'` SET '.$field.$where;
        $ret = $this->execute($sql);
		return $ret;
    }
    
    /**
     * 执行删除记录操作
     * @param $where        删除数据条件,不充许为空。
     * @return boolean
     */
    final public function delete($where) {
        if (is_array($where)) $where = $this->sqls($where);
        if ($this->table_name == '' || $where == '') {
            return false;
        }

		$this->check_read_only();

        $where = ' WHERE '.$where;
        $sql = 'DELETE FROM `'.$this->table_name.'`'.$where;
        return $this->execute($sql);
    }
    
    /**
     * 计算记录数
     * @param string/array $where 查询条件
     * @param $cache		是否需要sql缓存array('status'=>true,'time'=>3600)[慎用该功能]
     */
    final public function count($where = '', $cache=array()) {
        $r = $this->get_one($where, "COUNT(*) AS num","","",$cache);
        return $r['num'];
    }
    
    /**
     * 将数组转换为SQL语句 , 如果传入$in_cloumn 生成格式为 IN('a', 'b', 'c')
     * @param $data 条件数组或者字符串
     * @param $front 连接符
     * @param $in_column 字段名称
     * @return string
     */
    final public function sqls($where, $front = ' AND ', $in_column = false) {
        if($in_column && is_array($where)) {
            $ids = '\''.implode('\',\'', $where).'\'';
            $sql = "$in_column IN ($ids)";
            return $sql;
        } else {
            if ($front == '') {
                $front = ' AND ';
            }
	        if (is_array($where)) {
	            $sql = '';
	            foreach ($where as $key=>$val) {
	                $val  = addslashes($val);
	                $sql .= $sql ? " $front `$key` = '$val' " : " `$key` = '$val'";
	            }
	            return $sql;
	        } else {
	            return $where;
	        }
        }
    }
    /**
     * 对字段两边加反引号，以保证数据库安全
     * @param $value 数组值
     */
    final public function add_special_char(&$value) {
        if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos ( $value, '`')) {
            //不处理包含* 或者 使用了sql方法。
        } else {
            $value = '`'.trim($value).'`';
        }
        return $value;
    }
    /**
     * 对字段值两边加引号，以保证数据库安全
     * @param $value 数组值
     * @param $key 数组key
     * @param $quotation 
     */
    final public function escape_string(&$value, $key='', $quotation = 1) {
        if ($quotation) {
            $q = '\'';
        } else {
            $q = '';
        }
    	// 如果已经是在php.ini里面开启magic_quotes, 则不需要再 addslashes
        if (!get_magic_quotes_gpc())
        {
            $value = $q.addslashes($value).$q;
        }
        else
        {
            // 一些内部扩展获取的数据不会自动加 addslashes
            // 需要手工判断是否已经加过，如果还没有加过，需要再加一次
            $pos = strpos($value, "'");
            if ($pos !== false)
            {   
                $pre = substr($value, $pos -1, 1); 
                if ($pre !== '\\')
                {
                    $value = $q . addslashes($value) . $q;
                    return $value;
                }
            }
            $value = $q . $value . $q;
        }
        return $value;
    }
    /**
     * @desc 缓存处理
     * @param $sql
     */
    private function cache($sql,$cache){
    	$mc_key = Config::load_mc ( "wapka_sql" ) . md5($sql);
		$mc_data = Memcached::getInstance()->get($mc_key);
		if(empty($mc_data)){
			$my_data = $this->query($sql);
			if(!empty($my_data)){
				$time = ($cache['time']) ? $cache['time'] : 3600;
				Memcached::getInstance()->set($mc_key,$my_data,$time);
				$mc_data = $my_data;
			}
		}
		return $mc_data;
    }
    /**
     * 初始化
     */
    public function init($force = false)
    {

        if ($force == true || empty(self::$_dbs[$this->server_name]))
        {
            $config   = \Yaf_Registry::get('config');
            if (empty($config->mysql[$this->server_name]))
            {
                throw new DaoException("init: unknow server type: " . $this->server_name);
            }
            self::$_dbs[$this->server_name] = Mysql::getInstance($config->mysql[$this->server_name], $this->server_name);
        }
    }

	public function check_read_only()
	{
		if($this->read_only && in_array($this->table_name, array(
			"access_logs", "hash_buffer"
		)))
		{
			utils\Common::r('ban.php');	
			exit(0);
		}
	}

    /**
     * 初始化
     */
    public function __construct()
    {
    }
    public static $init = false;
    public $db;
    public $server_name = '';
    public static $_dbs = array();
    public static $_sql_counts = 0;
    public static $_sql_times = 0;
    private static $_sql_slow_time = 0.5;//单位秒
    public $double_write = false;
	public $read_only = false;
}
