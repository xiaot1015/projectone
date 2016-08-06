<?php

class UserAction extends IndexAction {

    public function index() {
        if (empty($this->uid)) {
            $this->redirect('index/to404');
        }
        $userModel = D('User');
        $user = $userModel->where('user_id = %d', $this->uid)->find();
        $this->assign('user', $user);
        $this->display();
    }

    public function edit() {
        $userModel = D('User');
        $user = $userModel->where('user_id = %d', $this->uid)->find();

        $campusModel = M('Campus');
        $gradeModel = M('Grade');
        $campus_array = $campusModel->select();
        $grade_array = $gradeModel->select();
        $this->assign('campus_array', $campus_array);
        $this->assign('grade_array', $grade_array);

        $this->assign('user', $user);
        $this->display();
    }

    public function save() {
        $userModel = D('User');
        $campusModel = M('Campus');
        $academiesModel = M('Academies');
        $majorModel = M('Major');
        $gradeModel = M('Grade');
        $classModel = M('Class');
        /*
         * 后期可优化
         */
        $campus = $campusModel->where('campus_id = %d', $_POST['campus'])->find();
        $acdemy = $academiesModel->where('academy_id = %d', $_POST['academy'])->find();
        $major = $majorModel->where('major_id = %d', $_POST['major'])->find();
        $grade = $gradeModel->where('grade_id = %d', $_POST['grade'])->find();
        $class = $classModel->where('class_id = %d', $_POST['class'])->find();

        $user = array(
            'user_id' => $_POST['uid'],
            'user_name' => $_POST['name'],
            'campus_id' => $campus['campus_id'],
            'campus_name' => $campus['campus_name'],
            'academy_id' => $acdemy['academy_id'],
            'academy_name' => $acdemy['academy_name'],
            'major_id' => $major['major_id'],
            'major_name' => $major['major_name'],
            'grade_id' => $grade['grade_id'],
            'grade_name' => $grade['grade_name'],
            'class_id' => $class['class_id'],
            'class_name' => $class['class_name'],
        );
        if (!empty($_POST['pwd'])) {
            $user['user_password'] = md5($_POST['pwd']);
        }
        //修改当前的session
        session('name', $_POST['name']);
        if (!$userModel->create($user)) {
            echo "修改出错了", $userModel->getError();
        } else {
            $userModel->save($user);
            echo "修改成功了<a href='" . __APP__ . "'>点击返回首页</a>";
            exit();
        }
    }

}
