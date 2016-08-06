<?php

class ResistAction extends IndexAction {

    protected $resistModel;

    public function __construct() {
        $this->resistModel = M('Resist_competition');
        parent::__construct();
    }

    public function index() {
        import('ORG.Util.String');
        $resist_array = $this->resistModel->where('status = 2')->order('starttime desc')->select();
        $this->assign('resist_array', $resist_array);
        $this->display();
    }

    public function info() {
        $id = I('id');
        $resist = $this->resistModel->where('competition_id = %d', $id)->find();
        $this->assign('resist', $resist);
        $this->display();
    }

    public function show() {
        $id = I('id');
        $resist = $this->resistModel->where('competition_id = %d', $id)->find();
        $this->startVM($id, 2);
        //判断时间
        if (empty($resist)) {
            //找不到信息，跳转到404页面。
            $this->redirect('index/to404');
            exit();
        } else if (strtotime($resist['endtime']) <= time()) {
            //比赛已结束，跳转到成绩排名页。
            $this->redirect('resist/score', array('tid' => $id));
            exit();
        }
        $this->assign('resist', $resist);
        $this->display();
    }

    public function renter() {
        $id = I('tid');
        $this->check($id, 'red');
        //获取比赛
        $resist = $this->resistModel->where('competition_id = %d', $id)->find();
        //获取分配信息
        $resistTeamModel = M('Resistcompantion_target');
        $resist_team_array = $resistTeamModel->where('competition_id = %d', $id)->select();
        $team_id_array = array();
        $resist_comp_array = array();
        foreach ($resist_team_array as $resist_team) {
            if (!empty($resist_team['defense_members'])) {
                //该队是蓝队
                $team_id_array[] = $resist_team['team_id'];
                $resist_comp_array[] = $resist_team['targetvm_id'];
            }
        }
        $comp_ids = implode(',', $resist_comp_array);
        $examResistModel = M('Exam_resistcomp');
        $exam_resist_array = $examResistModel->where('resistcomp_id in (%s)', $comp_ids)->select();
        $exam_resist_array = ConstModel::changeKeys($exam_resist_array, 'resistcomp_id');

        import('ORG.Util.Page'); // 导入分页类
        $team_ids = implode(',', $team_id_array);
        $teamModel = M('Team');
        $count = $teamModel->where('team_id in (%s) and team_id != %d ', $team_ids, $this->tid)->count();
        $Page = new Page($count, 8);
        $team_array = $teamModel->where('team_id in (%s) and team_id != %d ', $team_ids, $this->tid)->order('team_id')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $team_array = ConstModel::changeKeys($team_array, 'team_id');

        $show = $Page->show();
        foreach ($resist_team_array as $resist_team) {
            if ($resist_team['team_id'] == $team_array[$resist_team['team_id']]['team_id']) {
                $team_array[$resist_team['team_id']]['score_total'] = $exam_resist_array[$resist_team['targetvm_id']]['score'];
            }
        }

        //蓝队的成绩
        $scoreModel = D('Resist_score');
        $score_array = array();

        foreach ($team_array as $team) {
            $score = $scoreModel->where('competition_id = %d and team_id = %d', $id, $team['team_id'])->find();
            $score_array[] = $score;
        }
        foreach ($score_array as $score) {
            if ($score['team_id'] == $team_array[$score['team_id']]['team_id']) {
                $team_array[$score['team_id']]['score_lose'] = $score['score_lose'];
            }
        }

        $this->assign('resist', $resist);
        $this->assign('team_array', $team_array);

        $this->assign('page', $show);
        $this->display();
    }

