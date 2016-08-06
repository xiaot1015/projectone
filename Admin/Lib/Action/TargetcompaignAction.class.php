<?php

/**
 * 靶场比赛
 *
 */
class TargetcompaignAction extends AuthAction {

    public function index() {
        $this->getlist();
    }

    /**
     * 靶场比赛列表
     */
    public function getlist() {

        //$id = $this->_param("id");
        $competition_name = $this->_param("cname");

        $wherearr = array();
        if (!empty($competition_name)) {
            $wherearr['competition_name'] = array(0 => $competition_name, 1 => 'like');
            $this->assign("cname", $competition_name);
        }
        $where = "1=1 ";
        $model = M('target_competition');
        $where .= $this->_change_to_wherestr($wherearr);

        import("ORG.Util.Page");
        $count = $model->where($where)->count();
        $p = new Page($count, 20);
        $limit = $p->firstRow . ',' . $p->listRows;
        $page = $p->show();

        $list = $model->where($where)->order("competition_id desc")->limit($limit)->select();

        $this->assign("page", $page);
        $this->assign("list", $list);
        $this->display('list');
    }

    /**
     * 添加
     */
    public function add() {
        $id = $this->_param('id');
        $type = "add";
        if (!empty($id)) {
            $info = $this->getOneInfoById($id);
            $type = "edit";
            $this->assign("id", $info['competition_id']);
            $this->assign("info", $info);
        }

        $this->assign("step", "1");
        $this->assign("type", $type);
        $this->display();
    }

    /**
     * 根据ID 获取一条靶场比赛信息
     */
    private function getOneInfoById($id) {
        if (!empty($id)) {
            $model = M('target_competition');
            $where = sprintf("competition_id = %d", $id);
            $ret = $model->where($where)->find();
            return $ret;
        }
        return false;
    }

    /**
     * 编辑保存靶场比赛信息
     */
    public function edit() {
        $data = array();
        $id = $this->_param('id');
        $type = $this->_param('type');
        $step = $this->_param('step');
        if (empty($step))
            $step = 1;

        //	$data['status'] = isset($_POST['status'])?trim($_POST['status']):0;
        $data['status'] = 1;
        if ($step == 1) {   // ?????
            $data['competition_name'] = $this->_param("cname");
            $data['starttime'] = $this->_param("starttime");
            $data['endtime'] = $this->_param("endtime");
            $step = 2;
        } elseif ($step == 2) { // ?????
            $uids = rtrim($this->_param("uids"), ",");
            $tparr = explode(",", $uids);
            $num = count($tparr);
            $data['member_num'] = $num;
            $data['members'] = $uids;
            $step = 3;
        } elseif ($step == 3) { // ?????
            $qids = rtrim($this->_param("qids"), "@@");
            $tmp = explode("@@", $qids);
            $num = count($tmp);
            $newexams = array();
            $tmparr = array();
            foreach ($tmp as $val) {
                $t = array();
                $p = intval(stripos($val, "|"));
                $t['id'] = substr($val, 1, $p - 1);
                $newexams[] = substr($val, 1, $p - 1);
                $t['type'] = substr($val, 0, 1);
                $t['question'] = substr($val, $p + 1);
                $tmparr[] = $t;
            }
            $data['question_num'] = $num;
            $data['questions'] = json_encode($tmparr);
            $step = 4;
        } elseif ($step == 4) {
            $data['competition_note'] = htmlspecialchars($_POST['desc']);
            $data['status'] = 2;
            $step = 5;
        }

        $data['createtime'] = date('Y-m-d H:i:s');

        $model = M('target_competition');
        if (!empty($id) && $type == "edit") {
            $oldtargetinfo = $model->where("competition_id = %d", $id)->find();
            $oldusers = explode(",", $oldtargetinfo['members']);
            $tmpoldexams = json_decode($oldtargetinfo['questions'], true);
            $oldexams = array();
            foreach ($tmpoldexams as $tmpinfo) {
                $oldexams[] = $tmpinfo['id'];
            }
            $where = sprintf("competition_id = %d", $id);
            $ret = $model->where($where)->save($data);
        } else {
            $oldusers = array();
            $ret = $model->add($data);
            $id = $ret;
        }
        if ($step == 3) { // init score  and  vms
            //$this->initvmsuser($id, $oldusers, $tparr);
            if(!empty($oldusers)){
                $this->delTargetScoreByUid($id,$oldusers);
            }
        }
        if ($step == 4) {
            $this->initvmsexam($id, $oldexams, $newexams);
            $this->initScore($id);
        }
        if ($step == 5) {
            $this->initvmdata($id);
            //$this->success("编辑完成",U('Targetcompaign/getlist'));
            $this->redirect('Targetcompaign/getlist');
        }
        $this->nextStep($id, $step);
    }

