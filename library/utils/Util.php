<?php
namespace utils;
class Util {

	/**
 	*函数作用：随机字串
 	*函数名称：randString()
 	*返 回 值：$string
 	*作	  者：michael
 	*创建日期：2006-1-3
 	*/
	public static function randString($len, $scope= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890") {
		srand((double) microtime() * 1000000);
		$str_len= strlen($scope) - 1;
		$string= '';
		for ($i= 0; $i < $len; $i ++) {
			$string .= substr($scope, rand(0, $str_len), 1);
		}
		return $string;
	}
    /**
     * 用addslashes处理变量,可处理多维数组
     * @isForce 强制转换
     */
    public static function addQuotes($vars,$isForce = false) {
        if(get_magic_quotes_gpc() && $isForce == false){
    		return $vars;
    	}
    	if (is_array($vars)) {
    		foreach ($vars as $k => $v) {
    			if (is_array($v)) {
    				foreach ($v as $k1 => $v1) {
    					$vars[$k][$k1] = self::addQuotes($v1);
    				}
    			} else {
    				$vars[$k] = addslashes($v);
    			}
    		}
    	} else {
    		$vars = addslashes($vars);
    	}
    	return $vars;
    }
    
    /**
     * 对指定变量进行stripslashes处理,可处理多维数组
     */
    public static function stripQuotes($vars) {
    	if (!count($vars))
    		return '';
    	if (is_array($vars)) {
    		foreach ($vars as $k => $v) {
    			if (is_array($v)) {
    				foreach ($v as $k1 => $v1) {
    					$vars[$k][$k1] = self::stripQuotes($v1);
    				}
    			} else {
    				$vars[$k] = stripslashes($v);
    			}
    		}
    	} else {
    		$vars = stripslashes($vars);
    	}
    	return $vars;
    }
    
    /**
     * 用trim处理变量,可处理多维数组
     */
    public static function trimArr($vars) {
    	if (!count($vars))
    		return '';
    	if (is_array($vars)) {
    		foreach ($vars as $k => $v) {
    			if (is_array($v)) {
    				foreach ($v as $k1 => $v1) {
    					$vars[$k][$k1] = self::trimArr($v1);
    				}
    			} else {
    				$vars[$k] = trim($v);
    			}
    		}
    	} else {
    		$vars = trim($vars);
    	}
    	return $vars;
    }
    
    

}
