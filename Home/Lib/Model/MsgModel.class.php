<?php

/**
 * Description of FightcompaignModel
 *
 * @author tony
 */
class MsgModel {

    private $fields = array(
        'title' => '',
        'type' => '',
        'introduction' => '',
        'auther' => '',
        'thumb_img' => '',
        'video_url' => '',
        'content' => '',
        'status' => '',
    );
    private $fieldsarr ;


    private function checkParams($data){
        if(empty($data)) return false;

        $tmp = array();
        foreach($data as $key => $val){
            if(isset($this->fields[$key])){
                $tmp[$key]  = $val;
            }
        }
        $tmp['ctime']  = date('Y-m-d H:i:s');
        $this->fieldsarr = $tmp;
        return true;
    }

    public function addMsg($data){

        if(!$this->checkParams($data))
            return false;
        $model = M('tb_msg');
        $tid = $model->add($this->fieldsarr);
        return $tid;
    }

    public function updateMsgById($data,$id){
        if(!$this->checkParams($data))
            return false;
        $model = M('tb_msg');
        $tid = $model->where('id = %d',$id)->save($this->fieldsarr);
        return $tid;
    }

    public function getMsgInfo($id){
        if(empty($id))  return array();
        $ret = M('tb_msg')->where('id = %d ',$id )->find();
        if(!empty($ret)){
            $ret['content']  = html_entity_decode($ret['content']);
        }
        return $ret;
    }

}
