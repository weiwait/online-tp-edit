<?php
namespace base;
class I18n
{
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
     * set user's locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
        $this->_initDict(true);
        return true;
    }
    /**
     * get user's locale
     */
    public function getLocale()
    {
        // 尝试从session里面获取当前的locale设置
        //TODO 尝试从浏览器ua里面获取需要使用的locale
        return empty($this->_locale) ? $this->_fallback : $this->_locale;
    }
    /**
     * 将文字转换成多国语言版本
     *
     * 如果在字典里面找不到,则直接返回原内容
     *
     * @param  string $msg   required  - 文字原内容
     * @param  array  $vars  optional  - 文字替换的变量
     *
     * @return string 翻译后的字符串
     *
     */
    public function trans($msg, $vars = array())
    {
        $this->_initDict();
        $str = isset($this->_dict[$msg]) ? $this->_dict[$msg] : $msg;
        if (empty($str))
        {
            return $str;
        }
        if (!empty($vars))
        {
            $_search = array_keys($vars);
            $replace = array_values($vars);
            $search  = array();
            foreach($_search as $s)
            {
                $search[] = "%" . $s . "%";
            }
            return str_replace($search, $replace, $str);
        }
        return $str;
    }

    private static $_obj;
    /**
     * 类的初始化
     *
     * @param file $dict require - 语言字典路径
     * @return object
     */
    public function __construct($fallback = 'en_US')
    {
        if (!in_array($fallback, $this->_support))
        {
            throw new \Yaf_Exception_StartupError("utils\I18n: invalid language " . $fallback);
        }
        $this->_fallback = $fallback;
    }
    /**
     * 初始化加载语言包
     */
    private function _initDict($force = false)
    {
        if ($force || !$this->_isInited)
        {
            $lang  = empty($this->_locale) ? $this->_fallback : $this->_locale;
            $_dictFile       = APP_PATH . "/conf/i18n/" . $lang . ".ini";
            $this->_dict     = new \Yaf_Config_Ini($_dictFile, $lang);
            $this->_isInited = true;
        }
        return true;
    }
    // 是否已经初始化
    private $_isInited = false;
    // 目前支持的语言
    private $_support  = array('zh_CN', 'en_US');
    // 当前语言版本
    private $_locale   = '';
    // 默认语言版本
    private $_fallback = 'en_US';
    // 语言字典
    private $_dict     = '';
}
