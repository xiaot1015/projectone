<?php
/**
 * 选择题操作类
 * 包括多选题和单选题 
 */
class ResistExaminationAction extends AuthAction{
	private $resistbank = 2;
	/**
	 * 跳转到添加页面
	 */
	public function add(){
		$id = $this->_param('id');
		$checknum = $this->_param('checknum');
		if(empty($checknum)) $checknum = 0;
		$type = "add";
		if(!empty($id)){
			
			
			$info = $this->getResistExaminationInfo($id);
			// 获取关卡列表  根据id 正序
			$list = $this->getCheckPointList($id);
			// 获取  下一个关卡的 信息
			//$checkinfo = $list[$checknum];
			//$checknum = $checknum + 1;
			//$answers = explode(",", $checkinfo['keys']);
		
			$type = "edit";
			$this->assign("list",$list);
			$this->assign("infovms",explode(",",trim($info['target_ids'],',')));
			//$this->assign("checkinfo",$checkinfo);
			//$this->assign("checknum",$checknum);
			//$this->assign("answers",$answers);
			$this->assign("info",$info);
        }
        $bankmodel = D('Bank');
        $resistbank = $bankmodel->getkvlistbyparentid($this->resistbank);
        include_once '/data1/www/virtzh/api.php';
        $vmlist = Api::init()->getTemplate();
        foreach($vmlist as $k=>$v){
            if($v == C('linuxvm') || $v == C('windowsvm')){
                unset($vmlist[$k]);
            }
        }
        $this->assign("vmlist",$vmlist);
        $this->assign("resistbank",$resistbank);
        $this->assign("type",$type);
		$this->display();
	}
	
