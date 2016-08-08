<?php
/**
 * 头条
 * 
 */
class TopMsgAction extends Action{
    const PAGESIZE = 20;
	private $msgtype = 1;
	/**
	 * 跳转到添加页面
	 */
	private function add(){
		$id = $this->_param('id');
		$type = "add";
		if(!empty($id)){
			$info = D('Msg')->getMsgInfo($id);
			$type = "edit";
			$this->assign("info",$info);
        }
        $this->assign("type",$type);
		$this->display();
	}
	
	/**
	 * get info list   with search condition
	 */
	public function getList(){

		$id = $this->_param("id");
		$wherearr = array();
		if(!empty($id)){
			$wherearr['id'] = array(0=>$id,1=>'=');
			$this->assign("id",$id);
		}
		if(!empty($question)){
			$wherearr['question'] = array(0=>$question,1=>'like');
			$this->assign("question",$question);
		}
        $wherearr['type'] = array(0=>$this->msgtype,1=>'=');
		$where = "1=1 ";
		$model = M('tb_msg');
		$where .= $this->_change_to_wherestr($wherearr);

		import("ORG.Util.Page");
		$count = $model->where($where)->count();
		$p = new Page($count,self::PAGESIZE);
		$limit = $p->firstRow.','.$p->listRows;
		$page = $p->show();
		
		$list = $model->where($where)->order("id desc")->limit($limit)->select();
        
		$this->assign("page",$page);
		$this->assign("list",$list);
		$this->display('list');
	}

	private function updateMsg(){
        $id = $this->_param('id');
        $type = $this->_param('type');
        $title = $this->_param('title');
        $introduction = $this->_param('introduction');
        $content = $this->_param('content');
        $videourl = $this->_param('videourl');

		if(empty($id) && $type == 'edit'){
			$this->ajaxReturn("failed!","操作失败",0);
		}
        $data = array(
            'title' => $title ,
            'introduction' => $introduction,
            'video_url' => $videourl,
            'content' =>  htmlspecialchars($_POST["content"]) ,
            'status ' => 0 ,
            'type' => $this->msgtype ,
        );
		$model = D('Msg');
        if($type == 'edit' && !empty($id)){
		    $ret = $model->updateMsgById($data,$id);
        } else {
            $ret = $model->addMsg($data);
        }
		if($ret === false){
			$this->ajaxReturn("failed!","操作失败",0);
		}else{
			$this->ajaxReturn("ok!","操作成功",1);
		}
	}
	private function del(){
		$id = $this->_param('id');
		$model = M('tb_msg');
		$ret = $model->where("id = %d",$id)->delete();
		if($ret === false){
			$this->ajaxReturn("failed!","操作失败",0);
		}else{
			$this->ajaxReturn("ok!","操作成功",1);
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
