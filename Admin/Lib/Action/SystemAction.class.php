<?php

/**
 * 单选题题库
 */
class SystemAction extends AuthAction {

    /**
     * get info list   with search condition
     */
    public function index() {
        $model = M('system_info');
        $ret = $model->order('sys_id')->limit(0, 1)->find();
        $this->assign("info", $ret);
        $this->display("index");
    }

    /**
     * edit info by id
     */
    public function edit() {
        $id = $_GET['id'];

        $data['isregister'] = $this->_param("isregister");
        //$data['singlechoice_desc'] = htmlspecialchars($_POST["desc"]);
        //$data['background'] = htmlspecialchars($_POST["background"]);
        $data['name'] = $this->_param("name");
        $data['isverify'] = $this->_param("isverify");
        $data['isrange'] = $this->_param("isrange");
        $data['videonum'] = $this->_param("videonum");
        $data['video1'] = $this->_param("video1");
        $data['video2'] = $this->_param("video2");
        $data['video3'] = $this->_param("video3");
        $data['video4'] = $this->_param("video4");
        $data['rangeinfo'] = $this->_param("rangeinfo");
        $data['combatinfo'] = $this->_param("combatinfo");
        $model = M('system_info');
        if (!empty($id)) {
            $ret = $model->where("sys_id = %d", $id)->save($data);
        } else {
            $ret = $model->add($data);
        }
        $this->index();
    }

    /**
     *
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

}

?>
