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
class ResistScoreModel extends Model {

    protected $fileds = array(
        'score_id',
        'competition_id',
        'competition_name',
        'team_id',
        'team_name',
        'score_get',
        'score_lose',
        'total_score',
        'createtime',
        'lastupdate',
        '_pk' => 'score_id',
        '_autoinc' => true,
    );
    protected $_validate = array(
    );

}
