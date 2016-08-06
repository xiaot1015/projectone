<?php

/**
 * Description of AuthAction
 *
 * @author tony
 */
class AuthAction extends Action {

    //put your code here
    protected $uid = 0;
    protected $tid;
    protected $name = 'test帐号';

    public function _initialize() {
        $uid = session('uid');
        $tid = session('tid');
        $name = session('name');
        $ip = session('ip');

         if (empty($uid) || empty($ip)) {
          $this->error("请登录系统", "/index.php/login/index");
          exit();
          }
          $check_admin = D("user")->where("type in(1,2) and user_id = " . $uid)->find();
          if (empty($check_admin)) {
          $this->error("禁止访问", "/index.php/login/index");
          exit();
          } 

        $this->uid = empty($uid) ? $this->uid : $uid;
        $this->name = empty($name) ? $this->name : $name;
        $syslist = M('system_info')->limit(1)->select();
        $sysinfo = $syslist[0];
        
        $this->assign('sysinfo', $sysinfo);
        $this->assign('username', $this->name);
        $this->assign('userid', $this->uid);
        $this->assign('logout_url', 'Home/login/out');
    }

    public function success($message, $href) {
        echo htmlspecialchars_decode("<script>alert('" . $message . "');window.location.href='" . $href . "';</script>");
        exit();
    }

    public function error($message, $href) {
        echo htmlspecialchars_decode("<script>alert('" . $message . "');window.location.href='" . $href . "';</script>");
        exit();
    }

}
