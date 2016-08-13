<?php
namespace base;
use base\ServiceFactory;

class Curd extends Dao
{
    //删除状态
    const RECORD_STATUS_NORMAL = 0; //正常
    const RECORD_STATUS_DELETE = 1; //删除

    //字段类型,用于检查数据的合法性，有待完善
    const FIELD_TYPE_INT = "int";
    const FIELD_TYPE_VARCHAR = "varchar"; //这里是包含text字段的，也就是text也是设置成FIELD_TYPE_VARCHAR
    const FIELD_TYPE_DATE = "date";
    const FIELD_TYPE_EMAIL = "email";

    //curd的操作列表，使用位来运算的
    const CURD_ACTION_NONE = 1;
    const CURD_ACTION_CREATE = 2;
    const CURD_ACTION_UPDATE = 4;
    const CURD_ACTION_MANAGE = 8;
    const CURD_ACTION_READ = 16;
    const CURD_ACTION_RM = 24; //这个是复合操作 CURD_ACTION_MANAGE | CURD_ACTION_READ
    const CURD_ACTION_DELETE = 32;
    const CURD_ACTION_REF = 64;
    const CURD_ACTION_RMR = 88;
    const CURD_ACTION_ALL = 126; //这个是复合操作 

    const CURD_SELECT_TYPE_QUERY = "query";
    const CURD_SELECT_TYPE_COUNT = "count";

    //字段比较方法
    const FIELD_CMP_TYPE_EQUAL = 0; //精确匹配
    const FIELD_CMP_TYPE_LIKE = 1; //模糊匹配
    const FIELD_CMP_TYPE_MATCH_PREFIX_LIKE = 2; //模糊匹配后面
    const FIELD_CMP_TYPE_MATCH_SUFFIX_LIKE = 3; //模糊匹配后面
    const FIELD_CMP_TYPE_GT = 4; // >
    const FIELD_CMP_TYPE_GE = 5; // >=
    const FIELD_CMP_TYPE_LT = 6; // <
    const FIELD_CMP_TYPE_LE = 7; // <=
    const FIELD_CMP_TYPE_RAW = 8; //
    const FIELD_CMP_TYPE_IN = 9; // in

    public $tableName = "";
    public $realDelete = false; 
    public $deleteStatusField = "islock";
    public $tableConfig = array();

    //TODO 找天分离出来
    public $idKey = "";
    public $nameKey = "";
    public $codeKey = "";

    /*
        name 名称
        type 类型
        update 是否接受update操作的参数,当设置为false的时候，在update操作的时候，是不会接受外部参数的，一般用于lastmodify
        unique 是否唯一
        require 是否必须外部传递参数，假如没有外部没有传递参数，并且没有设置默认值，则报错
        show 在什么操作下显示数据, 假如show_all太大，建议设置成CURD_ACTION_RM
        cmpType 字段数据比较方式
        default 默认值,支持有限的php函数,这里有待完善
        setDefault 在什么操作下设置默认值
        equal 和某参数要相等，例子password和comfirm password,只在create的时候生效
        exist 该记录要存在的, 只在create的时候生效
        alias 别名,用于数据库查询
        assign 自动根据当前参数从别的数据表中查数据填充,只在create的时候生效
    */

    public function __construct() {
        // 指定mysql表名字
        /*
        $this->table_name  = "portal_template";
        $this->server_name = 'portal-db1';
        $this->use_cache = true;//控制sql语句是否允许使用缓存（总开关）
        */
        parent::__construct();
    }   

    /**
     * @desc 添加数据
     * @param array $rawParam外部参数
     */
    public function createImpl($rawParam, &$error)
    {
        //过滤参数，并检查合法性
        $param = $this->filterCreateParam($rawParam, $error); 
        if(false === $param)
        {
            return false;
        }

        if(!$this->checkAssign($param, $error))
        {
            return false; 
        }

        //检查不能缺少的
        if(!$this->checkRequire(self::CURD_ACTION_CREATE, $param, $error))
        {
            return false; 
        }

        //检查重复的
        if(!$this->checkUnique($param, $error))
        {
            return false; 
        }

        //添加到数据库
        $lastInsertId = $this->insert($param, true); 
        return $lastInsertId;
    }

