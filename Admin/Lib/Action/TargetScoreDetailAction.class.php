<?php
/**
 * 靶场比赛成绩详细
 * 成绩不能修改和删除  只能查看
 */
class ResistScoreDetailAction extends Action{
	
	public function __construct(){
	
	}
	/**
	 * get info list   with search condition
	 */
	public function getResistScoreDetailList(){

		$score_id = $this->_param("scoreid");
//		$user_id = $this->_param("userid");
//		$question_type = $this->_param("question_type");
//		$question_sequence = $this->_param("question_sequence");
//		$question_id = $this->_param("questionid");
//		$desc = $this->_param("question_desc");
		
		$wherearr = array();
		if(!empty($score_id)){
			$wherearr['score_id'] = array(0=>$score_id,1=>'=');
			//$this->assign("id",$id);
		}
		
		$page = !empty($this->_param("p"))?$this->_param("p"):1;
		$offset = ($page-1) * $pagesize;
		$where = "1=1 ";

		$model = M('resist_score_detail');
		$where .= $this->_change_to_wherestr($wherearr);
		$list = $model->where($where)->order("id desc")->limit("{$offset},{$pagesize}")->select();
		
		$this->assign("list",$list);
	}
	/**
	 * get one info by id 
	 */
	public function getResistScoreInfo(){
		$id = $this->_param("id");
		if(!empty($id)){
			$model = M('resist_score_detail');
			$where = sprintf("score_id = %d",$id);
			$ret = $model->where($where)->find();
		}
	}
	/**
	 *  add one score info 
	 */
	public function addResistScoreDetail(){
		$data = array();
		$data['competition_id'] = $this->_param("competition_id");
		$data['competition_name'] = $this->_param("competition_name");
		$data['user_id'] = $this->_param("user_id");
		$data['user_name'] = $this->_param("user_name");
		$data['score_get'] = $this->_param("score_get");
		$data['right_num'] = $this->_param("right_num");
		$data['wrong_num'] = $this->_param("wrong_num");
		$data['nodo_num'] = $this->_param("nodo_num");
		$data['createtime'] = date('Y-m-d H:i:s');
		// 比赛时间 需要跟据  比赛id 获取  
		$data['exam_time'] = $exam_time;
		
		$model = M('resist_score_detail');
		$ret = $model->add($data);
	}
	/**
	 * 更新靶场比赛 成绩详细
	 */
	public function updateResistScoreDetail(){
		$score_id = $this->_param("scoreid");
//		$user_id = $this->_param("userid");
//		$question_type = $this->_param("question_type");
//		$question_sequence = $this->_param("question_sequence");
//		$question_id = $this->_param("questionid");
//		$desc = $this->_param("question_desc");

		$model = M('resist_score_detail');
		$where = sprintf("1=1 and score_id = %d",$score_id);
		$ret = $model->where($where)->save($data);
	}
	
	
	/**
	 * 将数组转换成where 模式字符串
	 */
	private function _change_to_wherestr($arr){
		$pairs = array();
		$symbolarr = array('>','<','>=','<=','=','like','in','!=');
		foreach($arr as $k=>$v){
			if(is_array($v)){
				if(in_array($v[1],$symbolarr)){
					if($v[1] == 'like'){
						$pairs[] = sprintf(" and `%s` %s '%%%s%%'",$k,$v[1],$v[0]);	
					}else if($v[1] == 'in'){
						$pairs[] = sprintf(" and `%s` >= '%s' and `%s` <= '%s' ",$k,$v[0],$k,$v[2]);
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
