<?php
/**
 * 单选题题库
 */
class SingleExaminationAction extends AuthAction {

  private $singlebank=3;
    private $rightarr = array(1=>'A',2=>'B',3=>'C',4=>'D',5=>'E',6=>'F',7=>'G');
	/**
	 * turn to add page
	 */
	public function add(){
		$id = $this->_param('id');
		$type = "add";
		if(!empty($id)){
			$info = $this->getSingleExaminationInfo($id);
			$answers = json_decode($info['answers'],true);
			$type = "edit";
			$info['singlechoice_desc'] = html_entity_decode($info['singlechoice_desc']);
			$info['background'] = html_entity_decode($info['background']);

			$this->assign("rightarr",$this->rightarr);
			$this->assign("answers",$answers);
			$this->assign("info",$info);
		}
        $bankmodel = D('Bank');
        $banklist = $bankmodel->getkvlistbyparentid($this->singlebank);
        $this->assign("banklist",$banklist);
		$this->assign("type",$type);
		$this->display();
	}
	/**
	 * get info list   with search condition
	 */
	public function getlist(){

		$id = $this->_param("id");
		$question = $this->_param("question");

		$wherearr = array();
		if(!empty($id)){
			$wherearr['single_id'] = array(0=>$id,1=>'=');
			$this->assign("id",$id);
		}
		if(!empty($question)){
			$wherearr['question'] = array(0=>$question,1=>'like');
			$this->assign("question",$question);
		}
		$where = "1=1 ";
		$model = M('exam_single');
		$where .= $this->_change_to_wherestr($wherearr);

		import("ORG.Util.Page");
		$count = $model->where($where)->count();
		$p = new Page($count,20);
		$limit = $p->firstRow.','.$p->listRows;
		$page = $p->show();

		$list = $model->where($where)->order("single_id desc")->limit($limit)->select();

        $bankmodel = D('Bank');
        $banklist = $bankmodel->getkvlistbyparentid($this->singlebank);
        $this->assign("banklist",$banklist);
		$this->assign("page",$page);
		$this->assign("list",$list);
		$this->display('list');
	}
	/**
	 * get one info by id
	 */
	public function getSingleExaminationInfo($id){
		//$id = $this->_param("id");
		if(!empty($id)){
			$model = M('exam_single');
			$where = sprintf("single_id = %d",$id);
			$ret = $model->where($where)->find();
			return $ret;
		}
		return false;
	}
/**
	 * edit info by id
	 */
	public function edit(){
		$data = array();
		$id = $this->_param('id');
		$type = $this->_param('type');

		$rightans = $this->_param('ans');
		$data['bank_type_id'] = $this->_param("typeid");
		//$data['bank_type_name'] = $this->_param("bank_name");
		$data['singlechoice_desc'] = htmlspecialchars($_POST["desc"]);
		$data['background'] = htmlspecialchars($_POST["background"]);
		$data['question'] = $this->_param("question");
		$data['score'] = $this->_param("score");

		$i = 1;
		$bool = true;
		$ans_arr = array();
		$rightarr = $this->rightarr;
		while($bool === true){
			if(isset($_POST["item{$i}"])){
				$ans_arr[$i] = $_POST["item{$i}"];
				$i++;
			}else{
				$bool = false;
			}
		}
		$data['answers'] = json_encode($ans_arr);
		$data['right_answer'] = rtrim($this->_param('ans'),',');
		$data['status'] = isset($_POST['status'])?trim($_POST['status']):0;
		$data['createtime'] = date('Y-m-d H:i:s');
		$model = M('exam_single');
		if(!empty($id) && $type == "edit"){
			$where = sprintf("single_id = %d",$id);
			$ret = $model->where($where)->save($data);
		}else{
			$ret = $model->add($data);
		}
		if($ret !== false){
			$this->success("操作成功","../SingleExamination/getlist");
		}else{
			$this->error("操作失败","../SingleExamination/add");
		}
	}
	/**
	 *  add one info
	 */
	public function addSingleExamination(){
		$data = array();
		$data['bank_type_id'] = $this->_param("bank_id");
		$data['bank_type_name'] = $this->_param("bank_name");
		$data['singlechoice_desc'] = $this->_param("desc");
		$data['background'] = $this->_param("background");
		$data['question'] = $this->_param("question");
		$data['score'] = $this->_param("score");
		$data['answers'] = $this->_param("answers");
		$data['right_answer'] = $this->_param("right_answer");
		$data['status'] = $this->_param("status");
		$data['createtime'] = time();
		$model = M('exam_single');
		$ret = $model->add($data);
	}
	/**
	 * edit info by id
	 */
	public function editSingleExamination(){
		$data = array();
		$id = $this->_param('id');
		$data['bank_type_id'] = $this->_param("bank_id");
		$data['bank_type_name'] = $this->_param("bank_name");
		$data['singlechoice_desc'] = $this->_param("desc");
		$data['background'] = $this->_param("background");
		$data['question'] = $this->_param("question");
		$data['score'] = $this->_param("score");
		$data['answers'] = $this->_param("answers");
		$data['right_answer'] = $this->_param("right_answer");
		$data['status'] = $this->_param("status");

		if(!empty($id)){
			$where = sprintf("single_id = %d",$id);
			$model = M('exam_single');
			$ret = $model->where($where)->save($data);
		}
	}
	/**
	 * 删除
	 * @param int $id
	 */
	public function delete(){
		$id = isset($_POST['id'])?$_POST['id']:'';
		if(empty($id) || $id == "" ){
			$this->ajaxReturn("empty id","操作失败",0);
		}
		$model = M("exam_single");
		$wheresql = "single_id = %d";
		$where = sprintf($wheresql,$id);
		$ret =	$model->where($where)->delete();
		if($ret === false){ // delete failed
			$this->ajaxReturn("failed","操作失败",0);
		}else{
			$this->ajaxReturn("ok","操作成功",1);
		}
	}
	/**
	 * 删除
	 * @param int $id
	 */
	private function deletebyid($id){
		if(empty($id) || $id == "" ){
			return false;
		}
		$model = M("exam_single");
		$wheresql = "single_id = %d";
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
	 *
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
