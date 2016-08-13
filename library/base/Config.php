<?php
namespace base;
class Config extends Object
{
	/**
	 * 加载配置文件
	 * @param string $file 配置文件
	 * @param string $key  要获取的配置荐
	 * @param string $default  默认配置。当获取配置项目失败时该值发生作用。
	 * @param boolean $reload 强制重新加载。
	 */
	public static function load($file, $key = '', $default = '', $reload = false) {
		static $configs = array();
		if(!empty($key)){
			$arrKey = explode(".",$key);
		}
		if (!$reload && isset($configs[$file])) {
			if (empty($key)) {
				return $configs[$file];
			} else{
				$config = $configs[$file];
	            foreach ($arrKey as $the_key) {
	                if (!isset($config[$the_key])){
	                    return $default;
	                }
	                $config = $config[$the_key];
	            }
				return $config;
			}
		}
		$path = APP_PATH.'/conf/'.$file.'.inc.php';
		if (file_exists($path)) {
			$configs[$file] = include $path;
		}else{
			$this->log("file_no_exists:".$path);
		}
		if (empty($key)) {
			return $configs[$file];
		} else {
			$config = $configs[$file];
            foreach ($arrKey as $the_key) {
                if (!isset($config[$the_key])){
                    return $default;
                }
                $config = $config[$the_key];
            }
			return $config;
		}
	}
	/*
	 * 加载mc_config文件
	 */
	public static function load_mc($key = '', $default = '', $reload = false) {
		static $mc_config = array();
		if($reload || empty($mc_config)){
			$mc_config = include(\Yaf_Registry::get('config')->mc_config);
		}
		if (!empty($mc_config)) {
			if (empty($key)) {
				return $mc_config;
			} elseif (isset($mc_config[$key])) {
				return $mc_config[$key];
			} else {
				return $default;
			}
		}
	}
	/*
	 * 加载mc_config文件
	 */
	public static function load_db_shard_config($key = '', $default = '', $reload = false) {
		static $db_shard_config = array();
		if($reload || empty($db_shard_config)){
			include(\Yaf_Registry::get('config')->db_shard_config);
		}
		if (!empty($db_shard_config)) {
			if (empty($key)) {
				return $db_shard_config;
			} elseif (isset($db_shard_config[$key])) {
				return $db_shard_config[$key];
			} else {
				return $default;
			}
		}
	}
}
