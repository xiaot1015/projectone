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
