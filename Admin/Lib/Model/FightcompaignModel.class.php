<?php

/**
 * Description of FightcompaignModel
 *
 * @author tony
 */
class FightcompaignModel {
  //put your code here
  public function rebuid_fight_list($list = array()) {
    $i = 0;
    foreach ($list as $val) {
      $list[$i]['schedule'] = $this->get_schedule($val);
      $list[$i]['team_numbers'] = $this->get_team_nums($val);
      $list[$i]['target_numbers'] = $this->get_target_nums($val);
      $i++;
    }
    return $list;
  }

  public function get_schedule($val = array()) {
    $schedule = "0%";
    $starttime = strtotime($val['starttime']);
    $endtime = strtotime($val['endtime']);
    $now = strtotime(date("Y-m-d H:i:s"));
    if ($endtime <= $now) {//已结束
      $schedule = "100%";
    }
    if ($starttime >= $now && $endtime >= $now) {//未开始
      $schedule = "0%";
    }
    if ($starttime < $now && $endtime > $now) {
      $schedule = number_format(($now - $starttime) * 100 / ($endtime - $starttime), 2);
      $schedule = $schedule . "%";
    }
    return $schedule;
  }

  public function get_team_nums($val = array()) {
    $where = array(
        'competition_id' => $val['competition_id']
    );
    $res = D("resistcompantion_target")->where($where)->field('distinct team_id')->select();
    return count($res);
  }

  public function get_output_data($param = array()) {
    $return = array();
    return $return;
  }

  public function get_target_nums($val = array()) {
    $where = array(
        'competition_id' => $val['competition_id']
    );
    $res = D("resistcompantion_target")->where($where)->field('distinct targetvm_id')->select();
    return count($res);
  }

  public function initAttackScore($cid, $tid) {
    $resist = D("resistcompantion_target")->where('competition_id = %d', $cid)->find();
    $teamModel = M('Team');
    $scoreModel = D('resist_score');

    $team = $teamModel->where('team_id = %d', $tid)->find();
    $score = $scoreModel->where('competition_id = %d and team_id = %d', $cid, $tid)->find();
    if (empty($score)) {
      $score = array(
          'competition_id' => $resist['competition_id'],
          'competition_name' => $resist['competition_name'],
          'team_id' => $team['team_id'],
          'team_name' => $team['team_name'],
          'score_get' => 0,
          'score_lose' => 0,
          'score_total' => 0,
          'createtime' => date('Y-m-d H:i:s', time()),
      );
      $score['score_id'] = $scoreModel->add($score);
    }
  }

  /**
   * 初始化攻击队成绩
   * @param type $cid
   * @param type $tid
   */
  public function initScore($cid) {
    $resist = D("resist_competition")->where('competition_id = %d', $cid)->find();
    //获取分配信息
    $resistTeamModel = M('Resistcompantion_target');
    $teamModel = M('Team');
    $examResistModel = M('Exam_resistcomp');
    $examDeModel = M('Exam_resistcomp_de');
    $scoreModel = D('resist_score');
    $scoreDetailModel = D('resist_score_detail');

    $resist_team_array = $resistTeamModel->where('competition_id = %d', $cid)->select();
    $comp_ids = ConstModel::buildIn($resist_team_array, 'targetvm_id', FALSE);

    $exam_resist_array = $examResistModel->where('resistcomp_id in (%s)', $comp_ids)->select();
    $exam_resist_array = ConstModel::changeKeys($exam_resist_array, 'resistcomp_id');
    foreach ($resist_team_array as $resist_team) {
      $team = $teamModel->where('team_id = %d', $resist_team['team_id'])->find();
      if (!empty($resist_team['defense_members'])) {
        //防御队
        $score = $scoreModel->where('competition_id = %d and team_id = %d', $cid, $team['team_id'])->find();
        if (empty($score)) {
          $score = array(
              'competition_id' => $resist['competition_id'],
              'competition_name' => $resist['competition_name'],
              'team_id' => $team['team_id'],
              'team_name' => $team['team_name'],
              'score_get' => 0,
              'score_lose' => 0,
              'total_score' => $exam_resist_array[$resist_team['targetvm_id']]['score'],
              'createtime' => date('Y-m-d H:i:s', time()),
          );
          $score['score_id'] = $scoreModel->add($score);
        }
        $exam_array = $examDeModel->where('resistcomp_id = %d', (int) $resist_team['targetvm_id'])->select();
        $exam_array = ConstModel::changeKeys($exam_array, 'de_id');

        $detail_array = $scoreDetailModel->where('resist_score_id = %d and team_id = %d', (int) $score['score_id'], $team['team_id'])->select();

        if (empty($detail_array)) {
          foreach ($exam_array as $exam) {
            $i++;
            $detail = array(
                'team_id' => $resist_team['team_id'],
                'question_sequence' => $i,
                'question_id' => $exam['de_id'],
                'question_backgroud' => $exam['gatename'],
                'attacker' => '',
                'status' => ConstModel::RE_DETAIL_STATUS_DE,
                'createtime' => date('Y-m-d H:i:s'),
                'resist_score_id' => $score['score_id'],
            );
            $detail_array[] = $detail;
          }
          $scoreDetailModel->addAll($detail_array);
        }
      } else {
        //攻击队
        $this->initAttackScore($cid, $team['team_id']);
      }
    }
  }

}
