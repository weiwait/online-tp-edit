<?php
/**
 * @desc 更新城市的信息, crontab运行 2个小时运行一次
 */
use base\ServiceFactory;
use base\DaoFactory;
include "../Loader.php";

ServiceFactory::getService("Humidifier")->updateCityId();
