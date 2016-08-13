<?php
//mysql服务器定义
$db_shard_server = array(
    //"mysql001"=>array("master"=>"10.165.39.123:3306", "slave"=>"10.165.39.123:3306"),
    "mysql001"=>array("master"=>"rdsiw7z9hqi3rzubgw1vi.mysql.rds.aliyuncs.com:3306", "slave"=>"rdsiw7z9hqi3rzubgw1vi.mysql.rds.aliyuncs.com:3306"),
);

//定义分片规则，（垂直切分+水平切分,注意只做分库，不做分表,不做分表可以不修改任何sql语句）
$db_shard_config = array(
    "shard" => array(
        //"最小值-最大值"=>"数据库服务器id.存储的数据库名"
        "1-infinity"            =>"mysql001.test_tp_shard_001",    
    ),
);
