<?php

class IndexAction extends Action {

    public function index() {
        $this->display('./Home/Tpl/index.html');
    }

    public function menu() {
        echo "menu";exit;
        $this->display('index');
    }

}

?>
