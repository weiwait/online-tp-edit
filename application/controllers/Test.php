<?php
use base\ServiceFactory;
use base\DaoFactory;
use utils\Common;
use utils\Result;
use utils\TestRequest;

class TestController extends FrontController
{

    private $host = "127.0.0.1";

    /**
     * 初始化
     */
    public function init()
    {
        $this->host = $_SERVER['HTTP_HOST'];
        parent::init();
    }

    /**
     * 首页逻辑
     */
    public function indexAction()
    {
    }

    public function leftAction()
    {
    }

    public function welcomeAction()
    {
    }

    public function appregAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $path = "/app/reg";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("app", "reg"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function machineregAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $path = "/machine/reg";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("machine", "reg"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appbindAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $path = "/app/bind";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("app", "bind"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appunbindAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $path = "/app/unbind";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("app", "unbind"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotactionlogAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];
            $operation = $_REQUEST['operation'];
            $starttime = $_REQUEST['starttime'];
            $endtime = $_REQUEST['endtime'];
            $energy = $_REQUEST['energy'];
            $level = $_REQUEST['level'];
            $temp = $_REQUEST['temp'];
            $boil = $_REQUEST['boil'];
            $purify = $_REQUEST['purify'];
            $keepwarm = $_REQUEST['keepwarm'];
            $path = "/teapot/actionlog";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "operation" => $operation,
                "starttime" => $starttime,
                "endtime" => $endtime,
                "energy" => $energy,
                "level" => $level,
                "temp" => $temp,
                "boil" => $boil,
                "purify" => $purify,
                "keepwarm" => $keepwarm,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "actionlog"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotupdatestateAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $level = $_REQUEST['level'];
            $temp = $_REQUEST['temp'];
            $hub = $_REQUEST['hub'];
            $state = $_REQUEST['state'];
            $path = "/teapot/updatestate";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "level" => $level,
                "temp" => $temp,
                "hub" => $hub,
                "state" => $state,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "updatestate"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotrequestresultAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $orderid = $_REQUEST['orderid'];
            $result = $_REQUEST['result'];

            $path = "/teapot/requestresult";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "orderid" => $orderid,
                "result" => $result,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "requestresult"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotheatAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];
            $temp = $_REQUEST['temp'];
            $boil = $_REQUEST['boil'];
            $purify = $_REQUEST['purify'];
            $keepwarm = $_REQUEST['keepwarm'];
            $heattime = $_REQUEST['heattime'];
            $costtime = $_REQUEST['costtime'];
            $week = $_REQUEST['week'];

            $path = "/teapot/heat";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "temp" => $temp,
                "boil" => $boil,
                "purify" => $purify,
                "keepwarm" => $keepwarm,
                "heattime" => $heattime,
                "costtime" => $costtime,
                "week" => $week,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "heat"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotcancelheatAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];
            $orderid = $_REQUEST['orderid'];

            $path = "/teapot/cancelheat";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "orderid" => $orderid,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "cancelheat"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appgetmachinelistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/app/getmachinelist";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("app", "getmachinelist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appgetmachinenumAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];

