<?php
/**
 * 头条
 * 
 */
class ScrollImgAction extends Action{
    const PAGESIZE = 8;
	
	/**
	 * get info list   with search condition
	 */
	public function getList(){

		$id = $this->_param("id");
		$wherearr = array();
		$where = "1=1 ";
		$model = M('tb_scroll_img');
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

	public function del(){
		$id = $this->_param('id');
		$model = M('tb_scroll_img');
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
