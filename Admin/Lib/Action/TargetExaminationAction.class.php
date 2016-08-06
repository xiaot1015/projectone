<?php
/**
 * 选择题操作类
 * 包括多选题和单选题 
 */
class TargetExaminationAction extends AuthAction{
    private $targetbank = 1;	
	/**
	 * 题库总列表
	 */
	public function getAllList(){
		$this->display('alllist');
	}
	
	/**
	 * 跳转到添加页面
	 */
	public function add(){
		$id = $this->_param('id');
		$type = "add";
		if(!empty($id)){
			$info = $this->getTargetExaminationInfo($id);
			$answers = explode("@@", $info['keys']);
			$type = "edit";
			$info['targetcomp_desc'] = html_entity_decode($info['targetcomp_desc']);
			$info['background'] = html_entity_decode($info['background']);
			$this->assign("answers",$answers);
			$this->assign("info",$info);
		}
        $bankmodel = D('Bank');
        $targetbank = $bankmodel->getkvlistbyparentid($this->targetbank);
        include_once '/data1/www/virtzh/api.php';
        $vmlist = Api::init()->getTemplate();
        foreach($vmlist as $k=>$v){
            if($v == C('linuxvm') || $v == C('windowsvm')){
                unset($vmlist[$k]);
            }
        }
        $this->assign("vmlist",$vmlist);
        $this->assign("targetbank",$targetbank);
		$this->assign("type",$type);
		$this->display();
	}
	/**
	 * get info list   with search condition
	 */
	public function getlist(){
		$pagesize = 20;  // read form config file

		$id = $this->_param("id");
		$question = $this->_param("question");

		$wherearr = array();
		if(!empty($id)){
			$wherearr['targetcomp_id'] = array(0=>$id,1=>'=');
			$this->assign("id",$id);
		}
		if(!empty($question)){
			$wherearr['question'] = array(0=>$question,1=>'like');
			$this->assign("question",$question);
		}
		$where = "1=1 ";
		$where .= $this->_change_to_wherestr($wherearr);
		$model = M('exam_target');
		
		import("ORG.Util.Page");
		$count = $model->where($where)->count();
		$p = new Page($count,20);
		$limit = $p->firstRow.','.$p->listRows;
		$page = $p->show();
		
		$list = $model->where($where)->order("targetcomp_id desc")->limit($limit)->select();
		
        $bankmodel = D('Bank');
        $targetbank = $bankmodel->getkvlistbyparentid($this->targetbank);
        $this->assign("targetbank",$targetbank);
		$this->assign("page",$page);
		$this->assign("list",$list);
		$this->display('list');
	}
	/**
	 * get one info by id 
	 */
	public function getTargetExaminationInfo($id){
		//$id = $this->_param("id");
		if(!empty($id)){
			$model = M('exam_target');
			$where = sprintf("targetcomp_id = %d",$id);
			$ret = $model->where($where)->find();
			return $ret;
		}
		return false;
	}
	/**
	 *  add one info 
	 */
	public function addTargetExamination(){
		$data = array();
		$data['bank_type_id'] = $this->_param("bank_id");
		$data['bank_type_name'] = $this->_param("bank_name");
		$data['targetcomp_desc'] = $this->_param("desc");
		$data['background'] = $this->_param("background");
		$data['question'] = $this->_param("question");
		$data['score'] = $this->_param("score");
		$data['status'] = $this->_param("status");
		$data['target_id'] = $this->_param("targetid");
		$data['createtime'] = time();
		
		$keys = $this->_param("keys");	// keys 有多个  需要处理
		$data['keys'] = $keys;
		$model = M('exam_target');
		$ret = $model->add($data);
	}
	/**
	 * edit info by id
	 */
	public function edit(){
		$data = array();
		$id = $this->_param('id');
		$type = $this->_param('type');
		$savetype = $this->_param('savetype');
		
		$data['bank_type_id'] = $this->_param("typeid");
		$data['targetcomp_desc'] = htmlspecialchars($_POST["desc"]);
		$data['background'] = htmlspecialchars($_POST["background"]);
		$data['question'] = $this->_param("question");
		$data['score'] = $this->_param("score");
		$data['target_id'] = $this->_param("targetid");
		
//		
//		$i = 1;
//		$keys = '';
//		while(isset($_POST['item'.$i])){
//			$key = $_POST['item'.$i];
//			$keys .= $key.",";
//			$i++;
//		}
		$keys  = $this->_param("ans");
		// keys 有多个  需要处理
		$data['keys'] = rtrim($keys,'@@');
		$data['createtime'] = date('Y-m-d H:i:s');
		//$data['status'] = $this->_param("status");
		$model = M('exam_target');
		if(!empty($id) && $type == 'edit'){
			$where = sprintf("targetcomp_id = %d",$id);
			$ret = $model->where($where)->save($data);
		}else{
			$ret = $model->add($data);
		}
		if($ret === false){
			$this->ajaxReturn("","操作失败",0);
		}else{
			if($savetype == 1){
				$this->ajaxReturn("../TargetExamination/add","操作成功",1);
			}else{
				$this->ajaxReturn("../TargetExamination/getlist","操作成功",1);
			}
		}
	}
	/**
	 * 删除靶场题
	 * @param int $id 
	 */
	public function delete(){
		$id = isset($_POST['id'])?$_POST['id']:'';
		if(empty($id) || $id == "" ){
			$this->ajaxReturn("empty id","操作失败",0);
		}
		$model = M("exam_target");
		$wheresql = "targetcomp_id = %d";
		$where = sprintf($wheresql,$id);
		$ret =	$model->where($where)->delete();
		if($ret === false){ // delete failed
			$this->ajaxReturn("failed","操作失败",0);
		}else{
			$this->ajaxReturn("ok","操作成功",1);
		}
	}
	/**
	 * 删除靶场题
	 * @param int $id 
	 */
	private function deletebyid($id){
		if(empty($id) || $id == "" ){
			return false;
		}
		$model = M("exam_target");
		$wheresql = "targetcomp_id = %d";
		$where = sprintf($wheresql,$id);
		$ret =	$model->where($where)->delete();
		if($ret === false){ // delete failed
			return false;
		}else{
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
