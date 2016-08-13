<?php
/**
 * @desc 推送信息 screen运行，内部是一个死循环
 */
use base\ServiceFactory;
use base\DaoFactory;

include "../Loader.php";

ServiceFactory::getService("HumidifierOrder")->checkByCrontab();
