<?php
class LayoutPlugin extends Yaf_Plugin_Abstract 
{

    private $_layoutDir;
    private $_layoutFile;
    private $_layoutVars =array('meta_title' => '');
    /**
     * 初始化
     */
    public function __construct($layoutFile, $layoutDir=null)
    {
        $this->_layoutFile = $layoutFile;
        $this->_layoutDir  = !empty($layoutDir) ? $layoutDir : APP_PATH.'/application/views/';
    }
	public function setLayout($layoutFile = '')
	{
		$this->_layoutFile = $layoutFile;
		return true;
	}
    /**
     * 魔术方法__set
     */
    public function  __set($name, $value) 
    {
        $this->_layoutVars[$name] = $value;
    }

    public function getExtCss()
    {
        return $this->_layoutVars['ext_css'];
    }

    public function getReloader()
    {
        return $this->_layoutVars['reloader'];
    }

    public function getAdvStyle()
    {
        return $this->_layoutVars['adv_style'];
    }

    public function getDebugDispTimer()
    {
        return $this->_layoutVars['debug_disp_timer'];
    }

    public function dispatchLoopShutdown ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response )
    {
        // pass
    }

    public function dispatchLoopStartup ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response )
    {
        // pass
    }
    /**
     * 在postDispatch事件的时候触发
     */
    public function postDispatch ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response )
    {
        /* get the body of the response */
		$body = $response->getBody();
		if (!empty($this->_layoutFile))
		{
			/*clear existing response*/
			$response->clearBody();
			/* wrap it in the layout */
			$layout = new Yaf_View_Simple($this->_layoutDir);
			$layout->content = $body;
			$layout->request = $request;
			$layout->assign('layout', $this->_layoutVars);
			/* set the response to use the wrapped version of the content */
			$response->setBody($layout->render($this->_layoutFile));
		}
	}

    public function preDispatch ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response )
    {
 
    }

    public function preResponse ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response )
    {
        
    }

    public function routerShutdown ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response )
    {

    }

    public function routerStartup ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response )
    {

    }
}
