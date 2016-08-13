<?php
use base\ServiceFactory;
use base\DaoFactory;
use utils\Common;
use utils\Result;

include_once "MCommonController.php";

class LightController extends MCommonController
{

    /**
     * 初始化
     */
    public function init()
    {
        //check_admin();
        parent::init();
    }

    /**
     * @desc 保存配置
     */
    public function saveconfigAction()
    {
        $enableNightMode = trim($_REQUEST['enablenightmode']);
        $enableNightMode = $enableNightMode ? 1 : 0;
        $config = array(
            "enable_night_mode" => $enableNightMode
        );
        $this->saveConfig($config);
    }

    /**
     * @desc 获取配置
     */
    public function getconfigAction()
    {
        global $globalTpAppid, $globalTpMachineid;
        $machineid = trim($_REQUEST['machineid']);
        if (empty($machineid)) {
            Result::showError("machineid is empty");
        }
        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineid);
        if (empty($tpMachineid)) {
            Result::showError("machineid " . $machineid . " have not reg");
        }

        $appid = trim($_REQUEST['appid']);
        $tpAppid = ServiceFactory::getService("App")->getTpAppid($appid);
        if (empty($tpAppid)) {
            Result::showError("appid " . $appid . " have not reg");
        }

        $flag = ServiceFactory::getService("App")->isBind($tpAppid, $tpMachineid);
        if (!$flag) {
            Result::showError("appid " . $appid . " have not bind machineid " . $machineid . "");
        }
        $globalTpMachineid = $tpMachineid;
        $globalTpAppid = $tpAppid;

        ServiceFactory::getService("App")->active($tpAppid);

