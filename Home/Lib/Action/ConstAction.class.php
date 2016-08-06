<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConstAction
 *
 * @author tingyu
 */
class ConstAction extends IndexAction {

    public function verify() {
        $id = I('id');
        import('ORG.Util.Image');
        Image::buildImageVerify(4, 1, 'png', 40, 22, 'verify' . $id);
    }

}
