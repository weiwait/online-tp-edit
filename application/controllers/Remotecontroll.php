<?php
use base\ServiceFactory;
use utils\Result;

include_once "MCommonController.php";

class RemoteControllController extends MCommonController
{

    /**
     * @desc 关闭电器
     */
    public function stopAction()
    {
        $controllData = array(
            "c" => "C397E000A000200000000005FF"
        );
        $this->machineControll($controllData, 'ir');
    }

    /**
     * @desc 启动电器
     */
    public function startAction()
    {
        $controllData = array(
            "c" => "C397E000A0002000002000051F"
        );
        $this->machineControll($controllData, 'ir');
    }

    /**
     * @return null 默认值
     */
    public function getControlData()
    {
        return null;
    }

}

