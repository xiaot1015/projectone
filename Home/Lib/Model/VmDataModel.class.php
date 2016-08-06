<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once C('API');

/**
 * Description of VmdataModel
 *
 * @author tingyu
 */
class VmDataModel extends Model {

    protected $trueTableName = 'vmdata';

    public function getVmUrl($name) {
        try {
            $url = Api::init()->getUrlByVmName($name);
            return $url;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    public function start($name) {
        try {
            App::init()->start($name);
        } catch (Exception $e) {
            return FALSE;
        }
    }

}
