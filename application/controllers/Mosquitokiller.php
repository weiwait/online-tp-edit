<?php
use base\ServiceFactory;
use utils\Result;

include_once "MCommonController.php";

class MosquitoKillerController extends MCommonController
{

    /**
     * @desc 关闭电器
     */
    public function stopAction()
    {
        $this->stopMachine();
    }

    /**
     * @desc 启动电器
     */
    public function startAction()
    {
        $this->startMachine();
    }

    public function getControlData()
    {
        return null;
    }

    /**
     * @desc 保存配置
     */
    public function saveconfigAction()
    {
        $this->saveConfig();
    }

    /**
     * @desc 获取配置
     */
    public function getconfigAction()
    {
        $this->checkValidMachineAndApp();
        $data = ServiceFactory::getService("Mosquitokiller")->getConfig($this->getTPMachineid());
        if ($data) {
            $machineConfig = ServiceFactory::getService("App")->getMachineConfig($this->getTPAppid(), $this->getTPMachineid());
            $data['enableusernearstart'] = $machineConfig['enable_user_near_start'];
            $data['startAndStopRemind'] = $machineConfig['start_stop_remind'];
            Result::showOk($data);
        } else {
            Result::showError("system error");
        }
    }

    /**
     * @desc 发送使用记录
     */
    public function actionlogAction()
    {
        $this->checkValidMachineAndAppOther();
        $operation = trim($_REQUEST['operation']);
        if (empty($_REQUEST['appid'])) {
            $lastAppid = $this->getService()->getLastAppid($this->getTPMachineid());
            if (strlen($lastAppid) == strlen("00000000-0000-0000-0000-000000000000") && "00000000-0000-0000-0000-000000000000" == $lastAppid) {
                $operation = 0;
            }
        }
        $starttime = trim($_REQUEST['starttime']);
        $realStartTime = 0;
        $costtime = $this->calcCostTime($starttime, trim($_REQUEST['endtime']), $realStartTime);
        $ret = $this->getService()->actionLog($this->getTPMachineid(), $this->getMachineid(), $this->getTPAppid(), $this->getAppid(), $operation, $starttime, $costtime);
        if ($ret) {
            ServiceFactory::getService("MosquitoKillerStat")->updateStat($this->getTPMachineid());
        }
        $this->returnData($ret);
    }

    /**
     * @desc 获取使用记录列表
     */
    public function getactionloglistAction()
    {
        $this->getActionLogList();
    }

    /**
     * @desc 发送当前状态
     */
    public function updatestateAction()
    {
        $this->updateMachine();
        $ret = $this->getService()->updateState($this->getTPMachineid(), $this->getMachineid(), $this->getIntvalState(), $this->getTPAppid(), $this->getAppid());
        $this->returnData($ret);
    }

    /**
     * @desc 获取状态
     */
    public function getstateAction()
    {
        $this->checkValidMachineAndApp();
        $data = $this->getService()->getState($this->getTPMachineid());
        $this->returnJSON($data);
    }

    /**
     * @desc 获取加湿器统计信息
     */
    public function statAction()
    {
        $this->getMachineStat();
    }

    //    /**
    //     * @desc 电器获取是否需要开启
    //     */
    //    public function requestAction()
    //    {
    //        $this->request();
    //    }

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
        $data = $this->deleteOrder();
    }

    /**
     * @desc 获取预约列表
     */
    public function getorderlistAction()
    {
        $this->getOrderList();
    }

    /**
     * @desc 获取预约的详情
     */
    public function getorderAction()
    {
        $this->getOrder();
    }

}