	/**
	 * 跳转到添加页面
	 */
	public function next($nowcomp_id = ''){
		$nowcomp_id = $this->_param("id");
		//$checknum = 0;
		$type = "add";
        /*
		if(!empty($nowcomp_id)){
			$info = $this->getResistExaminationInfo($nowcomp_id);
			// 获取关卡列表  根据id 正序
			$list = $this->getCheckPointList($nowcomp_id);
			// 获取  下一个关卡的 信息
			//$checkinfo = $list[$checknum];
			//$checknum = $checknum + 1;
			//$answers = explode(",", $checkinfo['keys']);
		
			$type = "edit";
			$this->assign("list",$list);
			$this->assign("infovms",explode(",",trim($info['target_ids'],',')));
			//$this->assign("checkinfo",$checkinfo);
			//$this->assign("checknum",$checknum);
			//$this->assign("answers",$answers);
			$this->assign("info",$info);
		}
        */
        $bankmodel = D('Bank');
        $resistbank = $bankmodel->getkvlistbyparentid($this->resistbank);
        include_once '/data1/www/virtzh/api.php';
        $vmlist = Api::init()->getTemplate();
        foreach($vmlist as $k=>$v){
            if($v == C('linuxvm') || $v == C('windowsvm')){
                unset($vmlist[$k]);
            }
        }
        $this->assign("vmlist",$vmlist);
        $this->assign("resistbank",$resistbank);
		//$this->assign("checknum",$checknum);
		$this->assign("type",$type);
		$this->display('add');
	}
	/**
	 * 跳转到添加页面
	 */
	public function nextpoint($resistinfo=''){
		
		//$checknum = $this->_param('checknum');
		$id = $this->_param('id');
		//if(!empty($checknum)) $checknum = 0;
		
		$info = $this->getResistExaminationInfo($id);
		
		// 获取关卡列表  根据id 正序
		$list = $this->getCheckPointList($id);
		// 获取  下一个关卡的 信息
		//$checkinfo = !empty($list[$checknum])?$list[$checknum]:array();
		//$checknum = $checknum + 1;
		//$answers = explode(",", $checkinfo['keys']);
		
		$type = "add";
		
        $bankmodel = D('Bank');
        $resistbank = $bankmodel->getkvlistbyparentid($this->resistbank);
        $this->assign("resistbank",$resistbank);
		$this->assign("list",$list);
		include_once '/data1/www/virtzh/api.php';
        $vmlist = Api::init()->getTemplate();
        $this->assign("vmlist",$vmlist);
		//$this->assign("checkinfo",$checkinfo);
		//$this->assign("checknum",$checknum);
		//$this->assign("answers",$answers);
		$this->assign("infovms",explode(",",trim($info['target_ids'],',')));
		$this->assign("info",$info);
		$this->assign("type",$type);
		$this->display('add');
	}
	/**
	 * get info list   with search condition
	 */
	public function getList(){

		$id = $this->_param("id");
		$question = $this->_param("question");
		
		$wherearr = array();
		if(!empty($id)){
			$wherearr['id'] = array(0=>$id,1=>'=');
			$this->assign("id",$id);
		}
		if(!empty($question)){
			$wherearr['question'] = array(0=>$question,1=>'like');
			$this->assign("question",$question);
		}
		$where = "1=1 ";
		$model = M('exam_resistcomp');
		$where .= $this->_change_to_wherestr($wherearr);

		import("ORG.Util.Page");
		$count = $model->where($where)->count();
		$p = new Page($count,20);
		$limit = $p->firstRow.','.$p->listRows;
		$page = $p->show();
		
		$list = $model->where($where)->order("resistcomp_id desc")->limit($limit)->select();
        
        $bankmodel = D('Bank');
        $resistbank = $bankmodel->getkvlistbyparentid($this->resistbank);
        $this->assign("resistbank",$resistbank);

		$this->assign("page",$page);
		$this->assign("list",$list);
		$this->display('list');
	}
	/**
	 * get one info by id 
	 */
	public function getResistExaminationInfo($id){
		//$id = $this->_param("id");
		if(!empty($id)){
			$model = M('exam_resistcomp');
			$where = sprintf("resistcomp_id = %d",$id);
			$ret = $model->where($where)->find();
			return $ret;
		}
		return false;
	}
	/**
	 * get one info by id 
	 */
	public function getResistExaminationGateInfo($id){
		//$id = $this->_param("id");
		if(!empty($id)){
			$model = M('exam_resistcomp_de');
			$where = sprintf("de_id = %d",$id);
			$ret = $model->where($where)->find();
			return $ret;
		}
		return false;
	}
	/**
	 *  add one info 
	 */
	public function addResistExamination(){
		$data = array();
		$data['bank_type_id'] = $this->_param("typeid");
		$data['mulchoice_desc'] = $this->_param("desc");
		$data['background'] = $this->_param("background");
		$data['question'] = $this->_param("question");
		$data['score'] = $this->_param("score");
		$data['answers'] = $this->_param("answers");
		$data['right_answer'] = $this->_param("right_answer");
		$data['status'] = $this->_param("status");
		$data['createtime'] = time();
		$model = M('exam_resistcomp');
		$ret = $model->add($data);
    }
    /**
     * 根据比赛ID 获取改比赛所有管卡的分数
     */
    public function gettotalscorebycid($cid){
        $model = M('exam_resistcomp_de');
        $ret = $model->field("sum(score) as num")->where("resistcomp_id=%d",$cid)->find();
        return $ret['num'];
    }
	/**
	 * 修改对抗题
	 * @desc 修改对抗题      修改对抗题时会检查 对抗靶场 ，靶机 ， 和子类 是否有变化，否则不予修改。 关卡不检查
	 * edit info by id
	 */
	public function edit(){
		$data = array();
		$id = $this->_param('id');
		$type = $this->_param('type');
		$deid = $this->_param('deid');
		$savetype = $this->_param('savetype');
		
		$data['range_id'] = 0;
		$data['question'] = $this->_param('target');
		$data['bank_type_id'] = intval($this->_param("typeid"));
		$data['target_ids'] = $this->_param("targetids");
		$data['score'] = $this->_param("score");
		//$data['status'] = $this->_param("status");
		
		
		$checkdata['de_desc'] = htmlspecialchars($_POST["desc"]);
		$checkdata['background'] = htmlspecialchars($_POST["background"]);
		$checkdata['gatename'] = $this->_param("question");
		$checkdata['score'] = $this->_param("score");
		//$checkdata['status'] = $this->_param("status");
		
		$keys = $this->_param('ans');
		//if(!empty($keys))$keys = explode("@@", $keys);
		// keys 有多个  需要处理
		$checkdata['keys'] = rtrim($keys,"@@");
		
		
		$model = M('exam_resistcomp');
		
		if(!empty($id) && $type == 'edit' && !empty($deid)){
			$cmodel = M('exam_resistcomp_de');
			$cret = $cmodel->where("de_id={$deid} and resistcomp_id = {$id} ")->save($checkdata);
			if($cret !== false){
                $totalscore = $this->gettotalscorebycid($id);
                $data['score'] = $totalscore;
			    $where = sprintf("resistcomp_id = %d",$id);
                $ret = $model->where($where)->save($data);
				if($ret !== false){
					if($savetype == 1){
						$data['resistcomp_id'] = $id;
						$this->ajaxReturn('../ResistExamination/nextpoint?id='.$id,'操作成功',1);
					}elseif ($savetype == 3){
						$this->ajaxReturn('../ResistExamination/next?id='.$id,'操作成功',1);
					}elseif ($savetype == 2){	// 跳转到列表
						 $this->ajaxReturn('../ResistExamination/getlist','操作成功',1);
					}else{
						 $this->ajaxReturn('../ResistExamination/getlist','操作成功',1);
					}
				}else{
					$this->ajaxReturn("check failed!","关卡修改失败",3);
					//$this->error("关卡修改失败");
				}
			}else{
				$this->ajaxReturn("failed!","修改失败",3);
			}
		}else{
			if(!empty($id)){
                $where = sprintf("resistcomp_id = %d",$id);
				$ret = $model->where($where)->save($data);
				$ret = $id;
			}else{
		        $data['createtime'] = date('Y-m-d H:i:s');
				$ret = $model->add($data);
			}
			
			if($ret !== false && !empty($checkdata['gatename'])){
				$cmodel = M('exam_resistcomp_de');
                $checkdata['createtime'] = date('Y-m-d H:i:s');
				$checkdata['resistcomp_id'] = $ret;
                $cret = $cmodel->add($checkdata);
				if($cret !== false){
					// update score total
					$this->updatetotalscorebycid($ret);

					if($savetype == 1){
						$this->ajaxReturn('../ResistExamination/nextpoint?id='.$ret,'操作成功',1);
					}elseif ($savetype == 3){
						$this->ajaxReturn('../ResistExamination/next?id='.$ret,'操作成功',1);
					}elseif ($savetype == 2){	// 跳转到列表
						 $this->ajaxReturn('../ResistExamination/getlist','操作成功',1);
					}else{
						 $this->ajaxReturn('../ResistExamination/getlist','操作成功',1);
					}
				}else{
					$this->ajaxReturn("check failed!","关卡添加失败",3);
				}
			}else{
				$this->ajaxReturn("failed!","添加失败",3);
			}
		}
		
	}
	/**
	 * 修改对抗题gate
	 * @desc 修改对抗题      修改对抗题时会检查 对抗靶场 ，靶机 ， 和子类 是否有变化，否则不予修改。 关卡不检查
	 * edit info by id
	 */
	public function editgate(){
		$data = array();
		$id = $this->_param('id');
		$deid = $this->_param('deid');
		$type = $this->_param('type');
		
		$checkdata['de_desc'] = htmlspecialchars($_POST["desc"]);
		$checkdata['background'] = htmlspecialchars($_POST["background"]);
		$checkdata['gatename'] = $this->_param("question");
		$checkdata['score'] = $this->_param("score");
		//$checkdata['status'] = $this->_param("status");
		
		$keys = $this->_param('ans');
		//if(!empty($keys))$keys = explode("@@", $keys);
		// keys 有多个  需要处理
		$checkdata['keys'] = rtrim($keys,"@@");
		
		
		if(!empty($id) && $type == 'edit' && !empty($deid)){
			$cmodel = M('exam_resistcomp_de');
			$cret = $cmodel->where("de_id={$deid} and resistcomp_id = {$id} ")->save($checkdata);
			if($cret !== false){
				// update total score
				$this->updatetotalscorebycid($id);
				$this->ajaxReturn("success","操作成功!该窗口将在5秒后关闭.",1);
			}else{
				$this->ajaxReturn("failed!","修改失败",3);
			}
		}else{
			if(!empty($id)){
				$info = $this->getResistExaminationInfo($id);
				$gateinfo = $this->getResistExaminationGateInfo($deid);
				$answers = explode("@@", $gateinfo['keys']);
				$type = "edit";
				$gateinfo['de_desc'] = html_entity_decode($gateinfo['de_desc']);
				$gateinfo['background'] = html_entity_decode($gateinfo['background']);

				$this->assign("gateinfo",$gateinfo);
				$this->assign("answers",$answers);
				$this->assign("info",$info);
	        }
	        $bankmodel = D('Bank');
	        $resistbank = $bankmodel->getkvlistbyparentid($this->resistbank);
	        $this->assign("resistbank",$resistbank);
	        $this->assign("type",$type);
			$this->display();
		}
		
	}
	// update total score
	private function updatetotalscorebycid($id){
		$model = M('exam_resistcomp');
		$totalscore = $this->gettotalscorebycid($id);
        $scoredata['score'] = $totalscore;
        $where = sprintf("resistcomp_id = %d",$id);
		$ret_score = $model->where($where)->save($scoredata);
	}
	public function del(){
		$deid = $this->_param('deid');
		$model = M('exam_resistcomp_de');
		$deinfo = $model->where("de_id={$deid}")->find();
		$ret = $model->where("de_id={$deid}")->delete();
		if($ret === false){
			$this->ajaxReturn("failed!","操作失败",0);
		}else{
			$this->updatetotalscorebycid($deinfo['resistcomp_id']);
			$this->ajaxReturn("ok!","操作成功",1);
		}
	}
	/**
	 * 删除对抗题
	 * @param int $id 
	 */
	public function delete(){
		// resistcomp_id
		$id = isset($_POST['id'])?$_POST['id']:'';
		
		if(empty($id) || $id == "" ){
			//todo	
		}
		$model = M("exam_resistcomp");
		$wheresql = "resistcomp_id = %d";
		$where = sprintf($wheresql,$id);
		$ret =	$model->where($where)->delete();
		if($ret === false){ // delete failed
			$this->ajaxReturn("failed!","操作失败",0);
		}else{
			// 对抗题删除成功之后  删除  该对抗题中的所有的关卡
			$ret = $this->delOutpostByRid($id);
			if($ret === false){
				$this->ajaxReturn("failed!","关卡删除失败",0);
			}
			$this->ajaxReturn("ok!","操作成功",1);
		}	
	}
	/**
	 * 删除对抗题
	 * @param int $id 
	 */
	public function deletebyid($id){
		
		if(empty($id) || $id == "" ){
			//todo	
			return false;
		}
		$model = M("exam_resistcomp");
		$wheresql = "resistcomp_id = %d";
		$where = sprintf($wheresql,$id);
		$ret =	$model->where($where)->delete();
		if($ret === false){ // delete failed
			return false;
		}else{
			// 对抗题删除成功之后  删除  该对抗题中的所有的关卡
			$ret = $this->delOutpostByRid($id);
			if($ret === false){
				return false;
			}
			return true;
		}	
	}
	public function deleteids(){
		if(isset($_POST['ids']) && !empty($_POST['ids'])){
			$ids = $_POST['ids'];
			$idsarr = explode(",",trim($ids,","));
			foreach($idsarr as $val){
			    $this->deletebyid($val);
			}
			$this->ajaxReturn("ok","success",1);
		}else{
			$this->ajaxReturn("noids","failed",0);
		}
	}
	/**
	 * 根据对抗题id  获取该对抗题下所有的 关卡信息 
	 */
	private function getCheckPointList($resistcomp_id){
		$model = M('exam_resistcomp_de');
		$list = $model->where("resistcomp_id = {$resistcomp_id}")->order("de_id asc")->select();
		return $list;
	}
	/**
	 * 更新对抗题 总分
	 * @desc  每次添加或更新 对抗题下的关卡题时调用， 重新计算该对抗题下所有关卡的总分 
	 */
	private function updateResistScoreByRid($resistcomp_id){
		
		
	}
	/**
	 *  删除对抗题中的关卡      根据关卡ID 
	 */
    public function delOutpost(){
    	
    	
    }
    /**
     * 获取对抗题的下一个关卡信息。
     * 
     */
    public function getNextOutpostInResist($resistcomp_id=''){
    	$resistcomp_id = $this->_param('rid');
    	
    }
    