        $data = ServiceFactory::getService("Light")->getConfig($tpMachineid);
        if ($data) {
            //在这里重新获取app的智能配置
            $startStopDetail = ServiceFactory::getService("App")->getMachineConfig($tpAppid, $tpMachineid);
            //$startFlag = $startStopDetail['enable_user_near_start'];
            //$stopFlag = $startStopDetail['enable_user_far_stop'];
            //$nightMode = $startStopDetail['enable_night_mode'];
            $data['enableusernearstart'] = $startStopDetail['enable_user_near_start'];
            $data['enableuserfarstop'] = $startStopDetail['enable_user_far_stop'];
            $data['enablenightmode'] = $startStopDetail['enable_night_mode'];
            $data['startAndStopRemind'] = $startStopDetail['start_stop_remind'];
            //$data['toodryremind'] = $startStopDetail['tooDryRemind'];

            Result::showOk($data);
        } else {
            Result::showError("system error");
        }
    }

    /**
     * @desc 电器来获取是否需要开启
     */
    public function requestAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        $machineid = trim($_REQUEST['machineid']);
        if (empty($machineid)) {
            Result::showError("machineid is empty");
        }

        $arr = explode(",", $machineid);
        $retArray = array();
        foreach ($arr as $item) {
            $detail = "";
            $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($item);
            if (!empty($tpMachineid)) {
                ServiceFactory::getService("Machine")->active($tpMachineid);

                $detail = ServiceFactory::getService("Light")->getWork($tpMachineid);
            }
            $retArray[$item] = $detail;
        }

        Result::showOk($retArray);
    }

    /**
     * @desc 电器来获取是否需要开启
     */
    public function requestcallbackAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        $machineid = trim($_REQUEST['machineid']);
        if (empty($machineid)) {
            Result::showError("machineid is empty");
        }

        $arr = explode(",", $machineid);
        foreach ($arr as $item) {
            $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($item);
            if (!empty($tpMachineid)) {
                ServiceFactory::getService("Machine")->active($tpMachineid);

                ServiceFactory::getService("Light")->requestCallback($tpMachineid);
            }
        }

        Result::showOk("ok");
    }

    /**
     * @desc 关闭加湿器
     */
    public function stopAction()
    {
        $this->stopMachine();
    }

    /**
     * @desc 启动加湿器
     */
    public function startAction()
    {
        $this->startMachine();
    }

    /**
     * @desc 获取使用使用记录列表
     */
    public function getactionloglistAction()
    {
        //这个是从电器维度的，是不知道appid的
        \Yaf_Dispatcher::getInstance()->disableView();
        $machineid = trim($_REQUEST['machineid']);
        $appid = trim($_REQUEST['appid']);
        $page = trim($_REQUEST['page']);
        $pagesize = trim($_REQUEST['pagesize']);

        $page = intval($page);
        if ($page < 1) {
            $page = 1;
        }
        if ($pagesize < 1) {
            $pagesize = 10;
        }

        if (empty($machineid)) {
            Result::showError("machineid is empty");
        }
        if (empty($appid)) {
            Result::showError("appid is empty");
        }
        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineid);
        if (empty($tpMachineid)) {
            Result::showError("machineid " . $machineid . " have not reg");
        }

        $tpAppid = ServiceFactory::getService("App")->getTpAppid($appid);
        if (empty($tpAppid)) {
            Result::showError("appid " . $appid . " have not reg");
        }

        $flag = ServiceFactory::getService("App")->isBind($tpAppid, $tpMachineid);
        if (!$flag) {
            Result::showError("appid " . $appid . " have not bind machineid " . $machineid . "");
        }

        ServiceFactory::getService("App")->active($tpAppid);

        $total = ServiceFactory::getService("Light")->getActionLogNum($tpMachineid);
        if (empty($total)) {
            $ret = array(
                "status" => "1",
                "total" => 0,
                "page" => $page,
                "pagesize" => $pagesize,
                "data" => array(),
            );
            $ret = json_encode($ret);
            Result::output($ret);
            die;
        }
        $allPage = ceil($total / $pagesize);
        if ($page > $allPage) {
            $page = $allPage;
        }
        $offset = ($page - 1) * $pagesize;
        $limit = $pagesize;

        $data = ServiceFactory::getService("Light")->getActionLogList($tpMachineid, $offset, $limit);
        if ($data) {
            $ret = array(
                "status" => 1,
                "total" => $total,
                "page" => $page,
                "pagesize" => $pagesize,
                "data" => $data,
            );
            $ret = json_encode($ret);
            Result::output($ret);
            die;
        } else {
            Result::showError("system error");
        }

    }

    /**
     * @desc 保存使用记录
     */
    public function actionlogAction()
    {
        //这个是从电器维度的，是不知道appid的
        \Yaf_Dispatcher::getInstance()->disableView();
        //Result::showOk("ok");
        //die;
        $appid = "";
        $machineid = trim($_REQUEST['machineid']);
        $appid = trim($_REQUEST['appid']);
        $operation = trim($_REQUEST['operation']);
        $starttime = trim($_REQUEST['starttime']);
        $endtime = trim($_REQUEST['endtime']);
        $startlevel = trim($_REQUEST['startlevel']);
        $endlevel = trim($_REQUEST['endlevel']);
        $tophumidity = trim($_REQUEST['tophumidity']);
        $middlehumidity = trim($_REQUEST['middlehumidity']);
        $bottomhumidity = trim($_REQUEST['bottomhumidity']);
        $starthumidity = trim($_REQUEST['starthumidity']);
        $endhumidity = trim($_REQUEST['endhumidity']);
        $energy = trim($_REQUEST['energy']);

        $energy = strtoupper($energy);
        if (false !== strpos($energy, "KW")) {
            $energy = str_replace("KW", "", $energy);
            $energy = floatval($energy);
            $energy = $energy * 1000;
            $energy = $energy . "W";
        }

        if (empty($machineid)) {
            Result::showError("machineid is empty");
        }
        //fei 这个不能严格要求的，切记
        /*
        if(empty($appid))
        {
            Result::showError("appid is empty");
        }
        */
        $operation = intval($operation);
        if (empty($starttime)) {
            Result::showError("starttime is empty");
        }
        if (empty($endtime)) {
            Result::showError("endtime is empty");
        }
        if (empty($startlevel)) {
            Result::showError("startlevel is empty");
        }
        if (empty($endlevel)) {
            Result::showError("endlevel is empty");
        }
        if (empty($starthumidity)) {
            Result::showError("starthumidity is empty");
        }
        if (empty($endhumidity)) {
            Result::showError("endhumidity is empty");
        }
        if (empty($tophumidity)) {
            Result::showError("tophumidity is empty");
        }
        if (empty($middlehumidity)) {
            Result::showError("middlehumidity is empty");
        }
        if (empty($bottomhumidity)) {
            Result::showError("bottomhumidity is empty");
        }

        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineid);
        if (empty($tpMachineid)) {
            Result::showError("machineid " . $machineid . " have not reg");
        }

        $tpAppid = 0;
        if (!empty($appid)) {
            $tpAppid = ServiceFactory::getService("App")->getTpAppid($appid);
            if (empty($tpAppid)) {
                Result::showError("appid " . $appid . " have not reg");
            }

            $flag = ServiceFactory::getService("App")->isBind($tpAppid, $tpMachineid);
            if (!$flag) {
                Result::showError("appid " . $appid . " have not bind machineid " . $machineid . "");
            }
        }

        ServiceFactory::getService("Machine")->active($tpMachineid);

        //actionLog($tpMachineid, $machineid, $tpAppid, $appid, $operation, $starttime, $costtime, $humidity, $energy)

        $realStartTime = 0;

        $costtime = $this->calcCostTime($starttime, $endtime, $realStartTime);
        $humidity = "" . intval($bottomhumidity) . "%~" . intval($tophumidity) . "%";

        if (empty($appid)) {
            $lastAppid = ServiceFactory::getService("Light")->getLastAppid($tpMachineid);
            if (strlen($lastAppid) == strlen("00000000-0000-0000-0000-000000000000") && "00000000-0000-0000-0000-000000000000" == $lastAppid) {
                $operation = 0;
            }
        }


        //添加使用记录
        $ret = ServiceFactory::getService("Light")->actionLog($tpMachineid, $machineid, $tpAppid, $appid, $operation, $realStartTime, $costtime, $humidity, $energy);
        if ($ret) {
            //ServiceFactory::getService("LightStat")->updateStat($tpMachineid);
            Result::showOk("ok");
        } else {
            Result::showError("system error");
        }
    }

    /**
     * @desc 更新状态
     */
    public function updatestateAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        $machineid = trim($_REQUEST['machineid']);
        $lightness = trim($_REQUEST['lightness']);
        $temperature = trim($_REQUEST['temperature']);
        $red = trim($_REQUEST['red']);
        $green = trim($_REQUEST['green']);
        $blue = trim($_REQUEST['blue']);
        $state = trim($_REQUEST['state']);

        $this->updateMachine();

        //更新状态
        $ret = ServiceFactory::getService("Light")->updateState($this->getTPMachineid(), $machineid, $this->getTPAppid(), $this->getAppid(), $lightness, $temperature, $red, $green, $blue, $state, trim($_REQUEST['energy']));
        if ($ret) {
            Result::showOk("ok");
        } else {
            Result::showError("system error");
        }
    }

    /**
     * @desc 获取加湿器状态
     */
    public function getstateAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        $machineid = trim($_REQUEST['machineid']);
        $appid = trim($_REQUEST['appid']);
        if (empty($machineid)) {
            Result::showError("machineid is empty");
        }
        if (empty($appid)) {
            Result::showError("appid is empty");
        }

        $tpMachineid = ServiceFactory::getService("Machine")->getTpMachineid($machineid);
        if (empty($tpMachineid)) {
            Result::showError("machineid " . $machineid . " have not reg");
        }
        $tpAppid = ServiceFactory::getService("App")->getTpAppid($appid);
        if (empty($tpAppid)) {
            Result::showError("appid " . $appid . " have not reg");
        }

        $flag = ServiceFactory::getService("App")->isBind($tpAppid, $tpMachineid);
        if (!$flag) {
            Result::showError("appid " . $appid . " have not bind machineid " . $machineid . "");
        }

        ServiceFactory::getService("App")->active($tpAppid);

        $data = ServiceFactory::getService("Light")->getState($tpMachineid);
        if ($data) {
            $ret = array(
                "status" => 1,
                "data" => $data,
            );
            $ret = json_encode($ret);
            Result::output($ret);
        } else {
            Result::showError("system error");
        }
    }

    /**
     * @desc 获取加湿器统计信息
     */
    public function statAction()
    {
        $this->getMachineStat();
    }

    /**
     * @desc 添加预约
     */
    public function orderAction()
    {
        $this->addOrder();
    }

    /**
     * @desc 删除预约
     */
    public function cancelorderAction()
    {
        $this->deleteOrder();
    }

    public function getControlData()
    {
        if (!trim($_REQUEST['lightness'])) {
            $lastControlData = $this->getService()->getState($this->getSingleTPMachineid());
            $lightness = $lastControlData['lightness'];
            $temperature = $lastControlData['temperature'];
            $red = $lastControlData['red'];
            $green = $lastControlData['green'];
            $blue = $lastControlData['blue'];
        } else {
            $lightness = trim($_REQUEST['lightness']);
            $temperature = trim($_REQUEST['temperature']);
            $red = trim($_REQUEST['red']);
            $green = trim($_REQUEST['green']);
            $blue = trim($_REQUEST['blue']);
        }
        return array(
            "l" => $lightness,
            "t" => $temperature,
            "r" => $red,
            "g" => $green,
            "b" => $blue
        );
    }

    /**
     * @desc 获取预约的详情
     */
    public function getorderAction()
    {
        $this->getOrder();
    }

    /**
     * @desc 获取预约列表
     */
    public function getorderlistAction()
    {
        $this->getOrderList();
    }
}

