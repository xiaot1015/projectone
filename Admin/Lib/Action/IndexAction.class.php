<?php

class IndexAction extends Action {

    public function index() {
        $this->redirect('User/index');
        echo "'aa'";exit;
    }

    public function menu() {
        echo "menu";exit;
        $this->display('index');
    }

}

?>
