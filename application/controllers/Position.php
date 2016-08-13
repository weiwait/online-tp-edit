<?php

use base\DaoFactory;

require_once "MCommonController.php";

class PositionController extends MCommonController
{


	/**
	* desc 实现父类抽象方法
	*/	
	public function getControlData()
	{
		return NALL;
	}

	public function requestPositionAction($appid = '', $selfAppid = '', $title = 'REQUESTP')
	{
		parent::disableView();
		$appid = parent::getAppid();
		$tpAppid = parent::getTpAppid();
		$content = $_GET['selfAppid'];
		self::addMessage($tpAppid, $appid, $title, $content);
	}

	public function responsePositionAction($appid, $title = 'RESPONSEP', $selfAppid, $content)
	{
		parent::checkAppid();
		$tpAppid = parent::getTpAppid($appid);
		PushMsg::addMessage($type, $tpAppid, $appid, $title, $content, $tpMachineid);
	}

	protected function addMessage($tpAppid, $appid, $title, $content)
	{
		$sql = "INSERT INTO `weiwait` (`tp_appid`, `appid`, `title`, `content`, createtime) VALUES ($tpAppid, '$appid', '$title','$content'," . time() . ")";
		DaoFactory::getDao("Main")->query($sql);
	}
}