    public function benter() {
        $id = $this->_param('tid');
        $this->check($id, 'blue');

        $resist = $this->resistModel->where('competition_id = %d', $id)->find();

        $resistTeamModel = M('Resistcompantion_target');
        $resist_team = $resistTeamModel->where('competition_id = %d and team_id = %d', $id, $this->tid)->find();
        $examResistModel = M('Exam_resistcomp');
        $exam_resist = $examResistModel->where('resistcomp_id = %d', (int) $resist_team['targetvm_id'])->find();

        //查询questions
        import('ORG.Util.Page'); // 导入分页类
        $examDeModel = M('Exam_resistcomp_de');
        $count = $examDeModel->where('resistcomp_id = %d', $exam_resist['resistcomp_id'])->count();
        $Page = new Page($count, 6);
        $question_array = $examDeModel->where('resistcomp_id = %d', $exam_resist['resistcomp_id'])->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $question_array = ConstModel::changeKeys($question_array, 'de_id');
        $show = $Page->show();
        //查询蓝队成绩：如果成绩不存在，则初始化蓝队成绩
        $scoreModel = D('resist_score');
        $score = $scoreModel->where('competition_id = %d and team_id = %d', $id, $this->tid)->find();

        $scoreDetailModel = D('resist_score_detail');
        $detail_array = $scoreDetailModel->where('resist_score_id = %d and team_id = %d', (int) $score['score_id'], $this->tid)->select();

        foreach ($detail_array as $detail) {
            if ($detail['question_id'] == $question_array[$detail['question_id']]['de_id']) {
                $question_array[$detail['question_id']]['attack'] = $detail['status'];
            }
        }

        //获取防御队的虚拟机
        $vmDataModel = new VmDataModel();

        $vminfo = $vmDataModel->where('compid = %d and comptype = %d and teamid = %d', $id, 2, $this->tid)->find();
        $vminfo = explode(',', $vminfo['vms']);

        $vms_array = array();
        foreach ($vminfo as $vms) {
            $vm = array();
            if (strpos($vms, 'att') !== FALSE && strpos($vms, 'att') == 0) {
                continue;
            } else {
                $vm['name'] = $vms;
                $vm['url'] = $vmDataModel->getVmUrl($vms);
                $vms_array[] = $vm;
            }
        }

        session('vms', $vms_array);

        //初始化蓝队成绩
        $this->assign('question_array', $question_array);
        $this->assign('resist', $resist);
        $this->assign('vms_array', $vms_array);
        $this->assign('page', $show);
        $this->assign('p', (!isset($_GET['p']) || empty($_GET['p'])) ? 1 : $_GET['p']);
        $this->display();
    }

    public function exam() {
        $cid = I('cid');
        $tid = I('team');
        $this->check($cid);
        //获取当前的比赛
        $resist = $this->resistModel->where('competition_id = %d', $cid)->find();

        //获取防御队的信息
        $teamModel = M('Team');
        $team = $teamModel->where('team_id = %d', $tid)->find();
        //获取防御对的ip信息
        $ipModel = D('Resist_ip');
        $ip = $ipModel->where('team_id = %d and competition_id = %d', $tid, $cid)->find();
        if (empty($ip)) {
            $ip = array(
                'ip' => '',
            );
        }

        //获取防御队的靶场
        $resistTeamModel = M('Resistcompantion_target');
        $resist_team = $resistTeamModel->where('competition_id = %d and team_id = %d', $cid, $tid)->find();

        import('ORG.Util.Page'); // 导入分页类
        //获取防御队的题
        $examDeModel = M('Exam_resistcomp_de');
        $count = $examDeModel->where('resistcomp_id = %d', (int) $resist_team['targetvm_id'])->count();
        $Page = new Page($count, 6);
        $exam_array = $examDeModel->where('resistcomp_id = %d', (int) $resist_team['targetvm_id'])->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $exam_array = ConstModel::changeKeys($exam_array, 'de_id');
        $show = $Page->show(); //
        //获取防御队的成绩
        $scoreModel = D('resist_score');
        $score = $scoreModel->where('competition_id = %d and team_id = %d', $cid, $tid)->find();

        //获取防御队的成绩详细
        $scoreDetailModel = D('resist_score_detail');
        $detail_array = $scoreDetailModel->where('resist_score_id = %d and team_id = %d', (int) $score['score_id'], $tid)->select();

        foreach ($detail_array as $detail) {
            if ($detail['question_id'] == $exam_array[$detail['question_id']]['de_id']) {
                $exam_array[$detail['question_id']]['attack'] = $detail['status'];
            }
        }

        //获取攻击队的攻击机
        $vmDataModel = new VmDataModel();

        $vminfo = $vmDataModel->where('compid = %d and comptype = %d and teamid = %d', $cid, 2, $this->tid)->find();
        $vminfo = explode(',', $vminfo['vms']);
        $vms_array = array();
        foreach ($vminfo as $vms) {
            $vm = array();
            if (strpos($vms, 'att') !== FALSE && strpos($vms, 'att') == 0) {
                $vm['name'] = $vms;
                $vm['url'] = $vmDataModel->getVmUrl($vms);
                $vms_array[] = $vm;
            }
        }

        //获取攻击队的成绩
        $score_att = $scoreModel->where('competition_id = %d and team_id = %d', $cid, $this->tid)->find();

        $this->assign('resist', $resist);
        $this->assign('team', $team);
        $this->assign('exam_array', $exam_array);
        $this->assign('page', $show);
        $this->assign('p', (!isset($_GET['p']) || empty($_GET['p'])) ? 1 : $_GET['p']);
        $this->assign('ip', $ip['ip']);
        $this->assign('vms_array', $vms_array);
        $this->display();
    }

