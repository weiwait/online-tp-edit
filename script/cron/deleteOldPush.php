<?php
/**
 * @desc 删除旧的push信息 crontab运行，1天运行一次
 */
use base\ServiceFactory;
use base\DaoFactory;

include "../Loader.php";

ServiceFactory::getService("PushMsg")->deleteOld();
