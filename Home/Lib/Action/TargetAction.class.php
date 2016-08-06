<?php

// 本类由系统自动生成，仅供测试用途
class TargetAction extends IndexAction {

    protected $targetModel;

    public function __construct() {
        $this->targetModel = M('Target_competition');
        parent::__construct();
    }

    public function index() {
        import('ORG.Util.String');
        $target_array = $this->targetModel->where('status = 2')->order('starttime desc')->select();
        $this->assign('target_array', $target_array);
        $this->display();
    }

    public function info() {
        $id = I('id');
        $target = $this->targetModel->where('competition_id = %d', $id)->find();
        $this->assign('target', $target);
        $this->display();
    }

    public function show() {
        $id = $this->_param('id');
        $target = $this->targetModel->where('competition_id = %d', $id)->find();
        $this->check($target);

        //判断时间
        if (empty($target)) {
            //找不到信息，跳转到404页面。
            $this->redirect('index/to404');
            exit();
        } else if (strtotime($target['endtime']) <= time()) {
            //比赛已结束，跳转到成绩排名页。
            $this->redirect('target/score', array('tid' => $id));
            exit();
        } else {
            //比赛正在继续，进入答题页。
            $this->redirect('target/exam', array('tid' => $id));
            exit();
        }
    }

    public function exam() {
        $tid = I('tid');
        $dt = I('dt'); //题目序号
        $dt = empty($dt) ? 1 : $dt;
        $dx = $dt - 1;
        //获取当前比赛
        $target = $this->targetModel->where('competition_id = %d ', $tid)->find();
        $this->check($target);
        $this->startVM($tid, 1);
        //获取当前比赛的题
        $quest_array = json_decode($target['questions'], true);

        $exam = array();
        $type = $quest_array[$dx]['type'];

        if ($type == ConstModel::QUEST_TYPE_SINGLE) {
            $examModel = M('exam_single');
            $exam = $examModel->where('single_id = %d', (int) $quest_array[$dx]['id'])->find();
        } else if ($type == ConstModel::QUEST_TYPE_MUTI) {
            $examModel = M('exam_multiple');
            $exam = $examModel->where('multiple_id = %d', (int) $quest_array[$dx]['id'])->find();
        } else if ($type == ConstModel::QUEST_TYPE_TARGET) {
            $examModel = M('exam_target');
            $exam = $examModel->where('targetcomp_id = %d', (int) $quest_array[$dx]['id'])->find();
        } else {
            $this->redirect('index/to404');
        }

        if (empty($exam)) {
            $this->redirect('index/to404');
        }

        //获取当前的成绩
        $scoreModel = D('Target_score');
        $score = $scoreModel->where('user_id = %d and competition_id = %d', $this->uid, $tid)->find();

        //获取当前的详细成绩
        $scoreDetailModel = D('Target_score_detail');
        $detail_array = $scoreDetailModel->where('user_id = %d and score_id = %d', $this->uid, $score['score_id'])->select();

        $right = '';
        $wrong = '';
        foreach ($detail_array as $detail) {
            if ($detail['status'] == ConstModel::DETAIL_STATUS_RIGHT) {
                $right .= empty($right) ? $detail['question_sequence'] : ',' . $detail['question_sequence'];
            } else if ($detail['status'] == ConstModel::DETAIL_STATUS_WRONG) {
                $wrong .= empty($wrong) ? $detail['question_sequence'] : ',' . $detail['question_sequence'];
            }
        }

        $vmDataModel = new VmDataModel();
        $vminfo = $vmDataModel->where('compid = %d and comptype = %d and teamid = %d', $tid, 1, $this->uid)->find();
        $vms = explode(',', $vminfo['vms']);
        $vms_array = array();
        foreach ($vms as $vm) {
            $v['name'] = $vm;
            $v['url'] = $vmDataModel->getVmUrl($vm);
            $vms_array[] = $v;
        }

        session('vms', $vms_array);
        $this->assign('target', $target);
        $this->assign('exam', $exam);
        $this->assign('type', $type);
        $this->assign('score', $score['score_id']);
        $this->assign('now', $dt);
        $this->assign('wrong', $wrong);
        $this->assign('right', $right);
        $this->assign('vms_array', $vms_array);
        $this->display();
    }

    public function score() {
        $id = $this->_param('tid');
        $target = $this->targetModel->where('competition_id = %d', $id)->find();
        $targetScoreModel = D('Target_score');
        $score_array = $targetScoreModel->where('competition_id = %d', $id)->order('score_get desc,lastupdate')->select();
        $userModel = D('User');
        $user_array = $userModel->where('user_id in (%s) and status  != 0', $target['members'])->select();
        $user_array = ConstModel::changeKeys($user_array, 'user_id');
        foreach ($score_array as &$score) {
            if ($score['user_id'] == $user_array[$score['user_id']]['user_id']) {
                $score['campus_name'] = $user_array[$score['user_id']]['campus_name'];
                $score['major_name'] = $user_array[$score['user_id']]['major_name'];
            }
        }
        $this->assign('target', $target);
        $this->assign('score_array', $score_array);
        $this->display();
    }

