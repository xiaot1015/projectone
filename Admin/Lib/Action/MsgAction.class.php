<?php
/**
 * 头条
 * 
 */
class MsgAction extends Action{
    const PAGESIZE = 20;
	private $msgtype = 0;
    private $optarr = array(
        '15' => '剪纸',
        '16' => '刺绣',
        '17' => '竹编',
        '18' => '草榴',
        '19' => '陶瓷',
        '20' => '泥面',
    );
    private $msgarr = array(
        0 => '动态',
        1 => '头条',
        2 => '展览',
        3 => '中奖',
        4 => '通知',
        '5' => '剪纸消息',
        '6' => '刺绣消息',
        '7' => '竹编消息',
        '8' => '草榴消息',
        '9' => '陶瓷消息',
        '10' => '泥面消息',
        '15' => '剪纸',
        '16' => '刺绣',
        '17' => '竹编',
        '18' => '草榴',
        '19' => '陶瓷',
        '20' => '泥面',
    );
	/**
	 * 跳转到添加页面
	 */
	public function add(){
		$id = $this->_param('id');
		$msgtype = $this->_param("msgtype");
		$type = "add";
		if(!empty($id)){
			$info = D('Msg')->getMsgInfo($id);
			$type = "edit";
			$this->assign("info",$info);
        }
        $this->assign("optlist",$this->optarr);
        $this->assign("type",$type);
		$this->display();
	}
	
	/**
	 * get info list   with search condition
	 */
	public function getList(){

		$id = $this->_param("id");
		$msgtype = $this->_param("msgtype");
		$wherearr = array();
		if(!empty($id)){
			$wherearr['id'] = array(0=>$id,1=>'=');
			$this->assign("id",$id);
		}
		if(!empty($question)){
			$wherearr['question'] = array(0=>$question,1=>'like');
			$this->assign("question",$question);
		}
        if($msgtype == 2){
            $wherearr['type'] = array(0=> implode(array_keys($this->optarr) ,","),1=>'in');
        }else{
            $wherearr['type'] = array(0=>$msgtype,1=>'=');
        }
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
		$this->assign("msgtype",$msgtype);
		$this->assign("msgarr",$this->msgarr);
		$this->assign("list",$list);
		$this->display('list');
	}

	public function updateMsg(){
        $id = $this->_param('id');
        $type = $this->_param('type');
        $title = $this->_param('title');
        $introduction = $this->_param('introduction');
        $content = $this->_param('content');
        $videourl = $this->_param('videourl');
        $imgurl = $this->_param('imgurl');
        $fujianstr = $this->_param('fujianstr');
        $msgtype = $this->_param('msgtype');
        $previewtype = $this->_param('previewtype');

		if(empty($id) && $type == 'edit'){
			$this->ajaxReturn("failed!","操作失败",0);
		}
        $data = array(
            'title' => $title ,
            'introduction' => $introduction,
            'video_url' => $videourl,
            'thumb_img' => $imgurl,
            'fujians' => trim($fujianstr,","),
            'content' =>  htmlspecialchars($_POST["content"]) ,
            'status ' => 0 ,
            'type' => empty($previewtype)?$msgtype:$previewtype ,
        );
		$model = D('Msg');
        if($type == 'edit' && !empty($id)){
		    $ret = $model->updateMsgById($data,$id);
        } else {
            $data['ctime'] = date('Y-m-d H:i:s');
            $ret = $model->addMsg($data);
        }
		if($ret === false){
			$this->ajaxReturn("failed!","操作失败",0);
		}else{
			$this->ajaxReturn("ok!","操作成功",1);
		}
	}
	public function del(){
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
		$symbolarr = array('>','<','>=','<=','=','like','in');
		foreach($arr as $k=>$v){
			if(is_array($v)){
				if(in_array($v[1],$symbolarr)){
					if($v[1] == 'like'){
						$pairs[] = sprintf(" and `%s` %s '%%%s%%'",$k,$v[1],$v[0]);	
					}elseif($v[1] == 'in'){
						$pairs[] = sprintf(" and `%s` %s (%s)",$k,$v[1],$v[0]);	
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
