<?php
use base\ServiceFactory;
use base\DaoFactory;
use utils\Common;
use utils\Result;

class RouterController extends FrontController {
	/**
	 * 初始化
	 */
	public function init() 
    {
        //check_admin();
		parent::init ();
	}

    /**
     * @desc 
     */
    public function updatelistAction()
    {
        global $globalTpAppid, $globalTpMachineid;

        $machineid = trim($_REQUEST['machineid']); 
        if(empty($machineid))
        {
            Result::showError("machineid is empty");
        }
        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineid);
        if(empty($tpMachineid))
        {
            Result::showError("machineid ".$machineid." have not reg");
        }

        $list = trim($_REQUEST['list']); 
        ServiceFactory::getService("Machine")->active($tpMachineid);

        $arr = explode(",", $list);
        foreach($arr as $item)
        {
            $tmpTpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($item);
            ServiceFactory::getService("Machine")->updateRouter($tmpTpMachineid, $tpMachineid);
        }

        Result::showOk("ok");
    }

    public function clearlistAction()
    {
        global $globalTpAppid, $globalTpMachineid;

        $machineid = trim($_REQUEST['machineid']); 
        if(empty($machineid))
        {
            Result::showError("machineid is empty");
        }
        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineid);
        if(empty($tpMachineid))
        {
            Result::showError("machineid ".$machineid." have not reg");
        }

        $list = trim($_REQUEST['list']); 
        ServiceFactory::getService("Machine")->active($tpMachineid);

        $arr = explode(",", $list);
        foreach($arr as $item)
        {
            $tmpTpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($item);
            ServiceFactory::getService("Machine")->clearRouter($tmpTpMachineid, $tpMachineid);
        }

        Result::showOk("ok");
    }
}