    public function judge() {
        $keys = $_POST['key'];
        $verify = I('verify');
        $tid = I('target');
        $eid = I('exam');
        $sid = I('score');
        $type = I('type');
        $dt = I('dt');

        //获取当前比赛
        $target = $this->targetModel->where('competition_id = %d ', $tid)->find();
        $quest_array = json_decode($target['questions'], true);

        if (md5($verify) != session('verify')) {
            $this->ajaxReturn('', '', 0);
        }
        $scoreModel = D('Target_score');
        $scoreDetailModel = D('Target_score_detail');
        //初始化当前人的成绩
        $score = $scoreModel->where('user_id = %d and score_id = %d', $this->uid, $sid)->find();
        $score_data = array(
            'score_id' => $sid,
        );
        $detail_data = array(
            'user_id' => $this->uid,
            'score_id' => $sid,
            'question_type' => $type,
            'question_id' => $eid,
        );
        $detail_data = $scoreDetailModel->where($detail_data)->find();
        $flag = false;
        if (!empty($keys)) {
            if ($type == ConstModel::QUEST_TYPE_SINGLE) {
                $examModel = M('exam_single');
                $exam = $examModel->where('single_id = %d', $eid)->find();
                if ($keys == $exam['right_answer']) {
                    $flag = true;
                }
            } else if ($type == ConstModel::QUEST_TYPE_MUTI) {
                $examModel = M('exam_multiple');
                $exam = $examModel->where('multiple_id = %d', $eid)->find();
                $keys = implode(',', $keys);
                if ($keys == $exam['right_answers']) {
                    $flag = true;
                }
            } else {
                $examModel = M('exam_target');
                $exam = $examModel->where('targetcomp_id = %d', $eid)->find();
                if (strpos($exam['keys'], $keys) !== FALSE) {
                    $flag = true;
                }
            }
        }

        if ($detail_data['status'] == ConstModel::DETAIL_STATUS_WRONG) {
            if ($type != ConstModel::QUEST_TYPE_TARGET) {
                $this->ajaxReturn('', '', 3);
                exit();
            }
            if ($flag) {
                $detail_data['status'] = ConstModel::DETAIL_STATUS_RIGHT;
                $score_data['score_get'] = $score['score_get'] + $exam['score'];
                $score_data['right_num'] = $score['right_num'] + 1;
                $score_data['wrong_num'] = $score['wrong_num'] - 1;
            }
        } else if ($detail_data['status'] == ConstModel::DETAIL_STATUS_RIGHT) {
            if ($type != ConstModel::QUEST_TYPE_TARGET) {
                $this->ajaxReturn('', '', 3);
                exit();
            }
            if (!$flag) {
                $detail_data['status'] = ConstModel::DETAIL_STATUS_RIGHT;
                $score_data['score_get'] = $score['score_get'] - $exam['score'];
                $score_data['right_num'] = $score['right_num'] - 1;
                $score_data['wrong_num'] = $score['wrong_num'] + 1;
            }
        } else {
            if ($flag) {
                //答对了,且题未答完。
                $detail_data['status'] = ConstModel::DETAIL_STATUS_RIGHT;
                $score_data['score_get'] = $score['score_get'] + $exam['score'];
                $score_data['nodo_num'] = $score['nodo_num'] - 1;
                $score_data['right_num'] = $score['right_num'] + 1;
            } else {
                $detail_data['status'] = ConstModel::DETAIL_STATUS_WRONG;
                $score_data['nodo_num'] = $score['nodo_num'] - 1;
                $score_data['wrong_num'] = $score['wrong_num'] + 1;
            }
        }

        $scoreDetailModel->data($detail_data)->save();
        $scoreModel->data($score_data)->save();
        $flag ? $this->ajaxReturn($dt != count($quest_array), '', 1) : $this->ajaxReturn($dt != count($quest_array), '', 2);
    }

    protected function check($target) {
        $member_array = explode(',', $target['members']);
        if (!in_array($this->uid, $member_array)) {
            $this->redirect('target/score', array('tid', $target['competition_id']));
        }
    }

    protected function startVM($id, $type) {
        $vmDataModel = new VmDataModel();
        $vminfo = $vmDataModel->where('compid = %d and comptype = %d and teamid = 0', $id, $type)->select();
        foreach ($vminfo as $vms) {
            $vms = explode(',', $vms['vms']);
            foreach ($vms as $vm) {
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

    public function url($name) {
        include_once C('API');
        $url = '';
        try {
            $url = Api::init()->getUrlByVmName($name);
        } catch (Exception $e) {
            
        }
        return $url;
    }

    public function vm($name) {
        include_once C('API');
        try {
            if (!(Api::init()->runningState($name))) {
                Api::init()->start($name);
            }
        } catch (Exception $e) {
//            echo $e->getMessage();
        }
    }

    public function test() {
        include_once "/data1/www/virtzh/api.php";
        $s = App::init()->getUrlByVmName('att_xp_621467_20150908163419');
        var_dump($s);
        exit();
    }

}
