<?php
namespace base;
class Resource
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
     * set path of require dir
     */
    public function setRequirePath($path)
    {
        $this->_require_path = $path;
        return true;
    }
    /**
     * get path of require dir
     */
    public function getRequirePath()
    {
        return $this->_require_path;
    }

    /**
     * set path of preview dir
     */
    public function setPreviewPath($path)
    {
        $this->_preview_path = $path;
        return true;
    }
    /**
     * get path of preview dir
     */
    public function getPreviewPath()
    {
        return $this->_preview_path;
    }

    public function setPreviewState($lang)
    {
        $this->_state = 1;
        $i18n = \Yaf_Registry::get('i18n');
        $i18n->setLocale($lang);
    }

    public function checkPreviewState()
    {
        return $this->_state == 1;
    }

    public function getDict()
    {
        return $this->_dict;
    }

    public function setDict($dict)
    {
        $this->_dict = $dict;
    }

    // 单例
    private static $_obj;
    // path of require dir
    private $_require_path     = '';
    // path of preview dir
    private $_preview_path     = '';
    // dictionary
    private $_dict             = null;

    /**
     * state of preview
     * 0: normal
     * 1: preview
     */
    private $_state            = 0;
}