    /**
     * 下一步
     */
    public function nextStep($id, $step) {
        $info = $this->getOneInfoById($id);

        if ($step == 2) {
            $sc_list = $this->getSchoolList();
            $class_list = $this->getClassList();
            $usrlist = $this->getPeopleList();
            $result = $list = $usrkvlist = array();

            if (!empty($usrlist)) {
                foreach ($usrlist as $value) {
                    $result[$value['class_id']][] = $value;
                    $usrkvlist[$value['user_id']] = $value['user_name'];
                }
            }

            foreach ($class_list as $value) {
                $list[$value['campus_id']][] = $value;
            }

            $this->assign("usrlist", $result);
            $this->assign("schoollist", $sc_list);
            $this->assign("classlist", $list);
            $this->assign("usrkvlist", $usrkvlist);

            if (!empty($info['members'])) {
                $plist = explode(",", $info['members']);
                $this->assign("plist", $plist);
            }
        } elseif ($step == 3) {
            $examlist = $this->getExamList();
            if (!empty($info['questions'])) {
                $tmplist = json_decode($info['questions'], true);
                $this->assign("qlist", $tmplist);
            }

            $this->assign("examlist", $examlist);
        } elseif ($step == 4) {
            $info['competition_note'] = html_entity_decode($info['competition_note']);
            //print_r($info['competition_note']);exit;
        }
        $this->assign("info", $info);
        $this->assign("type", 'edit');
        $this->assign("step", $step);
        $this->assign("id", $id);
        if ($step == 3) {
            $this->display("addexam");
        } elseif ($step == 4) {
            $this->display("addlast");
        } else {
            $this->display("add");
        }
    }

    /**
     * 删除
     */
    public function delete() {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        if (empty($id) || $id == "") {
            //todo	
            $this->ajaxReturn("empty id", "操作失败", 0);
        }
        $model = M("target_competition");
        $wheresql = "competition_id = %d";
        $where = sprintf($wheresql, $id);
        $ret = $model->where($where)->delete();
        // 删除vm
        $this->delvmsbycompid($id);
        $this->delTargetScore($id);
        if ($ret === false) { // delete failed
            $this->ajaxReturn("failed", "操作失败", 0);
        } else {
            $this->ajaxReturn("ok", "操作成功", 1);
        }
    }

    /**
     * 
     */
    protected function delvmsbycompid($compid) {
        $vmmodel = new Model('vmdata', null);
        $list = $vmmodel->where("compid = %d", $compid)->select();
        if (!empty($list)) {
            $vmsarr = array();
            foreach ($list as $val) {
                if (!empty($val['vms'])) {
                    $vmsarr = array_merge($vmsarr, explode(",", $val['vms']));
                }
            }

            if (!empty($vmsarr)) {
                include_once '/data1/www/virtzh/api.php';
                $vmapi = Api::init();
                foreach($vmsarr as $vm){
                    $vmapi->shutdown($vm);
                    sleep(3);
                }
                $vmapi->deleteVmByName($vmsarr);
            }
            $this->delvmdatabycompid($compid);
        }
    }

    /**
     *  delete data in table vmdata  by compid
     */
    protected function delvmdatabycompid($compid) {
        $vmmodel = new Model('vmdata', null);
        $vmmodel->where("compid=%d", $compid)->delete();
    }

    /**
     *  搜索人员
     */
    public function membersearch() {
        $model = M('user');
        $keyword = $this->_param("keyword");
        $where = sprintf("status = 2 and user_name like '%%%s%%'", $keyword);
        $list = $model->where($where)->select();
        $result = array();
        if (!empty($list)) {
            foreach ($list as $val) {
                $result[$val['user_id']] = $val;
            }
        }
        $this->ajaxReturn($result, "success", 1);
    }

