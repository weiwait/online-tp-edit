<?php
namespace utils;
use base\Logger;
class Result
{
    public static function output($output)
    {
        file_put_contents("/tmp/output.log", date("Y-m-d H:i:s")." ok:".json_encode($_REQUEST)."@@@".$output."\n", FILE_APPEND);
        echo $output; 
    }

    public static function showError($msg, $status="0", $dieFlag=true)
    {
        if(isset($_REQUEST['machineid']))
        {
            $ret = array(
                "status"=>$status,
                "machineid"=>$_REQUEST['machineid'],
                "data"=>$msg,
            );
        }
        else
        {
            $ret = array(
                "status"=>$status,
                "data"=>$msg,
            );
        }
        $ret = json_encode($ret);
        file_put_contents("/tmp/output.log", date("Y-m-d H:i:s")." error:".json_encode($_REQUEST)."@@@".$ret."\n", FILE_APPEND);
        echo $ret;
        if($dieFlag)
        {
            die;
        }
    }

    public static function showOk($msg, $status="1", $dieFlag=true)
    {
        global $globalTpAppid, $globalTpMachineid;
        if(isset($_REQUEST['machineid']) && false === strpos($_REQUEST['machineid'], ","))
        {
            $ret = array(
                "status"=>$status,
                "machineid"=>$_REQUEST['machineid'],
                "data"=>$msg,
            );
        }
        else
        {
            $ret = array(
                "status"=>$status,
                "data"=>$msg,
            );
        }

        if(!empty($globalTpMachineid))
        {
            $ret['tpMachineid'] = $globalTpMachineid; 
        }
        if(!empty($globalTpAppid))
        {
            $ret['tpAppid'] = $globalTpAppid; 
        }


        $ret = json_encode($ret);
        file_put_contents("/tmp/output.log", date("Y-m-d H:i:s")." ok:".json_encode($_REQUEST)."@@@".$ret."\n", FILE_APPEND);
        echo $ret;
        if($dieFlag)
        {
            die;
        }
    }
}
