<?php

/**
 * @author tingyu
 */
class ConstModel {

    const LOGIN_TYPE_USER = 0;
    const LOGIN_TYPE_TEAM = 1;
    const USER_AUTH_ST = 0;
    const USER_AUTH_TH = 1;
    const USER_VERIFY_FAIL = 0;
    const USER_UN_VERIFY = 1;
    const USER_VERIFY_ACESS = 2;
    const QUEST_CENTER = 0;
    const QUEST_LEFT = 1;
    const QUEST_RIGHT = 2;
    const DETAIL_STATUS_UN = 0; //未完成
    const DETAIL_STATUS_RIGHT = 1; //正确
    const DETAIL_STATUS_WRONG = 2; //错误
    const QUEST_TYPE_TARGET = 1; //靶场题
    const QUEST_TYPE_RESIST = 2; //对抗题
    const QUEST_TYPE_SINGLE = 3; //单选
    const QUEST_TYPE_MUTI = 4; //多选
    const RE_DETAIL_STATUS_DE = 0; //未攻下
    const RE_DEATIL_STATUS_AT = 1; //攻下

    static $quest_dict = array(
        self::QUEST_CENTER => '=',
        self::QUEST_LEFT => '<',
        self::QUEST_RIGHT => '>'
    );
    static $answer = array(
        '1' => 'A',
        '2' => 'B',
        '3' => 'C',
        '4' => 'D',
        '5' => 'E',
        '6' => 'F',
        '7' => 'G',
    );

    public static function changeKeys($data, $key) {
        $result = array();
        foreach ($data as $d) {
            $result[$d[$key]] = $d;
        }
        return $result;
    }

    public static function getTimeDiff($begin_time, $type = TRUE) {
        if (empty($end_time)) {
            $end_time = time();
        }
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;
        if ($type) {
            return $timediff;
        } else {
            return $days . '天' . $hours . '时' . $mins . '分' . $secs . '秒';
        }
    }

    public static function buildIn($arr, $str, $flag = true) {
        $array = array_unique(array_map(create_function('$a', 'return $a["' . $str . '"];'), $arr));
        return $flag ? "(" . implode(',', $array) . ")" : implode(',', $array);
    }

    public static function getBrowser() {
        $agent = $_SERVER["HTTP_USER_AGENT"];
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) //ie11判断
            return "ie";
        else if (strpos($agent, 'Firefox') !== false)
            return "firefox";
        else if (strpos($agent, 'Chrome') !== false)
            return "chrome";
        else if (strpos($agent, 'Opera') !== false)
            return 'opera';
        else if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false)
            return 'safari';
        else
            return 'unknown';
    }

    public static function getBrowserVer() {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {    //当浏览器没有发送访问者的信息的时候
            return 'unknow';
        }
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs))
            return $regs[1];
        else
            return 'unknow';
    }

}
