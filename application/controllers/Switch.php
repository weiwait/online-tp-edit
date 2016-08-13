<?php
use base\ServiceFactory;
use base\DaoFactory;
use utils\Common;
use utils\Result;

class SwitchController extends FrontController {
	/**
	 * 初始化
	 */
	public function init() 
    {
        //check_admin();
		parent::init ();
	}

    

    /**
     * @desc 建立开关和灯的绑定关系
     */
    public function bindSwitchAction()
    {
		\Yaf_Dispatcher::getInstance()->disableView();
		
		$appid = $_REQUEST['appid']; 
        $routerid = trim($_REQUEST['routerid']); 
		$switchid = $_REQUEST['switchid'];
		$lightid = $_REQUEST['lightid'];
		
		$tpRouterid = ServiceFactory::getService("Machine")->getTpMachineid($routerid);
		$tpSwitchid = ServiceFactory::getService("Machine")->getTpMachineid($switchid);
		$tpLightid = ServiceFactory::getService("Machine")->getTpMachineid($lightid);
        
		if(empty($routerid))
        {
            Result::showError("routerid is empty");
        }
        if(empty($switchid))
        {
            Result::showError("switchid is empty");
        }
		if(empty($lightid))
        {
            Result::showError("lightid is empty");
        }
		
		
		if(empty($tpRouterid))
        {
            Result::showError("routerid ".$routerid." have not reg");
        }
        if(empty($tpSwitchid))
        {
            Result::showError("switchid ".$switchid." have not reg"); 
        }
		if(empty($tpLightid))
        {
            Result::showError("lightid ".$lightid." have not reg"); 
        }
		
		
        
        $tpAppid = 0;
        if($appid != "00000000-0000-0000-0000-000000000000")
        {
            $tpAppid = ServiceFactory::getService("App")->getTpAppid($appid);
            if(empty($tpAppid))
            {
                Result::showError("appid ".$appid." have not reg");
            }

            $flag = ServiceFactory::getService("App")->isBind($tpAppid, $tpRouterid);
            if(!$flag)
            {
                Result::showError("appid ".$appid." have not bind routerid ".$routerid."");
            }

            ServiceFactory::getService("App")->active($tpAppid);
            
        }
		
   		$flag = ServiceFactory::getService("Switchs")->bindSwitch($tpSwitchid, $tpLightid);
		
		
        
        if($flag)
        {   
            //tcp即时通讯开始
            $mymachine[$machineid] = array(
                "run"=>"1",
                "l"=>$lightness,
                "t"=>$temperature,
                "r"=>$red,
                "g"=>$green,
                "b"=>$blue,
                "h"=>"1"
                );
            $call = array(
                "url"=>'/light/start',
                "status"=>"1",
                "machineid"=>$machineid,
                "data"=>$mymachine
                );
            $call = json_encode($call);
            $call = str_replace("\\", '', $call);
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            $result = socket_connect($socket, '127.0.0.1', 1234);
            $in = $machineid."::";
            $in .= "HTTP/1.1 200 OK\r\n";
            $in .= "Date:".date("D, d M Y H:i:s T",time())."\r\n";
            $in .= "Content-Length:".strlen($call)."\r\n\r\n";
            $in .= $call;
            socket_write($socket, $in, strlen($in));
            socket_close($socket);
            //tcp即时通讯结束
			
			$ret = array(
				"status"=>1,
				"data"=>"ok",
				"switchid"=>$switchid,
				"lightid"=>$lightid,
			);
			echo json_encode($ret);
			//Result::output($ret);
            //Result::showOk("ok");
        }
        else
        {
            Result::showError("system error");    
        }
    }
	
	
	/**
     * @desc 获取开关同一路由器下所有的灯
     */
    public function getLightListAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        $appid = $_REQUEST['appid'];
        $machineid = $_REQUEST['machineid'];
        $page = trim($_REQUEST['page']);
        $pageSize = trim($_REQUEST['pagesize']);

        $page = intval($page);
        $pageSize = intval($pageSize);
        if($page < 1)
        {
            $page = 1;
        }
        if($pageSize < 1)
        {
            $pageSize = 10;
        }

        $total = 0;

        if(empty($appid))
        {
            Result::showError("appid is empty");
        }
        if(empty($machineid))
        {
            Result::showError("machineid is empty");
        }

        $tpAppid = ServiceFactory::getService("App")->getTpAppid($appid);
        if(empty($tpAppid))
        {
            Result::showError("appid ".$appid." have not reg"); 
        }
        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineid);
        if(empty($tpMachineid))
        {
            Result::showError("machineid ".$machineid." have not reg"); 
        }

        $flag = ServiceFactory::getService("App")->isBind($tpAppid, $tpMachineid);
        if(!$flag)
        {
            Result::showError("appid ".$appid." have bind machineid ".$machineid."");
        }

        ServiceFactory::getService("App")->active($tpAppid);

        $total = ServiceFactory::getService("LightOrder")->getOrderNum($tpMachineid, $tpAppid);
        if(empty($total))
        {
            $ret = array(
                "status"=>1,
                "page"=>$page,
                "pagesize"=>$pageSize,
                "total"=>0,
                "data"=>array(),
            );
            $ret = json_encode($ret);
            Result::output($ret);
            die;
        }

        $allPage = ceil($total/$pageSize);
        if($page > $allPage)
        {
            $page = $allPage;
        }

        $offset = ($page - 1)*$pageSize;
        $limit = $pageSize;
   
        $data = ServiceFactory::getService("LightOrder")->getOrderList($tpMachineid, $tpAppid, $offset, $limit);
        if($data)
        {
            $ret = array(
                "status"=>1,
                "page"=>$page,
                "pagesize"=>$pageSize,
                "total"=>$total,
                "data"=>$data,
            );
            $ret = json_encode($ret);
            Result::output($ret);
            die;
        }
        else
        {
            Result::showError("system error"); 
        }
    }
	

    
	
	

}