    /**
     * @desc 获取数据列表
     */
    public function manageImpl($rawParam, &$error)
    {
        $start = isset($rawParam['start'])?intval($rawParam['start']):0;
        $limit = isset($rawParam['limit'])?intval($rawParam['limit']):30;
        $orderBy = isset($rawParam['orderBy'])?$rawParam['orderBy']:$this->tableConfig['pk'];
        $sortType = isset($rawParam['sortType'])?$rawParam['sortType']:"";
        $start = $start<0?0:$start;
        $limit = $start<0?30:$limit;
       
        //过滤参数
        $param = $this->filterManageParam($rawParam, $error); 
        if(false === $param)
        {
            return false;
        }

        $rowCount = $this->countImpl($param, $error);
        if(empty($rowCount))
        {
            return array("rowCount"=>0, "rows"=>array());
        }

        $rows = array();
        if(!empty($rowCount))
        {
            //获取列表
            $rows = $this->selectImpl(self::CURD_SELECT_TYPE_QUERY, self::CURD_ACTION_MANAGE, $param, array(
                "orderBy"=>$orderBy,
                "sortType"=>$sortType,
                "start"=>$start,
                "limit"=>$limit,
            ), $error);
        }

        //格式化数据
        /*
        $comboList = array();
        if ($rawParam["type"] == "combo")
        {
            $this->getComboList($comboList, $rows, 0, "┣", $this->idKey, $this->codeKey, $this->nameKey);
            $rows = $comboList;
        }
        */

        return array(
            "rowCount"=>$rowCount,
            "rows"=>$rows,
        );
    }

    /**
     * @desc 根据pk获取数据,这里只能根据主键来获取数据
     */
    public function readImpl($rawParam, &$error)
    {
        $pkKey = $this->tableConfig['pk'];
        if(!isset($rawParam[$pkKey]))
        {
            $error[] = "参数".$pkKey."不能为空";
            return false; 
        }
        $pkValue = trim($rawParam[$pkKey]); 
		/*
        $pkValue = intval($rawParam[$pkKey]); 
        if($pkValue < 1)
        {
            $error[] = "非法参数";
            return false; 
        }
		*/
        return $this->readOneImpl(self::CURD_SELECT_TYPE_QUERY, array(
            $pkKey => $pkValue,
        ), $error);
    }

    public function readOneImpl($type, $param, &$error)
    {
        $result = $this->selectImpl($type, self::CURD_ACTION_READ, $param, true, $error);
        $result = isset($result[0]) ? $result[0] : array();
        return $result;
    }

    public function countImpl($param, &$error)
    {
        $result = $this->selectImpl(self::CURD_SELECT_TYPE_COUNT, self::CURD_ACTION_READ, $param, false, $error);
        return intval($result[0]['rowCount']);
    }

    public function selectImpl($type, $action, $param, $limitOne, &$error)
    {
        if($this->checkNeedJoin($action))
        {
            //需要join 
            $readFields = $this->getAllFields($action);
            $sql = $this->buildSelectJoinSql($type, $readFields, $param, $limitOne);  
        }
        else
        {
            $sql = $this->buildSelectSql($type, $action, $param, $limitOne);
        }
        $ret = $this->query($sql);
        return $ret;
    }