    public function score() {
        $id = $this->_param('tid');
        $resist = $this->resistModel->where('competition_id = %d', $id)->find();
        $resistScoreModel = D('Resist_score');
        $score_array = $resistScoreModel->where('competition_id = %d', $id)->order('total_score desc,lastupdate')->select();

        $teamModel = M('Team');
        $team_array = $teamModel->select();
        $team_array = ConstModel::changeKeys($team_array, 'team_id');

        $userModel = M('User');

        foreach ($score_array as &$score) {
            if ($score['team_id'] == $team_array[$score['team_id']]['team_id']) {
                $user_array = $userModel->where('user_id in (%s) and status  != 0', $team_array[$score['team_id']]['team_members'])->select();
                $score['team_members'] = ConstModel::buildIn($user_array, 'user_name', FALSE);
            }
        }
        $this->assign('resist', $resist);
        $this->assign('score_array', $score_array);
        $this->display();
    }

    public function judge() {
        $key = I('key');
        $verify = I('verify');
        $cid = I('resist');
        $eid = I('exam');
        $tid = I('team');

        if (md5($verify) != session('verify' . $eid)) {
            $this->ajaxReturn('', '验证失败', 2);
        }
        //获取当前题
        $examModel = M('Exam_resistcomp_de');
        $exam = $examModel->where('de_id = %d', $eid)->find();

        $scoreModel = D('Resist_score');
        //获取攻击队的成绩
        $score_att = $scoreModel->where('competition_id = %d and team_id = %d', $cid, $this->tid)->find();
        //获取防御队的成绩
        $score_de = $scoreModel->where('competition_id = %d and team_id = %d', $cid, $tid)->find();
        $scoreDetailModel = D('Resist_score_detail');
        //获取防御队对应题的成绩
        $detail = $scoreDetailModel->where('resist_score_id = %d and question_id = %d', $score_de['score_id'], $eid)->find();

        if ($detail['status'] == ConstModel::RE_DEATIL_STATUS_AT) {
            $this->ajaxReturn('', '该题已被攻陷', 2);
        }

        $flag = FALSE;
        if (strpos($exam['keys'], $key) !== FALSE) {
            //答对了
            $flag = TRUE;
            //攻击队总分增加，得分增加
            $score_att['score_get'] = $score_att['score_get'] + $exam['score'];
            $score_att['total_score'] = $score_att['total_score'] + $exam['score'];
            //防御队总分下降，失分增加
            $score_de['score_lose'] = $score_de['score_lose'] + $exam['score'];
            $score_de['total_score'] = $score_de['total_score'] - $exam['score'];
            $detail['attacker'] = $this->tid;
            $detail['status'] = ConstModel::RE_DEATIL_STATUS_AT;
            $s = $scoreModel->data($score_att)->save();
            $scoreModel->data($score_de)->save();
            $s = $scoreDetailModel->data($detail)->save();
        }
        $flag ? $this->ajaxReturn($eid, '答题正确', 1) : $this->ajaxReturn($eid, '答题失败', 2);
    }

    public function ip() {
        $data = array(
            'ip' => I('ip'),
            'team_id' => $this->tid,
            'competition_id' => I('resist'),
        );
        $resisIpModel = D('Resist_ip');
        $status = $resisIpModel->data($data)->add();
        empty($status) ? $this->ajaxReturn($data, '', 2) : $this->ajaxReturn($data, '', 1);
    }

    protected function check($id, $type = 'red') {
        $resistTeamModel = M('Resistcompantion_target');
        $resist_team_array = $resistTeamModel->where('competition_id = %d', $id)->select();
        $teams = ConstModel::buildIn($resist_team_array, 'team_id', FALSE);
        $team_id_array = explode(',', $teams);
        if (!in_array($this->tid, $team_id_array)) {
            $this->redirect('resist/index', array('id' => $id));
        }
        $resist_team = $resistTeamModel->where('competition_id = %d and team_id= %d', $id, $this->tid)->find();
        if ($type == 'red') {
            if (empty($resist_team['attack_members'])) {
                $this->redirect('resist/index');
            }
        } else if ($type == 'blue') {
            if (empty($resist_team['defense_members'])) {
                $this->redirect('resist/index');
            }
        }
    }

    protected function startVM($id, $type) {
        $vmDataModel = new VmDataModel();
        $vminfo = $vmDataModel->where('compid = %d and comptype = %d', $id, $type)->select();
        foreach ($vminfo as $vms) {
            $vms = explode(',', $vms['vms']);
            foreach ($vms as $vm) {
                if (strpos($vm, 'att') !== FALSE && strpos($vm, 'att') == 0) {
                    continue;
                }
                $this->vm($vm);
            }
        }
    }

    public function start() {
        $id = I('id');
        $this->vm($id);
        $url = $this->url($id);
        if (empty($url)) {
            echo "打开虚拟机失败。";
            exit();
        }
        header('Location:' . $url);
    }

    public function vm($name) {
        include_once C('API');
        try {
            if (!(Api::init()->runningState($name))) {
                Api::init()->start($name);
            }
        } catch (Exception $e) {
            
        }
    }

    public function url($name) {
        include_once C('API');
        $url = '';
        try {
            $url = Api::init()->getUrlByVmName($name);
        } catch (Exception $e) {
            
        }
        return $url;
    }

}