    /**
     * 显示参赛人员
     */
    public function showmember() {
        $id = $this->_param("id");
        $info = $this->getOneInfoById($id);
        $usrlist = $this->getPeopleList();
        $result = $list = array();
        if (!empty($usrlist)) {
            foreach ($usrlist as $value) {
                $result[$value['user_id']] = $value;
            }
        }
        $plist = explode(",", $info['members']);
        foreach ($plist as $v) {
            $list[$v] = $result[$v];
        }
        $this->assign("list", $list);
        $this->display("memberlist");
    }

    /**
     * 显示选择的试题
     * Enter description here ...
     */
    public function showquestions() {
        $id = $this->_param("id");
        $info = $this->getOneInfoById($id);
        $bank = array('1' => "靶场题", 3 => "单选题", 4 => "多选题");
        $tmplist = json_decode($info['questions'], true);

        $this->assign("list", $tmplist);
        $this->assign("bank", $bank);
        $this->display("questionlist");
    }

    /**
     *  搜索题库
     */
    public function examsearch() {

        $type = $this->_param("examtype");
        $keyword = $this->_param("keyword");
        if ($type == 1) {
            $model = M('exam_target');
        } elseif ($type == 3) {
            $model = M('exam_single');
        } elseif ($type == 4) {
            $model = M('exam_multiple');
        }
        $where = sprintf("question like '%%%s%%'", $keyword);
        $list = $model->where($where)->select();
        $result = array();
        if (!empty($list)) {
            foreach ($list as $val) {
                $result[$val['user_id']] = $val;
            }
        }
        $this->ajaxReturn($result, "success", 1);
    }

    public function getExamList() {
        //$type = $this->_param("examtype");
        $t_model = M('exam_target');
        $s_model = M('exam_single');
        $m_model = M('exam_multiple');

        $tlist = $t_model->field("targetcomp_id as id,question,bank_type_id")->where("status = 0")->select();
        $slist = $s_model->field("single_id as id,question,bank_type_id")->where("status = 0")->select();
        $mlist = $m_model->field("multiple_id as id,question,bank_type_id")->where("status = 0")->select();
        $result = array();
        $result[1] = $tlist;
        $result[3] = $slist;
        $result[4] = $mlist;
        return $result;
    }

    // 获取学校列表
    public function getSchoolList() {
        $model = M('campus');
        $list = $model->select();
        $result = array();
        foreach ($list as $val) {
            $result[$val['campus_id']] = $val;
        }
        return $result;
    }

    //获取班级列表
    public function getClassList() {
        $model = M('class');
        return $model->select();
    }

    // 获取所有学生列表  根据班级区分
    public function getPeopleList() {
        $model = M('user');
        $list = $model->where("type=0 and status=2")->select();
        return $list;
    }

    // init score
    protected function initScore($tid) {
        $targetModel = M("target_competition");
        $target = $targetModel->where('competition_id = %d ', $tid)->find();
        $quest_array = json_decode($target['questions'], true);

        $userModel = M('User');
        $scoreModel = M('Target_score');
        $scoreDetailModel = M('Target_score_detail');
        //该比赛的所有的用户
        $user_array = $userModel->where('status=2 and user_id in (%s)', $target['members'])->select();
        foreach ($user_array as $user) {
            $score = $scoreModel->where('user_id = %d and competition_id = %d', $user['user_id'], $tid)->find();
            if (empty($score)) {
                //初始化一条信息
                $score = array(
                    'competition_id' => $target['competition_id'],
                    'competition_name' => $target['competition_name'],
                    'user_id' => $user['user_id'],
                    'user_name' => $user['user_name'],
                    'score_get' => 0,
                    'right_num' => 0,
                    'wrong_num' => 0,
                    'nodo_num' => $target['question_num'],
                    'createtime' => date('Y-m-d H:i:s'),
                );
                $score['score_id'] = $scoreModel->data($score)->add();
            }
            //获取当前的详细成绩
            $detail_array = $scoreDetailModel->where('user_id = %d and score_id = %d', $user['user_id'], $score['score_id'])->select();
            if (empty($detail_array)) {
                //初始化详细成绩
                $i = 0;
                foreach ($quest_array as $quest) {
                    $i ++;
                    $detail = array(
                        'user_id' => $user['user_id'],
                        'question_type' => $quest['type'],
                        'question_sequence' => $i,
                        'question_id' => $quest['id'],
                        'question_desc' => $quest['question'],
                        'status' => 0,
                        'createtime' => date('Y-m-d H:i:s'),
                        'score_id' => $score['score_id'],
                    );
                    $detail_array[] = $detail;
                }
                $scoreDetailModel->addAll($detail_array);
            }
        }
    }

