<?php
/**
 * @desc 生成城市的信息 crontab运行，1个小时运行一次
 */
use base\ServiceFactory;
use base\DaoFactory;

include "../Loader.php";

$dir = "/home/webserver/tp.com/tp/conf";

$tmpFile = $dir."/cityInfo_tmp.php";
$finalFile = $dir."/cityInfo.php";

$sql = "select * from city where 1=1 order by city_id asc";
$data = DaoFactory::getDao("Main")->query($sql);

$content = "<?php\n\$cityInfo=>array(\n";

$i = 0;
foreach($data as $item)
{
    $content .= "    [".$i."]=>array('cityName'=>'".$item['city_name']."', 'tempTop'=>'".$item['temp_top']."', 'tempBottom'=>'".$item['temp_bottom']."', 'humidityBottom'=>'".$item['humidity_bottom']."', 'humidityTop'=>'".$item['humidity_top']."'),\n";
    ++$i;
}

$content .= ");";

file_put_contents($tmpFile, $content);
copy($tmpFile, $finalFile);
