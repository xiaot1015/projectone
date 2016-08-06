<?php

class LoginAction extends Action {

    public function show() {
        $vmDataModel = new VmDataModel();
        $vmList = $vmDataModel->select();
        var_dump($vmDataModel->_sql());
        dump($vmList);
    }

    public function index() {
        $sysModel = M('System_info');
        $login_err = $this->_param('err');
        $sys = $sysModel->find();
        $ip = get_client_ip();
        $browser = ConstModel::getBrowser() + ConstModel::getBrowserVer();
        session('is_register', $sys['isregister']);
        session('is_verify', $sys['isverify']);
        session('is_range', $sys['isrange']);
        session('ip', $ip);
        session('browser', $browser);
        session('title', $sys['name']);
        
        $this->assign('is_register', $sys['isregister']);
        $this->assign('err', $login_err);
        $this->display();
    }

    public function login() {
        session_start();
        $name = $this->_param('username');
        $pwd = $this->_param('password');
        $userModel = M('User');
        $teamModel = M('Team');
        $sysModel = M('System_info');
        $sys = $sysModel->find();
        session('is_verify', $sys['is_verify']);
        session('logged_in', true);
        $user_array = $userModel->where("user_name = '%s' and status = 2", $name)->select();

        foreach ($user_array as $user) {
            if ($user['user_password'] == md5($pwd)) {
                //单人登录成功
                session('uid', $user['user_id']);
                session('name', $user['user_name']);
                session('type', ConstModel::LOGIN_TYPE_USER);
                //判断单人权限
                if ($user['type'] == ConstModel::USER_AUTH_ST) {
                    $this->redirect('index/index');
                } else {
                    header('location:' . C('ADMIN_URL'));
                    exit();
                }
            }
        }
        $team_array = $teamModel->where("team_name = '%s'", $name)->select();
        foreach ($team_array as $team) {
            if ($team['team_password'] == md5($pwd)) {
                //团队登录成功
                session('tid', $team['team_id']);
                session('name', $team['team_name']);
                session('type', ConstModel::LOGIN_TYPE_TEAM);
                $this->redirect('index/index');
                exit();
            }
        }
        $this->redirect('login/index', array('err' => 'true'), 0);
        exit();
    }

    public function regeist() {
        $campusModel = M('Campus');
        $gradeModel = M('Grade');
        $campus_array = $campusModel->select();
        $grade_array = $gradeModel->select();
        $this->assign('campus_array', $campus_array);
        $this->assign('grade_array', $grade_array);
        $this->display();
    }

    public function rege() {
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

        $user = $userModel->where('user_name = %s and status != 0', $_POST['name'])->find();
        if (!empty($user)) {
            $this->error('用户名重复，请重新填写', U('Login/regeist'));
            exit();
        }

        $user = array(
            'user_name' => $_POST['name'],
            'user_password' => md5($_POST['pwd']),
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
            'auth' => ConstModel::USER_AUTH_ST,
            'status' => ConstModel::USER_UN_VERIFY,
        );

        if (session('is_verify') == 0) {
            $user['status'] = ConstModel::USER_VERIFY_ACESS;
        }

        if (!$userModel->create($user)) {
            $this->error("出错了", $userModel->getError(), U('Login/regeist'));
            exit();
        } else {
            $userModel->add($user);
            echo "注册成功了" . ((session('is_verify') == 1) ? "请等待审核" : "") . "<a href='" . __APP__ . "'>点击返回首页</a>";
            exit();
        }
    }

    public function out() {
        $vms = session('vms');
        foreach ($vms as $key => $v) {
            try {
                $this->shutdownvirt($v['name']);
            } catch (Exception $e) {
                //echo $e->getMessage();
            }
        }
        session(NULL);
        $this->redirect('index/index');
        exit();
    }

    public function shutdownvirt($id) {
        include_once "/data1/www/virtzh/api.php";
        try {
            Api::init()->shutdown($id);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function get() {
        $campus_id = $this->_param('campus');
        $academy_id = $this->_param('academy');
        $major_id = $this->_param('major');

        $academiesModel = M('Academies');
        $majorModel = M('Major');
        $classModel = M('Class');

        $data = "";
        if (!empty($campus_id)) {
            $academy_array = $academiesModel->where('campus_id = %d', $campus_id)->field('academy_id,academy_name')->select();
            foreach ($academy_array as $academy) {
                $data .= "<option value='" . $academy['academy_id'] . "'>" . $academy['academy_name'] . "</option>";
            }
            $this->ajaxReturn($data, 'success', 1);
        } else if (!empty($academy_id)) {
            $major_array = $majorModel->where('academy_id = %d', $academy_id)->field('major_id, major_name')->select();
            foreach ($major_array as $major) {
                $data .= "<option value='" . $major['major_id'] . "'>" . $major['major_name'] . "</option>";
            }
            $this->ajaxReturn($data, 'success', 1);
        } else if (!empty($major_id)) {
            $class_array = $classModel->where('major_id = %d', $major_id)->field('class_id, class_name')->select();
            foreach ($class_array as $class) {
                $data .= "<option value='" . $class['class_id'] . "'>" . $class['class_name'] . "</option>";
            }
            $this->ajaxReturn($data, 'success', 1);
        }
    }

}
