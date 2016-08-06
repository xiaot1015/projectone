<?php

/**
 * Description of ExamResistcomp
 *
 * @author tingyu
 */
class ExamResistcompModel extends Model {

    public function getResistComp() {
        $sql = "select * from hy_exam_resistcomp";
        $res = $this->query($sql);
        dump($res);
        exit();
    }

    public function getResistCompQuestion() {
        
    }

}
