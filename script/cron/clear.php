<?php
use base\ServiceFactory;
use base\DaoFactory;

include "../Loader.php";

deleteAppExt();

$sql = "select * from machine where 1=1";
$data = DaoFactory::getDao("Main")->query($sql);
foreach($data as $item)
{
    $tpMachineid = $item['id'];
    $machineid = $item['machineid'];

    $char2 = substr($machineid, 0, 2);
    if(in_array($char2, array("01", "02")))
    {
        continue; 
    }

    deleteBind($tpMachineid);
    deleteMachineDetail($tpMachineid);
    deleteTeapotActionLog($tpMachineid);
    deleteHumidifierActionLog($tpMachineid);
    deleteTeapotOrder($tpMachineid);
    deleteTeapotStat($tpMachineid);
    deleteTeapotState($tpMachineid);
    deleteHumidifierStat($tpMachineid);
    deleteHumidifierState($tpMachineid);
    deleteAppMsg($tpMachineid);
    deleteMachine($tpMachineid);
    deleteBindLog($tpMachineid);
}

$sql = "select * from app_detail where last_active_time < ".time()."- ".(14 * 86400)."";
DaoFactory::getDao("Shard")->branchDb("1");
$data = DaoFactory::getDao("Shard")->query($sql);
foreach($data as $item)
{
    $tpAppid = $item['tp_appid'];
    
    deleteBind2($tpAppid);
    deleteFeedback($tpAppid);
    deleteAppDetail($tpAppid);
    deleteAppMsg2($tpAppid);
    deleteAppPing($tpAppid);
    deleteBindLog2($tpAppid);
    deleteApp($tpAppid);

}

function deleteFeedback($tpAppid)
{
    $sql = "delete from bind where tp_appid='".$tpAppid."'";
    DaoFactory::getDao("Main")->query($sql);
}


function deleteBind($tpMachineid)
{
    $sql = "delete from bind where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Main")->query($sql);
}

function deleteApp($tpAppid)
{
    $sql = "delete from app where id='".$tpMachineid."'";
    DaoFactory::getDao("Main")->query($sql);
}

function deleteMachine($tpMachineid)
{
    $sql = "delete from machine where id='".$tpMachineid."'";
    DaoFactory::getDao("Main")->query($sql);
}

function deleteBind2($tpAppid)
{
    $sql = "delete from bind where tp_appid='".$tpAppid."'";
    DaoFactory::getDao("Main")->query($sql);
}

function deleteMachineDetail($tpMachineid)
{
    $sql = "delete from machine_detail where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteAppDetail($tpAppid)
{
    $sql = "delete from app_detail where tp_appid='".$tpAppid."'";
    DaoFactory::getDao("Shard")->branchDb($tpAppid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteTeapotActionLog($tpMachineid)
{
    $sql = "delete from teapot_action_log where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteHumidifierActionLog($tpMachineid)
{
    $sql = "delete from humidifier_action_log where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteTeapotOrder($tpMachineid)
{
    $sql = "delete from teapot_order where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteTeapotStat($tpMachineid)
{
    $sql = "delete from teapot_stat where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteTeapotState($tpMachineid)
{
    $sql = "delete from teapot_state where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteHumidifierStat($tpMachineid)
{
    $sql = "delete from humidifier_stat where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteHumidifierState($tpMachineid)
{
    $sql = "delete from humidifier_state where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteAppMsg($tpMachineid)
{
    $sql = "delete from app_msg where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteAppMsg2($tpAppid)
{
    $sql = "delete from app_msg where tp_appid='".$tpAppid."'";
    DaoFactory::getDao("Shard")->branchDb($tpAppid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteAppPing($tpAppid)
{
    $sql = "delete from app_ping where tp_appid='".$tpAppid."'";
    DaoFactory::getDao("Shard")->branchDb($tpAppid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteBindLog2($tpAppid)
{
    $sql = "delete from bind_log where tp_appid='".$tpAppid."'";
    DaoFactory::getDao("Shard")->branchDb($tpAppid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteBindLog($tpMachineid)
{
    $sql = "delete from bind_log where tp_machineid='".$tpMachineid."'";
    DaoFactory::getDao("Shard")->branchDb($tpMachineid);
    DaoFactory::getDao("Shard")->query($sql);
}

function deleteAppExt()
{
    $sql = "select * from app";
    $data = DaoFactory::getDao("Main")->query($sql);
    var_dump($data);
    foreach($data as $item)
    {
        $tpAppid = $item['id']; 
        echo "======".$tpAppid."=====\n";
        $sql = "select * from app_detail where tp_appid ='".$tpAppid."' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data1 = DaoFactory::getDao("Shard")->query($sql);
        if(empty($data1))
        {
            $sql = "delete from app where id='".$tpAppid."' limit 1"; 
            DaoFactory::getDao("Main")->query($sql);
        }
    }
}
