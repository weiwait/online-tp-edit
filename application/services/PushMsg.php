<?php
namespace services;

use base\Service;
use base\DaoFactory;
use base\ServiceFactory;
use utils\Tag;

class PushMsg extends Service
{
    public function __construct()
    {
    }

    public function add($type, $tpAppid, $appid, $title, $content, $tpMachineid)
    {
        $type = trim($type);

        if (!in_array($type, array(
            "teapot_start_remind",
            "teapot_end_remind",
            "humidifier_start_remind",
            "humidifier_end_remind",
            "humidifier_no_water_remind",
            "humidifier_too_dry_remind",
        ))
        ) {
            return false;
        }

        if (!$this->checkNeedSend($tpAppid, $tpMachineid, $type)) {
            return true;
        }


        $sql = "insert into push_msg(tp_appid, appid, title, content, createtime) values ('" . $tpAppid . "', '" . $appid . "', '" . $title . "', '" . $content . "', '" . time() . "')";
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function addMessage($type, $tpAppid, $appid, $title, $content, $tpMachineid)
    {
        $sql = "insert into push_msg(tp_appid, appid, title, content, createtime) values ('" . $tpAppid . "', '" . $appid . "', '" . $title . "', '" . $content . "', '" . time() . "')";
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function addSilentMessage($tpAppid, $appid)
    {
        $sql = "insert into push_msg(tp_appid, appid, createtime) values ('" . $tpAppid . "', '" . $appid . "', '" . time() . "')";
        return DaoFactory::getDao("Main")->query($sql);
    }

    /**
     * @desc 判断是否需要发送信息
     */
    public function checkNeedSend($tpAppid, $tpMachineid, $type)
    {
        $ret = false;
        $sql = "select tp_appid from app_machine_config where tp_appid='" . $tpAppid . "' and tp_machineid='" . $tpMachineid . "' and " . $type . "='1' limit 1";
        DaoFactory::getDao("Shard")->branchDb($tpAppid);
        $data = DaoFactory::getDao("Shard")->query($sql);
        if (empty($data)) {
            $ret = false;
        } else {
            $ret = true;
        }

        file_put_contents("/tmp/pushmsg", date("Y-m-d H:i:s") . " checkNeedSend tpAppid=" . $tpAppid . ", tpMachineid=" . $tpMachineid . ", type=" . $type . ", send=" . $ret . "\n", FILE_APPEND);

        return $ret;
    }

    public function addToOld($id, $tpAppid, $appid, $title, $content, $status)
    {
        $sql = "insert into push_msg_old(id, tp_appid, appid, title, content, createtime, status) values ('" . $id . "', '" . $tpAppid . "', '" . $appid . "', '" . $title . "', '" . $content . "', '" . time() . "', '" . $status . "')";
        return DaoFactory::getDao("Main")->query($sql);
    }

    public function deleteById($id)
    {
        $sql = "delete from push_msg where id='" . $id . "' limit 1";
        return DaoFactory::getDao("Main")->query($sql);
    }

    /**
     * @desc 只保留最近7天的日志
     */
    public function deleteOld()
    {
        $time = time() - 7 * 86400;
        $sql = "delete from push_msg_old where createtime <" . $time . "";
        return DaoFactory::getDao("Main")->query($sql);
    }

    /**
     * @desc 为了实时性估计这个要用screen来跑
     */
    public function push($debugMode = false)
    {
        require APP_PATH . "/library/umeng/Umeng.php";
        //while(1)
        if (1) {
            $sql = "select * from push_msg where 1=1 order by id asc limit 100";
            echo $sql . "\n";
            $data = DaoFactory::getDao("Main")->query($sql);
            foreach ($data as $item) {
                $id = $item['id'];
                $title = $item['title'];
                $content = $item['content'];
                $tpAppid = $item['tp_appid'];
                $appid = $item['appid'];
                $sound = true;
                $shock = true;

                $detail = ServiceFactory::getService("App")->getAppSettingSoundShock($tpAppid);
                $sound = $detail['sound'];
                $shock = $detail['shock'];

                //全小写，无-

                $ret = $this->send($appid, $title, $content, $sound, $shock);
                if ($ret) {
                    $status = 1;
                } else {
                    $status = 0;
                }

                $this->addToOld($id, $tpAppid, $appid, $title, $content, $status);
                $this->deleteById($id);

                if ($debugMode) {
                    echo "debugMode open\n";
                    return;
                }
            }
            sleep(1);
        }
    }

    public function testIOSSilent()
    {
        $Umeng = new \Umeng("54a8dbd3fd98c523050011c4", "gotzxontizwjo9rfruybnazxmrmonvaf");
        $Umeng->sendIOSCustomizedcast('7B83EC57-A11F-4F03-8104-0F283E7408FE', "", "", false, false, '1e0874633036ea39c31cd4da704d7f2223481691db844522d69eca141ba9bfcf');
        //, 'aceba02cc136e15f51165bb948a77c1ecb91c6aefe6855027ee29cfb70ce284e'
    }

    public function testAndroidSilent()
    {
        $Umeng = new \Umeng("54d97a8bfd98c52b06000086", "qp4vy8yiygqjd5q6xu6fb0yjjpuchdqw");
        $Umeng->sendAndroidCustomizedcast('1456154149865143', "", "", false, false, 'AoRmnNR4qB3bwwDuro4hrHyl7hBwePOy6S39DmMr6Sja');
        //, 'aceba02cc136e15f51165bb948a77c1ecb91c6aefe6855027ee29cfb70ce284e'
    }


    private function send($appid, $title, $content, $sound = true, $shock = true)
    {
        echo "into send\n";
        $ret = 0;
        $phoneType = ServiceFactory::getService("App")->getPhoneType($appid);

        $appid = strtolower($appid);
        $appid = str_replace("-", "", $appid);

        switch ($phoneType) {
            case 1:
                $Umeng = new \Umeng("54d97a8bfd98c52b06000086", "qp4vy8yiygqjd5q6xu6fb0yjjpuchdqw");
                //android
                $ret = $Umeng->sendAndroidCustomizedcast($appid, $title, $content, $sound, $shock);
                break;
            case 2:
                $Umeng = new \Umeng("54a8dbd3fd98c523050011c4", "gotzxontizwjo9rfruybnazxmrmonvaf");
                //ios
                $ret = $Umeng->sendIOSCustomizedcast($appid, $title, $content, $sound, $shock);
                break;
            default:
                echo "bad phoneType[" . $phoneType . "]\n";
                $ret = false;
                break;
        }
        return $ret;
    }

    public function pushMessage($type, $tpMachineid, $content, $title = "温馨提示")
    {
        $tpAppidArray = ServiceFactory::getService("Machine")->getAppidList($tpMachineid);
        foreach ($tpAppidArray as $tpAppid) {
            $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
            $this->addMessage($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }

    public function pushHumidifierStart($tpMachineid)
    {
        $tpAppidArray = ServiceFactory::getService("Machine")->getAppidList($tpMachineid);
        file_put_contents("/tmp/pushmsg", date("Y-m-d H:i:s") . " tpMachineid=" . $tpMachineid . ", getAppidList=" . count($tpAppidArray) . "\n", FILE_APPEND);
        foreach ($tpAppidArray as $tpAppid) {
            $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
            //判断是否在线，只发给在线的app
            //发送消息推送,找出这个电器的所有app
            $title = "温馨提示";
            $content = "加湿器已开启";
            $type = "humidifier_start_remind";
            file_put_contents("/tmp/pushmsg", date("Y-m-d H:i:s") . " start to push start tpMachineid=" . $tpMachineid . ", tpAppid=" . $tpAppid . ", appid=" . $appid . ", " . $title . " " . $content . "\n", FILE_APPEND);
            $this->add($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }

    public function pushHumidifierStop($tpMachineid)
    {
        $tpAppidArray = ServiceFactory::getService("Machine")->getAppidList($tpMachineid);
        foreach ($tpAppidArray as $tpAppid) {
            $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
            //判断是否在线，只发给在线的app
            //发送消息推送,找出这个电器的所有app
            $title = "温馨提示";
            $content = "加湿器已关闭";
            $type = "humidifier_end_remind";
            file_put_contents("/tmp/pushmsg", date("Y-m-d H:i:s") . " start to push stop tpMachineid=" . $tpMachineid . " " . $tpAppid . ", " . $appid . ", " . $title . " " . $content . "\n", FILE_APPEND);
            $this->add($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }

    public function pushHumidifierNoWater($tpMachineid)
    {
        $tpAppidArray = ServiceFactory::getService("Machine")->getAppidList($tpMachineid);
        foreach ($tpAppidArray as $tpAppid) {
            $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
            //判断是否在线，只发给在线的app
            //发送消息推送,找出这个电器的所有app
            $title = "温馨提示";
            $content = "加湿器水量不足,请加水";
            $type = "humidifier_no_water_remind";
            file_put_contents("/tmp/pushmsg", date("Y-m-d H:is") . " start to push " . $tpAppid . ", " . $title . " " . $content . "\n", FILE_APPEND);
            $this->add($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }

    /**
     * @desc 干燥提醒
     */
    public function pushHumidifierTooDry($tpMachineid)
    {
        $tpAppidArray = ServiceFactory::getService("Machine")->getAppidList($tpMachineid);
        foreach ($tpAppidArray as $tpAppid) {
            $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
            //判断是否在线，只发给在线的app
            //发送消息推送,找出这个电器的所有app
            $title = "温馨提示";
            $content = "太干燥了,需要加湿了";
            $type = "humidifier_too_dry_remind";
            file_put_contents("/tmp/pushmsg", date("Y-m-d H:is") . " start to push " . $tpAppid . ", " . $title . " " . $content . "\n", FILE_APPEND);
            $this->add($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }

    /**
     * @desc 推送缺水提醒
     */
    public function pushTeapotNoWater($tpAppid, $tpMachineid)
    {
        $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
        $detail = ServiceFactory::getService("App")->getDetail($tpAppid);

        $type = "teapot_no_water_remind";
        $flag = intval($detail[$type]);
        if ($flag) {
            //判断是否在线，只发给在线的app
            //发送消息推送,找出这个电器的所有app
            $title = "热水壶水量不足";
            $content = "";
            file_put_contents("/tmp/pushmsg", date("Y-m-d H:is") . " start to push " . $tpAppid . ", " . $title . " " . $content . "\n", FILE_APPEND);
            $this->add($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }

    /**
     * @desc 推送热水壶开始加热
     */
    public function pushTeapotStart($tpAppid, $tpMachineid)
    {
        $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
        $detail = ServiceFactory::getService("App")->getDetail($tpAppid);

        $type = "teapot_start_remind";
        $flag = intval($detail[$type]);
        if ($flag) {
            //判断是否在线，只发给在线的app
            //发送消息推送,找出这个电器的所有app
            $title = "热水壶开始加热";
            $content = "";
            file_put_contents("/tmp/pushmsg", date("Y-m-d H:is") . " start to push " . $tpAppid . ", " . $title . " " . $content . "\n", FILE_APPEND);
            $this->add($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }

    /**
     * @desc 推送热水壶加热结束
     */
    public function pushTeapotStop($tpAppid, $tpMachineid)
    {
        $appid = ServiceFactory::getService("App")->getAppid($tpAppid);
        $detail = ServiceFactory::getService("App")->getDetail($tpAppid);

        $type = "teapot_end_remind";
        $flag = intval($detail[$type]);
        if ($flag) {
            //判断是否在线，只发给在线的app
            //发送消息推送,找出这个电器的所有app
            $title = "热水壶加热完毕";
            $content = "";
            file_put_contents("/tmp/pushmsg", date("Y-m-d H:is") . " start to push " . $tpAppid . ", " . $title . " " . $content . "\n", FILE_APPEND);
            $this->add($type, $tpAppid, $appid, $title, $content, $tpMachineid);
        }
    }
}
