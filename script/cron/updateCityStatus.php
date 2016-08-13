<?php
/**
 * @desc 更新城市的信息, crontab运行 2个小时运行一次
 */
use base\ServiceFactory;
use base\DaoFactory;

include "../Loader.php";

//while(1)
if(1)
{
    header('Content-type:text/html;charset=utf-8');
    $time = time() - 7200;
    $sql = "select * from city where last_update_time < ".$time." order by last_update_time asc";
    $data = DaoFactory::getDao("Main")->query($sql);
    echo "num=".count($data)."\n";

 
    //配置您申请的appkey
    $appkey = "121f74ac37d8907995b7aee0193494f1";
     
    $url = "http://op.juhe.cn/onebox/weather/query";

    foreach($data as $item)
    {
        $cityName = $item['city_name'];
        $cityId = $item['city_id'];

        $params = array(
              "cityname" => $cityName,//要查询的城市，如：温州、上海、北京
              "key" => $appkey,//应用APPKEY(应用详细页查询)
              "dtype" => "",//返回数据的格式,xml或json，默认json
        );

        $paramstring = http_build_query($params);
        echo $url.$paramstring."\n";
        $content = juhecurl($url,$paramstring);
        $result = json_decode($content,true);
        if(empty($result)){
            echo "请求失败"."\n";
            $sql = "update city set last_update_time='".time()."' where city_id='".$cityId."' limit 1";
            DaoFactory::getDao("Main")->query($sql);
            continue; 
        }elseif($result['error_code']!='0'){
            echo $result['error_code'].":".$result['reason']."\n";
            $sql = "update city set last_update_time='".time()."' where city_id='".$cityId."' limit 1";
            DaoFactory::getDao("Main")->query($sql);
            continue; 
        }
        
        $sd = trim($result['result']['data']['realtime']['weather']['humidity']);
        $tempTop = $result['result']['data']['weather'][0]['info']['day'][2];
        $tempBottom = $result['result']['data']['weather'][0]['info']['night'][2];

        $sql = "update city set last_update_time='".time()."', humidity_top='".$sd."', humidity_bottom='".$sd."', temp_top='".$tempTop."', temp_bottom='".$tempBottom."' where city_id='".$cityId."' limit 1";
        echo $sql."\n";
        DaoFactory::getDao("Main")->query($sql);
        sleep(2); 
    }
}


 
/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function juhecurl($url,$params=false,$ispost=0){
    $httpInfo = array();
    $ch = curl_init();
 
    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}