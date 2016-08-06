<?php
/**
 * 靶场比赛成绩
 * 成绩不能修改和删除  只能查看
 */
class TargetScoreAction extends Action{
	
	public function __construct(){
	
	}
	/**
	 * get info list   with search condition
	 */
	public function getTargetScoreList(){
		$pagesize = 20;  // read form config file

		$id = $this->_param("id");
		$compname = $this->_param("compname");
		$start_time = $this->_param("starttime");
		$end_time = $this->_param("endtime");
		$wherearr = array();
		if(!empty($id)){
			$wherearr['id'] = array(0=>$id,1=>'=');
			$this->assign("id",$id);
		}
		if(!empty($compname)){
			$wherearr['competition_name'] = array(0=>$compname,1=>'like');
			$this->assign("compname",$compname);
		}
		if(!empty($end_time) && !empty($start_time)){
			$wherearr['exam_time'] = array(0=>$start_time,1=>'in',2=>$end_time);
			$this->assign("starttime",$start_time);
			$this->assign("endtime",$end_time);
		}else{
			if(!empty($end_time)){
				$wherearr['exam_time'] = array(0=>$end_time,1=>"<=");
				$this->assign("endtime",$end_time);
			}			
			if(!empty($start_time)){
				$wherearr['exam_time'] = array($start_time,">=");
				$this->assign("starttime",$start_time);
			}
		}
		$page = !empty($this->_param("p"))?$this->_param("p"):1;
		$offset = ($page-1) * $pagesize;
		$where = "1=1 ";

		$model = M('target_score');
		$where .= $this->_change_to_wherestr($wherearr);
		$list = $model->where($where)->order("id desc")->limit("{$offset},{$pagesize}")->select();

		$this->assign("list",$list);
	}
	/**
	 * get one info by id 
	 */
	public function getTargetScoreInfo(){
		$id = $this->_param("id");
		if(!empty($id)){
			$model = M('target_score');
			$where = sprintf("score_id = %d",$id);
			$ret = $model->where($where)->find();
		}
	}
	/**
	 *  add one score info 
	 */
	public function addTargetScore(){
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
		
		$model = M('target_score');
		$ret = $model->add($data);
	}
	/**
	 *  add one score info 
	 *  @param array $dataarr 
	 *  @desc 开始创建 成绩详细信息时候 调用。自动生成一条 成绩记录  并返回其scoreid
	 */
	private function createOneTargetScore($dataarr){
		$data = array();
		$data['competition_id'] = $dataarr("competition_id");
		$data['competition_name'] = $dataarr("competition_name");
		$data['user_id'] = $dataarr("user_id");
		$data['user_name'] = $dataarr("user_name");
		$data['score_get'] = $dataarr("score_get");
		$data['right_num'] = $dataarr("right_num");
		$data['wrong_num'] = $dataarr("wrong_num");
		$data['nodo_num'] = $dataarr("nodo_num");
		$data['createtime'] = date('Y-m-d H:i:s');
		// 比赛时间 需要跟据  比赛id 获取  
		$data['exam_time'] = $exam_time;
		
		$model = M('target_score');
		$ret = $model->add($data);
		return $ret;
	}
	/**
	 * 更新成绩num  如   正确数   未做数  错误数
	 */
	public function updateScoreNum($scoreid,$score_get,$right_num,$wrong_num,$nodo_num){
		
		$arr['score_get'] = $score_get;
		$arr['right_num'] = $right_num;
		$arr['nodo_num'] = $nodo_num;
		$arr['wrong_num'] = $wrong_num;
		
		$model = M('target_score');
		$where = sprintf("1=1 and score_id = %d ",$scoreid);
		$ret = $model->where($where)->save($arr);
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