            $path = "/app/getmachinenum";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("app", "getmachinenum"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appdeletemachineAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/app/deletemachine";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("app", "deletemachine"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appcheckversionAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $version = $_REQUEST['version'];

            $path = "/app/checkversion";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "version" => $version,
                "v" => "v1",
                "token" => $this->buildToken("app", "checkversion"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appfeedbackAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $content = $_REQUEST['content'];

            $path = "/app/feedback";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "content" => $content,
                "v" => "v1",
                "token" => $this->buildToken("app", "feedback"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function apprequestAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/app/request";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("app", "request"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotrequestAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            //$appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/teapot/request";
            $method = "POST";
            $param = array(
                //"appid"=>$appid,
                "machineid" => $machineid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "request"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appclearallrequestAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/app/clearallrequest";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("app", "clearallrequest"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotgetstateAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/teapot/getstate";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "getstate"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotgetactionloglistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/teapot/getactionloglist";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "getactionloglist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotstopheatAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/teapot/stopheat";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "stopheat"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotgetorderlistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/teapot/getorderlist";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "getorderlist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appupdatelocationAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $longitude = $_REQUEST['longitude'];
            $latitude = $_REQUEST['latitude'];

            $path = "/app/updatelocation";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "longitude" => $longitude,
                "latitude" => $latitude,
                "v" => "v1",
                "token" => $this->buildToken("app", "updatelocation"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appfeedbackdetailAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/app/feedbackdetail";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("app", "feedbackdetail"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appaddmsgAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $content = $_REQUEST['content'];

            $path = "/app/addmsg";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "content" => $content,
                "v" => "v1",
                "token" => $this->buildToken("app", "addmsg"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appgetmsglistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/app/getmsglist";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("app", "getmsglist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appgetunreadmsglistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/app/getunreadmsglist";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("app", "getunreadmsglist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appupdatemsgstatusAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $id = $_REQUEST['id'];

            $path = "/app/updatemsgstatus";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "id" => $id,
                "v" => "v1",
                "token" => $this->buildToken("app", "updatemsgstatus"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appgetmsgnumAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];

            $path = "/app/getmsgnum";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("app", "getmsgnum"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appgetunreadmsgnumAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];

            $path = "/app/getunreadmsgnum";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("app", "getunreadmsgnum"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appdeletemsgAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $id = $_REQUEST['id'];

            $path = "/app/deletemsg";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "id" => $id,
                "v" => "v1",
                "token" => $this->buildToken("app", "deletemsg"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotstatAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/teapot/stat";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "id" => $id,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "stat"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function appgetnearmachineAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $onlineflag = $_REQUEST['onlineflag'];
            $bindflag = $_REQUEST['bindflag'];

            $path = "/app/getnearmachine";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "onlineflag" => $onlineflag,
                "bindflag" => $bindflag,
                "id" => $id,
                "v" => "v1",
                "token" => $this->buildToken("app", "getnearmachine"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function teapotruntimeAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $orderid = $_REQUEST['orderid'];
            $state = $_REQUEST['state'];

            $path = "/teapot/runtime";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "state" => $state,
                "orderid" => $orderid,
                "v" => "v1",
                "token" => $this->buildToken("teapot", "teapotruntime"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }


    //加湿器接口

    public function humidifieractionlogAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];
            $operation = $_REQUEST['operation'];
            $starttime = $_REQUEST['starttime'];
            $endtime = $_REQUEST['endtime'];
            $startlevel = $_REQUEST['startlevel'];
            $endlevel = $_REQUEST['endlevel'];
            $tophumidity = $_REQUEST['tophumidity'];
            $middlehumidity = $_REQUEST['middlehumidity'];
            $bottomhumidity = $_REQUEST['bottomhumidity'];
            $starthumidity = $_REQUEST['starthumidity'];
            $endhumidity = $_REQUEST['endhumidity'];
            $energy = $_REQUEST['energy'];

            $path = "/humidifier/actionlog";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "operation" => $operation,
                "starttime" => $starttime,
                "endtime" => $endtime,
                "startlevel" => $startlevel,
                "endlevel" => $endlevel,
                "tophumidity" => $tophumidity,
                "middlehumidity" => $middlehumidity,
                "bottomhumidity" => $bottomhumidity,
                "starthumidity" => $starthumidity,
                "endhumidity" => $endhumidity,
                "energy" => $energy,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "actionlog"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifierupdatestateAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $humidity = $_REQUEST['humidity'];
            $level = $_REQUEST['level'];
            $state = $_REQUEST['state'];
            $path = "/humidifier/updatestate";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "humidity" => $humidity,
                "level" => $level,
                "state" => $state,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "updatestate"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifiergetstateAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/humidifier/getstate";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "getstate"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifiergetactionloglistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/humidifier/getactionloglist";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "getactionloglist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifierstatAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/humidifier/stat";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "id" => $id,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "stat"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifierrequestAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];

            $path = "/humidifier/request";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "request"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifierstartAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];
            $grade = $_REQUEST['grade'];
            //$issummermode = $_REQUEST['issummermode'];
            $delay = $_REQUEST['delay'];

            $path = "/humidifier/start";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "delay" => $delay,
                "grade" => $grade,
                //"issummermode"=>$issummermode,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "start"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifierstopAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $path = "/humidifier/stop";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "start"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifiergetconfigAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $path = "/humidifier/getconfig";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "getconfig"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifiersaveconfigAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $drymode = $_REQUEST['drymode'];
            $wetmode = $_REQUEST['wetmode'];
            $enableai = $_REQUEST['enableai'];
            $enableusernearstart = $_REQUEST['enableusernearstart'];
            $enableuserfarstop = $_REQUEST['enableuserfarstop'];
            $toodryremind = $_REQUEST['toodryremind'];

            $path = "/humidifier/saveconfig";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "drymode" => $drymode,
                "wetmode" => $wetmode,
                "enableai" => $enableai,
                "enableusernearstart" => $enableusernearstart,
                "enableuserfarstop" => $enableuserfarstop,
                "toodryremind" => $toodryremind,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "getconfig"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifierorderAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $action = $_REQUEST['action'];
            $week = $_REQUEST['week'];
            $heattime = $_REQUEST['heattime'];
            $startremind = $_REQUEST['startremind'];
            $endremind = $_REQUEST['endremind'];
            $nowaterremind = $_REQUEST['nowaterremind'];
            $grade = $_REQUEST['grade'];

            $path = "/humidifier/order";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "week" => $week,
                "startremind" => $startremind,
                "endremind" => $endremind,
                "nowaterremind" => $nowaterremind,
                "heattime" => $heattime,
                "grade" => $grade,
                "action" => $action,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "order"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifiergetorderAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $orderid = $_REQUEST['orderid'];

            $path = "/humidifier/getorder";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "orderid" => $orderid,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "getorder"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifiercancelorderAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $orderid = $_REQUEST['orderid'];

            $path = "/humidifier/cancelorder";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "orderid" => $orderid,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "cancelorder"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function humidifiergetorderlistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/humidifier/getorderlist";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("humidifier", "getorderlist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    //灯接口

    public function lightactionlogAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];
            $operation = $_REQUEST['operation'];
            $starttime = $_REQUEST['starttime'];
            $endtime = $_REQUEST['endtime'];
            $startlevel = $_REQUEST['startlevel'];
            $endlevel = $_REQUEST['endlevel'];
            $tophumidity = $_REQUEST['tophumidity'];
            $middlehumidity = $_REQUEST['middlehumidity'];
            $bottomhumidity = $_REQUEST['bottomhumidity'];
            $starthumidity = $_REQUEST['starthumidity'];
            $endhumidity = $_REQUEST['endhumidity'];
            $energy = $_REQUEST['energy'];

            $path = "/light/actionlog";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "operation" => $operation,
                "starttime" => $starttime,
                "endtime" => $endtime,
                "startlevel" => $startlevel,
                "endlevel" => $endlevel,
                "tophumidity" => $tophumidity,
                "middlehumidity" => $middlehumidity,
                "bottomhumidity" => $bottomhumidity,
                "starthumidity" => $starthumidity,
                "endhumidity" => $endhumidity,
                "energy" => $energy,
                "v" => "v1",
                "token" => $this->buildToken("light", "actionlog"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightupdatestateAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $lightness = $_REQUEST['lightness'];
            $temperature = $_REQUEST['temperature'];
            $red = $_REQUEST['red'];
            $green = $_REQUEST['green'];
            $blue = $_REQUEST['blue'];
            $state = $_REQUEST['state'];
            $path = "/light/updatestate";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "lightness" => $lightness,
                "temperature" => $temperature,
                "red" => $red,
                "green" => $green,
                "blue" => $blue,
                "state" => $state,
                "v" => "v1",
                "token" => $this->buildToken("light", "updatestate"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightgetstateAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/light/getstate";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("light", "getstate"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightgetactionloglistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];
            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/light/getactionloglist";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("light", "getactionloglist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightstatAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $appid = $_REQUEST['appid'];
            $machineid = $_REQUEST['machineid'];

            $path = "/light/stat";
            $method = "POST";
            $param = array(
                "appid" => $appid,
                "machineid" => $machineid,
                "id" => $id,
                "v" => "v1",
                "token" => $this->buildToken("light", "stat"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightrequestAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];

            $path = "/light/request";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("light", "request"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightrequestcallbackAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];

            $path = "/light/requestcallback";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "v" => "v1",
                "token" => $this->buildToken("light", "request"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightstartAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];
            $lightness = $_REQUEST['lightness'];
            $temperature = $_REQUEST['temperature'];
            $red = $_REQUEST['red'];
            $green = $_REQUEST['green'];
            $blue = $_REQUEST['blue'];

            $path = "/light/start";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "lightness" => $lightness,
                "temperature" => $temperature,
                "red" => $red,
                "green" => $green,
                "blue" => $blue,
                "v" => "v1",
                "token" => $this->buildToken("light", "start"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightstopAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $path = "/light/stop";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("light", "start"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightgetconfigAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $path = "/light/getconfig";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "v" => "v1",
                "token" => $this->buildToken("light", "getconfig"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightsaveconfigAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $drymode = $_REQUEST['drymode'];
            $wetmode = $_REQUEST['wetmode'];
            $enableai = $_REQUEST['enableai'];
            $enableusernearstart = $_REQUEST['enableusernearstart'];
            $enableuserfarstop = $_REQUEST['enableuserfarstop'];
            $toodryremind = $_REQUEST['toodryremind'];

            $path = "/light/saveconfig";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "drymode" => $drymode,
                "wetmode" => $wetmode,
                "enableai" => $enableai,
                "enableusernearstart" => $enableusernearstart,
                "enableuserfarstop" => $enableuserfarstop,
                "toodryremind" => $toodryremind,
                "v" => "v1",
                "token" => $this->buildToken("light", "getconfig"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightorderAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $action = $_REQUEST['action'];
            $week = $_REQUEST['week'];
            $heattime = $_REQUEST['heattime'];
            $lightness = $_REQUEST['lightness'];
            $temperature = $_REQUEST['temperature'];
            $red = $_REQUEST['red'];
            $green = $_REQUEST['green'];
            $blue = $_REQUEST['blue'];

            $path = "/light/order";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "week" => $week,
                "heattime" => $heattime,
                "lightness" => $lightness,
                "temperature" => $temperature,
                "red" => $red,
                "green" => $green,
                "blue" => $blue,
                "action" => $action,
                "v" => "v1",
                "token" => $this->buildToken("light", "order"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightgetorderAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $orderid = $_REQUEST['orderid'];

            $path = "/light/getorder";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "orderid" => $orderid,
                "v" => "v1",
                "token" => $this->buildToken("light", "getorder"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightcancelorderAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $orderid = $_REQUEST['orderid'];

            $path = "/light/cancelorder";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "orderid" => $orderid,
                "v" => "v1",
                "token" => $this->buildToken("light", "cancelorder"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function lightgetorderlistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $appid = $_REQUEST['appid'];

            $page = $_REQUEST['page'];
            $pagesize = $_REQUEST['pagesize'];

            $path = "/light/getorderlist";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "appid" => $appid,
                "page" => $page,
                "pagesize" => $pagesize,
                "v" => "v1",
                "token" => $this->buildToken("light", "getorderlist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    //路由器接口
    public function routerupdatelistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $list = $_REQUEST['list'];

            $path = "/router/updatelist";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "list" => $list,
                "v" => "v1",
                "token" => $this->buildToken("router", "updatelist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function routerclearlistAction()
    {
        $requestString = "";
        $result = "";

        if ("POST" == $_SERVER['REQUEST_METHOD']) {
            $machineid = $_REQUEST['machineid'];
            $list = $_REQUEST['list'];

            $path = "/router/clearlist";
            $method = "POST";
            $param = array(
                "machineid" => $machineid,
                "list" => $list,
                "v" => "v1",
                "token" => $this->buildToken("router", "clearlist"),
            );
            $requestString = TestRequest::buildRequest($this->host, $path, $method, $param);
            $result = TestRequest::sendRequest($this->host, 8081, $requestString);
        }
        $this->getView()->requestString = $requestString;
        $this->getView()->result = $result;
    }

    public function wangLogAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        echo nl2br(file_get_contents("wang.log"));
    }

    public function yaoLogAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        echo nl2br(file_get_contents("yaojun.log"));
    }

    public function liuLogAction()
    {
        \Yaf_Dispatcher::getInstance()->disableView();
        echo nl2br(file_get_contents("liu.log"));
    }

}