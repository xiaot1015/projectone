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
class TargetScoreDetailModel extends Model {

    protected $fileds = array(
        'id',
        'user_id',
        'question_type',
        'question_sequence',
        'question_id',
        'question_desc',
        'status',
        'createtime',
        'score_id',
        '_pk' => 'id',
        '_autoinc' => true,
    );
    protected $_validate = array(
    );

}
