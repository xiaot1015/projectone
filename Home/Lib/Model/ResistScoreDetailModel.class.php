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
class ResistScoreDetailModel extends Model {

    protected $fileds = array(
        'id',
        'team_id',
        'detail_id',
        'question_sequence',
        'question_id',
        'question_backgroud',
        'attacker',
        'status',
        'createtime',
        'resist_score_id',
        '_pk' => 'id',
        '_autoinc' => true,
    );
    protected $_validate = array(
    );

}
