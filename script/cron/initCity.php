<?php
use base\ServiceFactory;
use base\DaoFactory;

include "../Loader.php";

$content = trim(file_get_contents("city.txt"));

$arr = explode("\n", $content);
foreach($arr as $item)
{
    $item = trim($item);
    if(empty($item))
    {
        continue; 
    }
    $arr1 = explode("=", $item);
    if(2 != count($arr1))
    {
        echo "skip ".$item."\n";
        continue; 
    }
    $cityId = $arr1[0];
    $cityName = $arr1[1];
    //echo $cityId."-".$cityName."\n";
    $sql = "select 1 from city where city_name='".mysql_escape_string($cityName)."' limit 1";
    $data = DaoFactory::getDao("Main")->query($sql);
    if(empty($data))
    {
        $sql = "insert into city (city_id, city_name) values('".$cityId."', '".$cityName."')"; 
        DaoFactory::getDao("Main")->query($sql);
    }
    else
    {
        echo "exists ".$item."\n";
    }
}


$sql = "";



