<?php

include_once "Api.php";
require_once "MCommonController.php";

/**
 * 关于学校打卡的功能
 * User: ACC
 * Date: 16/7/19
 * Time: 上午10:20
 */
class SchoolController extends MCommonController
{
    /**
     * @desc 用户注册
     */
    public function registAction()
    {
        if ($this->DEMO) {
            $this->checkIsDemo();
        }
        $phone = $this->getValidParam('phone', '电话', $this->TYPE_PHONE, true);
        $name = $this->getValidParam('name');
        $school = $this->getValidParam('school');
        $class = $this->getValidParam('class');
        $company = $this->getValidParam('company');
        $job = $this->getValidParam('job');
        $this->show("注册成功");
    }

    /**
     * @desc 获取用户详细信息
     */
    public function getUserAction()
    {
        $this->checkIsDemo();
        $id = $this->getValidParam('user', 'user', null, true);
        $user = ApiController::getSchoolRegistDemoData();
        $user['id'] = $id;
        $this->show($user);
    }

    /**
     * @desc 绑定标签
     */
    public function bindLabelAction()
    {
        $this->checkIsDemo();
        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);
        $lable = $this->getValidParam('label', 'label', $this->TYPE_NUMBER, true, "/^10[0-9]{18}$/", "应为20为数字,前面为10");
        $this->show("标签绑定成功");
    }

    /**
     * @desc 获取用户选修课程
     */
    public function getUserCourseAction()
    {
        $this->checkIsDemo();
        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);

        $data['user'] = $user;

        $course['id'] = '123';
        $course['name'] = '歌剧赏析';
        $course['teacher'] = '345';
        $courses[0] = $course;

        $courseTwo['id'] = '124';
        $courseTwo['name'] = '跆拳道';
        $courseTwo['teacher'] = '346';
        $courses[1] = $courseTwo;

        $courseThree['id'] = '125';
        $courseThree['name'] = '足球';
        $courseThree['teacher'] = '347';
        $courses[2] = $courseThree;

        $data['courses'] = $courses;

        $this->show($data);
    }

    /**
     * @desc 获取课程详细
     */
    public function getCourseAction()
    {
        $this->checkIsDemo();
        $course = $this->getValidParam('course', 'course', $this->TYPE_NUMBER, true);

        $data['course'] = $course;

        $data['timetables'] = ApiController::getSchoolTimetablesDemoData();

        $this->show($data);
    }

    /**
     * @desc 获取用户课程表
     */
    public function getTimetablesAction()
    {
        $this->checkIsDemo();

        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);

        $data['user'] = $user;

        $data['timetables'] = ApiController::getSchoolTimetablesDemoData();

        $this->show($data);
    }

    /**
     * @desc 获取评价和分数
     */
    public function getValueAction()
    {
        $this->checkIsDemo();
        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);
        $course = $this->getValidParam('course', 'course', $this->TYPE_NUMBER, true);
        $value['id'] = '123';
        $value['user'] = $user;
        $value['course'] = $course;
        $value['value'] = '8';
        $value['score'] = '60';
        $this->show($value);
    }

    /**
     * @desc 课程评价
     */
    public function setValueAction()
    {
        $this->checkIsDemo();
        $value = $this->getValidParam('value', 'value', null, true, "/^([1-5])$/", "应为1~5数字");
        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);
        $course = $this->getValidParam('course', 'course', $this->TYPE_NUMBER, true);
        $this->show("评价成功");
    }

    /**
     * @desc 设置提醒
     */
    public function setNotifyAction()
    {
        $this->checkIsDemo();
        $notify = $this->getValidParam('notify', 'notify', null, true, "/^[0-9]$/", "应为0~9数字");
        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);
        $course = $this->getValidParam('course', 'course', $this->TYPE_NUMBER, true);
        $this->show("提醒成功");
    }

    /**
     * @desc 获取考勤记录
     */
    public function getAttendanceAction()
    {
        $this->checkIsDemo();
        $begin = $this->getValidParam('begin', 'begin', null, true, "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", "应为格式如2016-07-20");
        $end = $this->getValidParam('end', 'end', null, true, "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", "应为格式如2016-07-20");
        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);
        $course = $this->getValidParam('course');
        $courseDetail = $this->getValidParam('courseDetail');

        $data = ApiController::getSchoolGetAttendanceDemoData();

        $this->show($data);
    }

    /**
     * @desc 获取统计信息
     */
    public function getStaticAction()
    {
        $this->checkIsDemo();
        $begin = $this->getValidParam('begin', 'begin', null, true, "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", "应为格式如2016-07-20");
        $end = $this->getValidParam('end', 'end', null, true, "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", "应为格式如2016-07-20");
        $user = $this->getValidParam('user', 'user', $this->TYPE_NUMBER, true);
        $course = $this->getValidParam('course');

        $data = ApiController::getSchoolGetStaticDemoData();

        $this->show($data);
    }

    /**
     * 获取控制电器的参数值
     * @return mixed
     */
    function getControlData()
    {
        return null;
    }
}