    /**
     *  根据对抗题id 删除该组关卡
     */
	private function delOutpostByRid($resistcomp_id){
		$model = M('exam_resistcomp_de');
		$ret = $model->where("resistcomp_id={$resistcomp_id}")->delete(); 	
		return $ret;	
	}
    /**
     *  添加一个新的关卡
     */
    private function addOneOutpost(Array $data){
    	
    	
    }
    /**
     *  根据关卡id 修改一个关卡
     */
	private function editOneOutpostById(Array $data,$id){
		
		
	}
	
	
	
	/**
	 * 将数组转换成where 模式字符串
	 */
	private function _change_to_wherestr($arr){
		$pairs = array();
		$symbolarr = array('>','<','>=','<=','=','like');
		foreach($arr as $k=>$v){
			if(is_array($v)){
				if(in_array($v[1],$symbolarr)){
					if($v[1] == 'like'){
						$pairs[] = sprintf(" and `%s` %s '%%%s%%'",$k,$v[1],$v[0]);	
					}else{
						$pairs[] = sprintf(" and `%s` %s '%s'",$k,$v[1],$v[0]);	
					}
				}	
			}else{
				$pairs[] = sprintf(" and `%s`='%s'",$k,$v);
			}
		}
		return join(' ',$pairs);
	}

}
?>