    /* -------------------------------------------- */

    /**
     * 获取靶机列表
     */
    public function targetList() {
        $id = I('id');
        $vmmodel_m = new Model('vmdata', null);
        $result = $vmmodel_m->where("compid =%d and comptype=1", $id)->select();
        $targetmodel = M('target_competition');
        $targetinfo = $targetmodel->where('competition_id =%d', $id)->find();

        $questions = json_decode($targetinfo['questions'], true);
        $members = explode(",", $targetinfo['members']);
        $qvms = $mvms = '';
        $vmlist = array();
        include_once '/data1/www/virtzh/api.php';
        $vmapi = Api::init();
        $vmmodel = D("Vm");
        // diao yong jie kou huo qu chuang jian wan cheng de  vms 
        $qvminfo = $vmapi->getVmNameByMatch($id, 1);
        $vmlisturl = $userlist = array();
        foreach ($qvminfo as $qvm) {
            $qvms .= $qvm['vmname'] . ",";
            $vmlist[] = $qvm['vmname'];
            $userlist[$qvm['vmname']] = $targetinfo['competition_name'];
            $vmlisturl[$qvm['vmname']] = $vmapi->getUrlByVmName($qvm['vmname']);
        }
        $qvms = rtrim($qvms, ",");
        $vmmodel->addVm($id, 1, $qvms);
        // mei ge ren hui dou hui chuang jian yige vm 
        foreach ($members as $m) {
            $mvms = '';
            $mvminfo = $vmapi->getVmNameByMatch($id, 1, $m);
            $userinfo = M('user')->where("status =2 and user_id=%d",$m)->find();
            foreach ($mvminfo as $mvm) {
                $mvms .= $mvm['vmname'] . ",";
                $vmlist[] = $mvm['vmname'];
                $userlist[$mvm['vmname']] = $userinfo['user_name'];
                $vmlisturl[$mvm['vmname']] = $vmapi->getUrlByVmName($mvm['vmname']);
            }
            $mvms = rtrim($mvms, ",");
            $vmmodel->addVm($id, 1, $mvms, $m);
        }
        $this->assign("vmlist", $vmlist);
        $this->assign("userlist", $userlist);
        $this->assign("vmlisturl", $vmlisturl);
        $this->display('targetlist');
    }
    /**
    *  insert data into table vmdata
    */
    protected function initvmdata($id) {
        sleep(3);
        //$vmmodel_m = new Model('vmdata', null);
        //$result = $vmmodel_m->where("compid =%d and comptype=1", $id)->select();
        $targetmodel = M('target_competition');
        $targetinfo = $targetmodel->where('competition_id =%d', $id)->find();

        $questions = json_decode($targetinfo['questions'], true);
        $members = explode(",", $targetinfo['members']);
        $qvms = $mvms = '';
        //$vmlist = array();
        include_once '/data1/www/virtzh/api.php';
        $vmapi = Api::init();
        $vmmodel = D("Vm");
        // diao yong jie kou huo qu chuang jian wan cheng de  vms 
        $qvminfo = $vmapi->getVmNameByMatch($id, 1);
        //$vmlisturl = $userlist = array();
        foreach ($qvminfo as $qvm) {
            $qvms .= $qvm['vmname'] . ",";
            //$vmlist[] = $qvm['vmname'];
            //$userlist[$qvm['vmname']] = $targetinfo['competition_name'];
            //$vmlisturl[$qvm['vmname']] = $vmapi->getUrlByVmName($qvm['vmname']);
        }
        $qvms = rtrim($qvms, ",");
        $vmmodel->addVm($id, 1, $qvms);
        // mei ge ren hui dou hui chuang jian yige vm 
        foreach ($members as $m) {
            $mvms = '';
            $mvminfo = $vmapi->getVmNameByMatch($id, 1, $m);
            // $userinfo = M('user')->where("status =2 and user_id=%d",$m)->find();
            foreach ($mvminfo as $mvm) {
                $mvms .= $mvm['vmname'] . ",";
                // $vmlist[] = $mvm['vmname'];
                // $userlist[$mvm['vmname']] = $userinfo['user_name'];
                // $vmlisturl[$mvm['vmname']] = $vmapi->getUrlByVmName($mvm['vmname']);
            }
            $mvms = rtrim($mvms, ",");
            $vmmodel->addVm($id, 1, $mvms, $m);
        }
        // $this->assign("vmlist", $vmlist);
        // $this->assign("userlist", $userlist);
        // $this->assign("vmlisturl", $vmlisturl);
        // $this->display('targetlist');
    }
    // start virt
    public function startvirt() {
        include_once "/data1/www/virtzh/api.php";
        $id = I("id");
        try {
            Api::init()->start($id);
            $this->ajaxReturn("ok", "success", 1);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->ajaxReturn("error", "failed", 0);
        }
    }

