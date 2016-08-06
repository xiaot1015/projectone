<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TargetScoreModel
 *
 * @author tingyu
 */
class ResistIpModel extends Model {

    protected $fileds = array(
        'id',
        'competition_id',
        'team_id',
        'ip',
        '_pk' => 'id',
        '_autoinc' => true,
    );
    protected $_validate = array(
    );

}