    /**
     * @desc 删除数据,只能根据主键删除
     */
    public function deleteImpl($rawParam, &$error)
    {
        $pkKey = $this->tableConfig['pk'];
        if(!isset($rawParam[$pkKey]))
        {
            $error[] = "参数".$pkKey."为空";
            return false; 
        }

        if(is_array($rawParam[$pkKey]))
        {
            //支持删除多个
            foreach($rawParam[$pkKey] as $pkValue)
            {
                $pkValue = intval($pkValue); 
                if($pkValue < 1)
                {
                    $error[] = "非法参数";
                    return false; 
                }

                if($this->realDelete)
                {
                    //真删除
                    $sql = "delete from ".$this->tableName." where ".$this->tableConfig['pk']."='".$pkValue."' limit 1"; 
                }
                else
                {
                    //假删除
                    $sql = "update ".$this->tableName." set ".$this->deleteStatusField."='".self::RECORD_STATUS_DELETE."' where ".$this->tableConfig['pk']."='".$pkValue."' and ".$this->deleteStatusField."=".self::RECORD_STATUS_NORMAL." limit 1";
                }
                $ret = $this->execute($sql);
            }
        }
        else
        {
            $pkValue = intval($rawParam[$pkKey]); 
            if($pkValue < 1)
            {
                $error[] = "非法参数";
                return false; 
            }

            if($this->realDelete)
            {
                //真删除
                $sql = "delete from ".$this->tableName." where ".$this->tableConfig['pk']."='".$pkValue."' limit 1"; 
            }
            else
            {
                //假删除
                $sql = "update ".$this->tableName." set ".$this->deleteStatusField."='".self::RECORD_STATUS_DELETE."' where ".$this->tableConfig['pk']."='".$pkValue."' and ".$this->deleteStatusField."=".self::RECORD_STATUS_NORMAL." limit 1";
            }
            $ret = $this->execute($sql);
        }
        if(!$ret)
        {
            $error[] = "删除失败";
            return false;
        }
        return true;
    }

    /**
     * @desc 更新数据,只能根据主键更新数据,
     */
    public function updateImpl($rawParam, &$error)
    {
        $pkKey = $this->tableConfig['pk'];
        if(!isset($rawParam[$pkKey]))
        {
            $error[] = "没有传参数".$pkKey;
            return false; 
        }
        $pkValue = intval($rawParam[$pkKey]); 
        if($pkValue < 1)
        {
            $error[] = "非法参数".$pkKey.", value='".$rawParam[$pkKey]."'";
            return false; 
        }

        //过滤参数
        $param = $this->filterUpdateParam($rawParam, $error);
        if(false === $param)
        {
            return false;
        }

        //检查不能缺少的
        //TODO 这里是否要检查require呢？因为是数据更新，可能只要更新一小部分而不是全部
        /*
        fei 2013-06-07 注释
        if(!$this->checkRequire(self::CURD_ACTION_UPDATE, $param, $error))
        {
            return false; 
        }
        */

        //检查重复的
        if(!$this->checkUnique($param, $error, $pkValue))
        {
            return false; 
        }

        return $this->updateOneImpl($param, array(
            $pkKey=>$pkValue
        ), $error);
    }

    public function updateOneImpl($param, $cond, &$error)
    {
        return $this->updateSomeImpl($param, $cond, true, $error);
    }

    public function updateSomeImpl($param, $cond, $limitOne, &$error)
    {
        $sql = $this->buildUpdateSql($param, $cond, $limitOne);

        $ret = $this->execute($sql);
        if(!$ret)
        {
            $error[] = "更新失败";
            return false;
        }
        return true; 
    }

    /**
     * @desc 组装sql,用于update,更新数据
     */
    private function buildUpdateSql($field=array(), $cond=array(), $limitOne=true)
    {
        $sql = "update ".$this->tableName." set ";
        $sep = "";
        foreach($field as $key=>$value)
        {
            $sql .= $sep . "`".$key."`" . "='".$value."'";
            $sep = ", ";
        }
        $sql .= " where ";
        $sep = "";
        foreach($cond as $key=>$value)
        {
            $sql .= $sep . "`".$key."`" . "='".$value."'";
            $sep = " and ";
        }
        if($limitOne)
        {
            $sql .= " limit 1";
        }
        return $sql;
    }