    // shutdown  virt
    public function shutdownvirt() {
        include_once "/data1/www/virtzh/api.php";
        $id = I("id");
        try {
            Api::init()->shutdown($id);
            $this->ajaxReturn("ok", "success", 1);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->ajaxReturn("error", "failed", 0);
        }
    }

    // recover virt
    public function recovervirt() {
        include_once "/data1/www/virtzh/api.php";
        $id = I("id");
        try {
            Api::init()->recoverVmByBackup($id);
            $this->ajaxReturn("ok", "success", 1);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->ajaxReturn("error", "failed", 0);
        }
    }

    /** init vmdata info
     * 初始化VMDATA    靶场比赛添加的时候 
     *
     */
    protected function initvmsexam($compid, $oldexams, $newexams) {
        $model = M("target_competition");
        $exam_model = M("exam_target");
        //$compinfo = $model->where("competition_id = %d",$compid)->find();
        //$questions = json_decode($compinfo['questions']);
        //$inexams = array_intersect($oldexams, $newexams);
        //$toadd = array_diff($inexams, $newexams);
        //$todelete = array_diff($inexams, $oldexams);

        // get data array
        $number = 1;
        $comptype = 1;
        // add vms api 
        include_once "/data1/www/virtzh/api.php";
        $vmapi = Api::init();
        $baselist = $vmapi->getTemplate();
        $name = $baselist[1];
        try {
            // delete old vms
            $vmexam = $vmapi->getVmNameByMatch($compid, $comptype);
            if (!empty($vmexam)) {
                $oldvmarr = array();
                foreach ($vmexam as $oldvm) {
                    $oldvmarr[] = $oldvm['vmname'];
                    $vmapi->shutdown($oldvm['vmname']);
                    sleep(3);
                }
                if(!empty($oldvmarr)) $vmapi->deleteVmByName($oldvmarr);
            }
            // add new vms
            foreach ($newexams as $new) {
                $examinfo = $exam_model->where("targetcomp_id=%d", $new)->find();
                if (!empty($examinfo)) {
                    $vmapi->addIncrement($examinfo['target_id'], $number, $compid, $comptype);
                }
            }
            // shutdown these new vms
            $vmexam = $vmapi->getVmNameByMatch($compid, $comptype);
            if (!empty($vmexam)) {
                $oldvmarr = array();
                foreach ($vmexam as $oldvm) {
                    $vmapi->shutdown($oldvm['vmname']);
                    sleep(3);
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /** init vmdata info
     * 初始化VMDATA   在点靶机管理的时候  初始化 人员的靶机信息
     *
     */
    protected function initvmsuser($compid, $oldusers, $newusers) {
        $model = M("target_competition");
        //compinfo = $model->where("competition_id = %d",$compid)->find();
        //$questions = json_decode($compinfo['questions']);
        //$members = explode(",", $compinfo['members']);
        //$inusers = array_intersect($oldusers, $newusers);
        //$toadd = array_diff($inusers, $newusers);
        //$todelete = array_diff($inusers, $oldusers);

        $number = 1;
        $comptype = 1;
        // add vms api 
        include_once "/data1/www/virtzh/api.php";
        $vmapi = Api::init();
        //$baselist = $vmapi->getTemplate();
        $linux = C('linuxvm');
        $window = C('windowsvm');
        foreach ($oldusers as $dval) {
            try {
                $vmuser = $vmapi->getVmNameByMatch($compid, $comptype, $dval);
                if (!empty($vmuser)) {
                    $oldvmuserarr = array();
                    foreach ($vmuser as $oldvmuser) {
                        $oldvmuserarr[] = $oldvmuser['vmname'];
                        $vmapi->shutdown($oldvmuser['vmname']);
                        sleep(3);
                    }
                    if(!empty($oldvmuserarr)) $vmapi->deleteVmByName($oldvmuserarr);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        foreach ($newusers as $user) {
            try {
                $vmuser = $vmapi->getVmNameByMatch($compid, $comptype, $user);
                if (empty($vmuser)) {
                    $vmapi->addIncrement($linux, $number, $compid, $comptype, $user);
                    $vmapi->addIncrement($window, $number, $compid, $comptype, $user);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        // shutdown news vms 
        foreach ($newusers as $nval) {
            try {
                $vmuser = $vmapi->getVmNameByMatch($compid, $comptype, $nval);
                if (!empty($vmuser)) {
                    $oldvmuserarr = array();
                    foreach ($vmuser as $oldvmuser) {
                        $vmapi->shutdown($oldvmuser['vmname']);
                        sleep(3);
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        $vmdatamodel = new Model("vmdata",null);
        $vmdatamodel->where("compid={$compid} and comptype = 1")->delete();
    }


    /**
     * 执行靶机命令
     */
    public function targetCommand() {
        
    }

    public function num_clicked_questions() {


        $this->display('num_clicked_questions');
    }

    public function num_clicked_humens() {
        $this->display('num_clicked_humens');
    }

    public function target_admin() {
        $this->display('target_admin');
    }

    /**
     * 将where 转换成where 语句
     */
    private function _change_to_wherestr($arr) {
        $pairs = array();
        $symbolarr = array('>', '<', '>=', '<=', '=', 'like');
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                if (in_array($v[1], $symbolarr)) {
                    if ($v[1] == 'like') {
                        $pairs[] = sprintf(" and `%s` %s '%%%s%%'", $k, $v[1], $v[0]);
                    } else {
                        $pairs[] = sprintf(" and `%s` %s '%s'", $k, $v[1], $v[0]);
                    }
                }
            } else {
                $pairs[] = sprintf(" and `%s`='%s'", $k, $v);
            }
        }
        return join(' ', $pairs);
    }
    /**
    * delete target competition score
    */
    private function delTargetScore($compid){

        if(!empty($compid)){
            $scoremodel = M('target_score');
            $scoredetailmodel = M('target_score_detail');
            $tmp_score_detail_sql = "delete from hy_target_score_detail where score_id in (select score_id from hy_target_score where competition_id = %d ) limit 100";
            $score_detail_sql = sprintf($tmp_score_detail_sql,$compid);
            $scoredetailmodel->query($score_detail_sql);
            $tmp_score_sql = "delete from hy_target_score where competition_id = %d limit 100";
            $score_sql = sprintf($tmp_score_sql,$compid);
            $scoremodel->query($score_sql);
        }

    }
    /**
    * delete target competition score
    */
    private function delTargetScoreByUid($compid,$uidarr){

        if(!empty($compid) && !empty($uidarr)){
            $scoremodel = M('target_score');
            $scoredetailmodel = M('target_score_detail');
            $tmp_score_detail_sql = "delete from hy_target_score_detail where score_id in (select score_id from hy_target_score where competition_id = %d and user_id in (%s) ) limit 100";
            $score_detail_sql = sprintf($tmp_score_detail_sql,$compid,implode(',',$uidarr));
            $scoredetailmodel->query($score_detail_sql);
            $tmp_score_sql = "delete from hy_target_score where competition_id = %d and user_id in (%s) limit 100";
            $score_sql = sprintf($tmp_score_sql,$compid,implode(',',$uidarr));
            $scoremodel->query($score_sql);
        }

    }

}
