<?php

class IndexAction extends Action {

    protected $uid;
    protected $tid;
    protected $name;
    protected $type;

    public function _initialize() {
        $sysModel = M('System_info');
        $sys = $sysModel->find();
        session('title', $sys['name']);
        $uid = session('uid');
        $tid = session('tid');
        $name = session('name');
        if (isset($uid) && !empty($uid)) {
            $this->uid = $uid;
            $this->name = $name;
            $this->type = ConstModel::LOGIN_TYPE_TEAM;
        } elseif (!empty($tid)) {
            $this->tid = $tid;
            $this->name = $name;
            $this->type = ConstModel::LOGIN_TYPE_USER;
        } else {
            $this->redirect('login/index');
        }
    }

    public function index() {
        $sysModel = M('System_info');
        $sys = $sysModel->find();
        $this->assign('sys', $sys);
        $this->display();
    }

    protected function to404() {
        $this->display();
        exit();
    }

    public function success($message, $href = '') {
        if (empty($href)){
            echo htmlspecialchars_decode("<script>alert('" . $message . "');window.location.reload();</script>");
        }
        echo htmlspecialchars_decode("<script>alert('" . $message . "');window.location.href='" . $href . "';</script>");
        exit();
    }

    public function error($message, $href) {
        echo htmlspecialchars_decode("<script>alert('" . $message . "');window.location.href='" . $href . "';</script>");
        exit();
    }

}