    /**
     * @desc 组装select的sql语句
     */
    private function buildSelectSql($type, $action, $cond=array(), $limitOne=true)
    {
        $flag = true;
        $sql = "select ";
        if(self::CURD_SELECT_TYPE_QUERY == $type)
        {
            $sep = "";
            foreach($this->tableConfig['fields'] as $key=>$config)
            {
                //判断当前字段是否显示
                if(isset($config['show']) && !($action & $config['show']))
                {
                    continue; 
                }
                $sql .= $sep . "`".$key."`";
                $sep = ",";
            }
        }
        else
        {
            $sql .= " count(1) as rowCount "; 
            //count的时候是不需要加上limit 1的
            $limitOne = false;
        }
        $sql .= " from ".$this->tableName." ";
        $sep = "";
        foreach($cond as $key => $value)
        {
			if("" == $value)
			{
				continue;
			}
            //过滤不合法的key
            if(isset($this->tableConfig['fields'][$key]))
            {
                if($flag)
                {
                    $sql .= " where ";
                    $flag = false;
                }
                //$sql .= $sep . $key."='".$value."'"; 


                $cmpType = isset($this->tableConfig['fields'][$key]['cmpType'])?$this->tableConfig['fields'][$key]['cmpType']:self::FIELD_CMP_TYPE_EQUAL;
                $type = isset($this->tableConfig['fields'][$key]['type'])?$this->tableConfig['fields'][$key]['type']:self::FIELD_TYPE_INT;

                //判断字段比较的类型是精确匹配还是模糊匹配,只有varchar支持like的，其他类型只能精确匹配
                if(self::FIELD_CMP_TYPE_LIKE == $cmpType && self::FIELD_TYPE_VARCHAR == $type)
                {
                    $sql .= $sep . $key." like '%".trim($value)."%'";
                }
                else if(self::FIELD_CMP_TYPE_MATCH_PREFIX_LIKE == $cmpType && self::FIELD_TYPE_VARCHAR == $type)
                {
                    $sql .= $sep . $key." like '".trim($value)."%'";
                }
                else if(self::FIELD_CMP_TYPE_MATCH_SUFFIX_LIKE == $cmpType && self::FIELD_TYPE_VARCHAR == $type)
                {
                    $sql .= $sep . $key." like '%".trim($value)."'";
                }

                //fei 2014-05-05
                else if(self::FIELD_CMP_TYPE_GT == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." > '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_GE == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." >= '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_LT == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." < '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_LE == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." <= '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_RAW == $cmpType)
                {
                    $sql .= $sep . $key." ".$value." ";
                }
                else if(self::FIELD_CMP_TYPE_IN == $cmpType)
                {
                    $sql .= $sep . $key." in (".$value.") ";
                }
                else
                {
                    $sql .= $sep . $key."='".$value."'";
                }

                $sep = " and ";
            }
        }
        if(is_array($limitOne))
        {
            $sql .= " order by ".$limitOne['orderBy']." ".$limitOne['sortType']." limit ".$limitOne['start'].", ".$limitOne['limit'];
        }
        else if(is_bool($limitOne) && $limitOne)
        {
            $sql .= " limit 1"; 
        }
        return $sql;
    }

    private function formatData($data)
    {
    
    }

    /**
     * @desc 过滤create的参数
     */
    private function filterCreateParam($param, &$error)
    {
        return $this->filterParam(self::CURD_ACTION_CREATE, $param, $error);
    }

    /**
     * @desc 过滤update参数
     */
    private function filterUpdateParam($param, &$error)
    {
        return $this->filterParam(self::CURD_ACTION_UPDATE, $param, $error, true);
    }

    /**
     * @desc 过滤manage参数
     */
    private function filterManageParam($param, &$error)
    {
        return $this->filterParam(self::CURD_ACTION_MANAGE, $param, $error);
    }

    /**
     * @desc 过滤参数
     */
    private function filterParam($action, $param, &$error, $skipPk=false)
    {
        $flag = true;
        $ret = array();
        foreach($param as $key => $value)
        {
            if("NULL" == $value || "null" == $value)
            {
                continue; 
            }
            //支持跨表
            if(false !== strpos($key, "_dot_"))
            {
                $ret[$key] = $param[$key]; 
                continue; 
            }
            if(isset($this->tableConfig['fields'][$key]))
            {
                if($skipPk && $key == $this->tableConfig['pk'])
                {
                    continue;
                }
                $fieldConfig = $this->tableConfig['fields'][$key];

                //只有create和update需要检查参数合法性
                if(($action & self::CURD_ACTION_CREATE) && ($action & self::CURD_ACTION_UPDATE) )
                {
                    if(!$this->checkParamType($fieldConfig, $value))
                    {
                        $error[] = "非法参数, key=".$key.", value=".$value;
                        $flag = false;
                        continue;
                    }
                }

                //检查相等性,equal
                if(($action & self::CURD_ACTION_CREATE) && isset($fieldConfig['equal']) && !empty($fieldConfig['equal']))
                {
                    if($value != $param[$fieldConfig['equal']])
                    {
                        $error[] = "输入有误, ".$fieldConfig['name']."和".$param[$fieldConfig['equal']]."不相等";
                        $flag = false;
                        continue;
                    }
                }

                //检查exist
                if(($action & self::CURD_ACTION_CREATE) && isset($fieldConfig['exist']) && !empty($fieldConfig['exist']))
                {
                    $className = $fieldConfig['exist'][0];//类名 
                    $checkField = $fieldConfig['exist'][1];//字段名称
                    $ret = ServiceFactory::getService($className)->checkExist(array($checkField=>$value));
                    if(!$ret)
                    {
                        $error[] = "字段".$fieldConfig['name']."value=".$value."依赖".$className.".".$checkField."存在失败";
                        $flag = false;
                        continue;
                    }
                }


                //只有在update需要检查update设置
                if($action & self::CURD_ACTION_UPDATE)
                {
                    //过滤那些不能更新的字段
                    if(isset($fieldConfig['update']) && false === $fieldConfig['update'])
                    {
                        continue;
                    }
                }
                
                $ret[$key] = $param[$key]; 
            }
        }
        if(!$flag)
        {
            return false;
        }
        return $ret;
    }

    /**
     * @desc 返回默认值 
     */
    private function fieldDefaultValue($func)
    {
        $func = substr($func, strlen("php:")); 
        switch($func)
        {
            case "date":
                return date("Y-m-d H:i:s");
            break;
            case "time":
                return time();
            break;
            default:
                die("not support fieldDefaultValue func [".$func."]");
            break;
        }
    }

    /**
     * @desc 判断是否可以设置默认值
     */
    private function canSetDefault($fieldConfig, $action)
    {
        $setDefault = $fieldConfig['setDefault'];
        if(self::CURD_ACTION_ALL == $setDefault || ($action & $setDefault))
        {
           return true; 
        }
        return false; 
    }

    public function getOneField($field, $cond)
    {
        $sql = "select ".$field." from ".$this->tableName." where ".$cond." limit 1"; 
        $result = $this->query($sql);
        $result = isset($result[0][$field])?$result[0][$field]:"";
        return $result;
    }

    private function checkAssign(&$param, &$error)
    {
        $ret = true;
        foreach($this->tableConfig['fields'] as $key => $config)
        {
            if(isset($config['assign']) && !empty($config['assign']))
            {
                $className = $config['assign'][0]; //类名
                $queryField = $config['assign'][1]; //查询的字段
                $condField = $config['assign'][2]; //查询的字段
                $valueField = $config['assign'][3]; //当前值的字段
                if(!isset($param[$valueField]))
                {

                    $error[] = "自动填充，需要传递参数".$key."[".$this->tableConfig['fields'][$key]['name']."]"; 
                    $ret = false;
                    continue;
                }
                $param[$key] = ServiceFactory::getService($className)->getOneField($queryField, $condField."='".$valueField."'"); 
            }
        }
        return $ret; 
    }

    /**
     * @desc 判断参数是否缺少，这个函数混杂了set default的功能!!!!
     */
    private function checkRequire($action, &$param, &$error)
    {
        $ret = true;
        foreach($this->tableConfig['fields'] as $key => $config)
        {
            if(self::CURD_ACTION_UPDATE == $action && false === $config['update'])
            {
                //这些变量在上层就过滤了
                continue; 
            }
            if(isset($config['require']) && $config['require'])
            {
                //参数不能空的
                if(!isset($param[$key]))
                {
                    //参数是为空的
                    if(isset($config['default']))
                    {
                        if($this->canSetDefault($config, $action))
                        {
                            //有设置默认值
                            if(0 === strpos($config['default'], "php:"))
                            {
                                $param[$key] = $this->fieldDefaultValue($config['default']); 
                            }
                            else
                            {
                                $param[$key] = $config['default']; 
                            }
                        }
                        else
                        {
                            $error[] = "".$config['name']."[".$key."]不能为空"; 
                            $ret = false;
                        }
                    }
                    else
                    {
                        $error[] = "".$config['name']."[".$key."]不能为空"; 
                        $ret = false;
                    }
                }
            }
            else if(isset($config['default']))
            {
                //可以设置默认值
                if($this->canSetDefault($config, $action))
                {
                    //设置默认值
                    if(!isset($param[$key]))
                    {
                        //有设置默认值
                        if(0 === strpos($config['default'], "php:"))
                        {
                            $param[$key] = $this->fieldDefaultValue($config['default']); 
                        }
                        else
                        {
                            $param[$key] = $config['default']; 
                        }
                    }
                }

            }
        }
        return $ret;
    }

    /**
     * @desc 判断字段是否唯一
     */
    private function checkUnique($param, &$error, $skipPkValue=0)
    {
        $ret = true;
        $pkKey = $this->tableConfig['pk'];
        foreach($this->tableConfig['fields'] as $key => $config)
        {
            //要求唯一，而且参数里面带有这个值
            if(isset($config['unique']) && $config['unique'] && isset($param[$key]))
            {
                if(is_array($config['unique']))
                {
                    $tips = array();
                    $tips[] = "字段".$key."[".$param[$key]."]";

                    $cond = $key."='".$param[$key]."'"; 
                    $sep = " and ";
                    foreach($config['unique'] as $k => $value)
                    {
                        if("*" === $value)
                        {
                            $cond .= $sep . $k ."='".$param[$k]."'"; 
                            $tips[] = "字段".$k."[".$param[$k]."]";
                        }
                        else
                        {
							$value = empty($value)?"0":$value;
                            $cond .= $sep . $k ."='".$value."'"; 
                            $tips[] = "字段".$k."[".$value."]";
                        }
                    }
                    $sql = "select ".$pkKey." from ".$this->tableName." where ".$cond." limit 1"; 
			
                    $tmp = $this->query($sql);
                    if(!empty($tmp))
                    {
                        //当是更新的时候，要跳过当前自己的记录
                        if((!empty($skipPkValue) && $tmp[0][$pkKey] != $skipPkValue) || empty($skipPkValue))
                        {
                            $error[] = implode("和", $tips)."组合已经存在";
                            $ret = false;
                        }
                    }
                }
                else
                {
                    $sql = "select ".$pkKey." from ".$this->tableName." where ".$key."='".$param[$key]."' limit 1"; 
                    $tmp = $this->query($sql);
                    if(!empty($tmp))
                    {
                        //当是更新的时候，要跳过当前自己的记录
                        if((!empty($skipPkValue) && $tmp[0][$pkKey] != $skipPkValue) || empty($skipPkValue))
                        {
                            $error[] = $config['name']."[".$param[$key]."]已经存在";
                            $ret = false;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * @desc 检查参数类型的合法性
     * @param array $fieldConfig
     */
    private function checkParamType($fieldConfig, $value)
    {
        $type = self::FIELD_TYPE_INT;
        if(isset($fieldConfig['type']))
        {
            $type = $fieldConfig['type'];
        }
        switch($type)
        {
            case self::FIELD_TYPE_INT:
                if(!is_numeric($value))
                {
                    return false;
                }
            break;
            case self::FIELD_TYPE_VARCHAR:

            break;
            case self::FIELD_TYPE_DATE:

            break;
            case self::FIELD_TYPE_EMAIL:
                if(false === filter_var($value, FILTER_VALIDATE_EMAIL)) return false;
            break;
            default:
                die("checkParamType not support field type[".$type."]");
            break;
        }
        return true; 
    }

    /**
     * @desc 判断是否需要使用left join拼装sql语句
     */
    private function checkNeedJoin($action)
    {
        $fields = $this->tableConfig['fields']; 
        foreach($fields as $key => $fieldConfig)
        {
            //没有设置show就是show all
            if(
                (!isset($fieldConfig['show']) || ($action & $fieldConfig['show'])) && 
                isset($fieldConfig['ref']) && 
                !empty($fieldConfig['ref'])
            )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @desc 获取当前操作需要获取的字段名称
     */
    public function getFields($action)
    {
        $ret = array();
        $fields = $this->tableConfig['fields']; 
        foreach($fields as $key => $fieldConfig)
        {
            if(!isset($fieldConfig['show']) || ($action & $fieldConfig['show']))
            {
                //假如是有设置别名的话，就返回别名,假如别名还冲突的话，那就会导致sql语句出错的
                if(isset($fieldConfig['alias']) && !empty($fieldConfig['alias']))
                {
                    //别名相同，会导致同一个字段，2个as
                    $key .= " as " . $fieldConfig['alias']; 
                }
                $ret[] = $this->tableName.".".$key;
            }
        }
        return $ret;
    }

    /**
     * @desc 获取所有要查询的字段（根据$action）,用于join查询的
     * @param string $action 行为
     * @return array
     */
    private function getAllFields($action)
    {
        $allFields = array();
        $tmpFields = $this->getFields($action);
        $ret = array();
        $ret[] = array(
                "tableName"=>$this->tableName, 
                "fields"=>$tmpFields,
            );
        foreach($tmpFields as $field)
        {
            $tmpArray = explode(".", $field); 
            $field = end($tmpArray);
            $allFields[] = $field;
        }

        $fields = $this->tableConfig['fields']; 
        foreach($fields as $key => $fieldConfig)
        {
            if(isset($fieldConfig['ref']))
            {
                $className = $fieldConfig['ref'][0]; //类名
                $refField = $fieldConfig['ref'][1]; //字段
           
                //获取关联表的字段
                $tmpObj = ServiceFactory::getService($className);
                $tmpFields = $tmpObj->getFields(self::CURD_ACTION_REF);
                foreach($tmpFields as $k => $field)
                {
                    $tmpArray = explode(".", $field);
                    $field = end($tmpArray);

                    if($field == $refField)
                    {
                        //ref的字段就不需要了
                        unset($tmpFields[$k]);
                        continue; 
                    }

                    if(in_array($field, $allFields))
                    {
                        //字段冲突了，前面加表名
                        $field = $tmpObj->tableName."_".$field; 
                        $tmpFields[$k] .= " as ".$field;
                    }
                    $allFields[] = $field;
                }
                $ret[] = array(
                    "tableName"=>$tmpObj->tableName,
                    "fields"=>$tmpFields,
                    "ref"=>$this->tableName.".".$key." = ".$tmpObj->tableName.".".$refField,
                );
            }
        }
        return $ret;
    }

    /**
     * @desc 构建join查询的sql语句
     * @param array $fields 字段
     * @param array $cond 条件
     * @return string
     */
    private function buildSelectJoinSql($type, $fields, $cond, $limitOne=true)
    {
        $sql = "select ";
        $sep = "";
        $leftJoin = "";
        $flag = true;
        foreach($fields as $item)
        {
            if(self::CURD_SELECT_TYPE_QUERY == $type && $item['fields'])
            {
                $sql .= $sep . implode(", ", $item['fields']); 
                $sep = ", ";
            }

            //有设置ref就加上left join的
            if(!empty($item['ref']))
            {
                //fei 2014-05-09 看来left join和inner join是有很大区别的
                $leftJoin .= " left join ".$item['tableName']." on ".$item['ref']; 
            }
        }
        if(self::CURD_SELECT_TYPE_COUNT == $type)
        {
            $sql .= "count(1) as rowCount ";
            $limitOne = false;
        }
        $sql .= " from ".$this->tableName." ".$leftJoin;
        $sep = "";
        foreach($cond as $key=>$value)
        {
            //有设置这个字段才加上去的
            if(isset($this->tableConfig['fields'][$key]))
            {
                //假如参数为空,则忽略
                if("" == $value)
                {
                    continue; 
                }
                if($flag)
                {
                    $sql .= " where ";
                    $flag = false;
                }
                $cmpType = isset($this->tableConfig['fields'][$key]['cmpType'])?$this->tableConfig['fields'][$key]['cmpType']:self::FIELD_CMP_TYPE_EQUAL;
                $type = isset($this->tableConfig['fields'][$key]['type'])?$this->tableConfig['fields'][$key]['type']:self::FIELD_TYPE_INT;

                //判断字段比较的类型是精确匹配还是模糊匹配,只有varchar支持like的，其他类型只能精确匹配
                if(self::FIELD_CMP_TYPE_LIKE == $cmpType && self::FIELD_TYPE_VARCHAR == $type)
                {
                    $sql .= $sep . $this->tableName.".".$key." like '%".trim($value)."%'";
                }
                else if(self::FIELD_CMP_TYPE_MATCH_PREFIX_LIKE == $cmpType && self::FIELD_TYPE_VARCHAR == $type)
                {
                    $sql .= $sep . $this->tableName.".".$key." like '".trim($value)."%'";
                }
                else if(self::FIELD_CMP_TYPE_MATCH_SUFFIX_LIKE == $cmpType && self::FIELD_TYPE_VARCHAR == $type)
                {
                    $sql .= $sep . $this->tableName.".".$key." like '%".trim($value)."'";
                }
                //fei 2014-05-05
                else if(self::FIELD_CMP_TYPE_GT == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." > '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_GE == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." >= '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_LT == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." < '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_LE == $cmpType && self::FIELD_TYPE_INT == $type)
                {
                    $sql .= $sep . $key." <= '".$value."'";
                }
                else if(self::FIELD_CMP_TYPE_RAW == $cmpType)
                {
                    $sql .= $sep . $key." ".$value." ";
                }
                else if(self::FIELD_CMP_TYPE_IN == $cmpType)
                {
                    $sql .= $sep . $key." in (".$value.") ";
                }
                else
                {
                    $sql .= $sep . $this->tableName.".".$key."='".$value."'";
                }
                $sep = " and ";
            }
            //支持跨表
            else if(false !== strpos($key, "_dot_"))
            {
                if(empty($sep))
                {
                    $sep = " and "; 
                }
                $tmpKey = str_replace("_dot_", ".", $key);
                $sql .= $sep . $tmpKey."='".$value."'";
                $sep = " and ";
            }
        }

        if(is_array($limitOne))
        {
            $sql .= " order by ".$limitOne['orderBy']." ".$limitOne['sortType']." limit ".$limitOne['start'].", ".$limitOne['limit'];
        }
        else if(is_bool($limitOne) && $limitOne)
        {
            $sql .= " limit 1"; 
        }
        //echo $sql."<br/>";
        return $sql;
    }

    /**
     * @desc 格式化数据
    private function getComboList(&$comboList, $list, $parentId, $prefix, $idKey, $codeKey, $nameKey)
    {
        if ($parentId <= 0)
        {
            $comboList[] = array(
                    $idKey=>0,
                    $codeKey=>"",
                    $nameKey=>"顶级权限",
                    );
        }

        if(empty($list))
        {
            return "";
        }

        foreach($list as $per)
        {
            if($per['parentid'] == $parentId)
            {
                $comboList[] = array(
                        $idKey=>$per[$idKey],
                        $codeKey=>$per[$codeKey],
                        $nameKey=>$perfix . $per[$nameKey],
                        );

                $this->getComboList($comboList, $list, $per[$idKey], "perfix" . "┣", $idKey, $codeKey, $nameKey);
            }
        }
    }
     */

    /**
     * @desc 判断记录是否存在
     * @param $param 查询条件参数
     * @return bool
     */
    public function checkExist($param)
    {
        $condStr = "";
        $sep = "";
        foreach($param as $key => $value)
        {
            $condStr .= $sep . $key."='".$value."'"; 
            $sep = " and ";
        }
        $sql = "select 1 as num from ".$this->tableName." where ".$condStr." limit 1";
        $result = $this->query($sql);
        if(empty($result))
        {
            return false; 
        }
        else
        {
            return true;
        }
    }
}


