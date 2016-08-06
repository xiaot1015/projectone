<?php

class IndexAction extends Action {

    public function index() {
    $this->redirect('User/index');
  }

    public function menu() {
        $this->display('index');
    }

}

?>
