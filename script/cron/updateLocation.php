<?php
use base\ServiceFactory;

include "../Loader.php";
echo date("Y-m-d H:i:s ");
//同步machine地图数据
ServiceFactory::getService("Machine")->syncMapData();

file_put_contents("test.log", "test", FILE_APPEND);

//更新坐标
ServiceFactory::getService("Machine")->updateLocation();

echo "\n";



