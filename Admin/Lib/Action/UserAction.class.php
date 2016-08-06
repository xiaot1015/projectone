<?php

/**
 * Description of UserAction
 *
 * @author tony
 */
//引入UploadFile类
import('ORG.Net.UploadFile');

class UserAction extends AuthAction {

    protected $grade_model;
    protected $class_model;
    protected $Major_model;
    protected $Academy_model;
    protected $School_model;
    protected $Group_model;
    protected $Class_model;
    protected $School_list = array();
    protected $Academy_list = array();
    protected $Major_list = array();
    protected $grade_list = array();
    protected $group_list = array();
    protected $class_list = array();
    protected $school_fileds = 'campus_name,campus_id';
    protected $academy_fields = 'academy_id,academy_name';
    protected $major_fields = 'major_id,major_name';
    protected $grade_fields = 'grade_id,grade_name';
    protected $group_fields = 'team_id,team_name';
    protected $class_fields = 'class_id,class_name';
    protected $user_type = array(
        '0' => '学生',
        '1' => '教师',
        '2' => '超级管理员'
    );

    public function __construct() {
        parent::__construct();

        $sysModel = M('System_info');
        $sys = $sysModel->find();
        session('is_verify', $sys['isverify']);
        session('is_range', $sys['isrange']);

        $this->grade_model = new GradeModel();
        $this->class_model = new ClassModel();
        $this->Major_model = new MajorModel();
        $this->Academy_model = new AcademyModel();
        $this->School_model = new SchoolModel();
        $this->Group_model = new GroupModel();
        $this->Class_model = new ClassModel();
        $this->School_list = $this->School_model->get_list($this->school_fileds);
        $this->Academy_list = $this->Academy_model->get_list($this->academy_fields, 'campus_id=' . $this->School_list[0]['campus_id']);
        $this->grade_list = $this->grade_model->get_list();
        $this->group_list = $this->Group_model->get_list();
        $this->class_list = $this->Class_model->get_list();
        $this->assign('Academy_list', $this->Academy_list);
        $this->assign('School_list', $this->School_list);
        $this->assign('grade_list', $this->grade_list);
        $this->assign('group_list', $this->group_list);
        $this->assign('class_list', $this->class_list);
        $this->assign('user_type', $this->user_type);
    }

    public function index() {

        $where = array();
        if (isset($_POST['search'])) {
            $where = array(//搜索过滤条件
            );
            if (empty($_POST['grade'])) {
                unset($where['grade_id']);
            } else {
                $where['grade_id'] = array("like", "%" . $_POST['grade'] . "%");
            }
            if (empty($_POST['class'])) {
                unset($where['class_id']);
            } else {
                $where['class_id'] = array("like", "%" . $_POST['class'] . "%");
            }
            if (empty($_POST['group'])) {
                unset($where['team_id']);
            } else {
                $where['team_id'] = array("like", "%" . $_POST['group'] . "%");
            }
            if (empty($_POST['type'])) {
                unset($where['type']);
            } else {
                $where['type'] = array("eq", $_POST['type']);
            }
            if (empty($_POST['user_name'])) {
                unset($where['user_name']);
            } else {
                $where['user_name'] = array("like", "%" . $_POST['user_name'] . "%");
            }
        }
        $status = isset($_GET['status']) ? $_GET['status'] : '2';
        $where['status'] = $status;

        /**
         * 分页
         */
        $all = D('user')->where($where)->field('user_id')->select();
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $num = count($all);
        $per_page = C('PAGE_NUMBER');
        $offset = ($page - 1) * $per_page;
        $num_page = ceil($num / $per_page);
        $res = D('user')->where($where)->order('user_id desc')->limit($offset, $per_page)->select();

        $this->assign('list', $res);
        $this->assign('page', $page);
        $this->assign('num_page', $num_page);
        $this->assign('status', $status);
        $this->display('index');
    }

    public function add() {
        $model = new UserModel();
        if (isset($_POST['user_name'])) {
            if (empty($_POST['user_name'])) {
                $this->error('请填写用户名称', U('User/add'));
                exit();
            }
            if (empty($_POST['type']) && $_POST['type'] != '0') {
                $this->error('请填写用户性质', U('User/add'));
                exit();
            }
            if (empty($_POST['password'])) {
                $this->error('请填写用户密码', U('User/add'));
                exit();
            }

            $user = $model->where('user_name = %s and status != 0', $_POST['user_name'])->find();
            if (!empty($user)) {
                $this->error('用户名重复，请重新填写', U('User/add'));
                exit();
            }

            $data = array(
                'user_password' => md5($_POST['password']),
                'user_name' => $_POST['user_name'],
                'user_gender' => $_POST['user_gender'],
                'campus_id' => $_POST['school'],
                'academy_id' => $_POST['academy'],
                'major_id' => $_POST['major'],
                'grade_id' => $_POST['grade'],
                'class_id' => $_POST['class'],
                'team_id' => $_POST['group'],
                'campus_name' => $_POST['school_name'],
                'academy_name' => $_POST['academy_name'],
                'major_name' => $_POST['major_name'],
                'grade_name' => $_POST['grade_name'],
                'class_name' => $_POST['class_name'],
                'team_name' => $_POST['group_name'],
                'type' => $_POST['type'],
                'status' => 2,
            );
            $res = $model->save_data($data);
            if ($res) {
                $this->success('添加用户成功', U('User/index?status=2'));
                exit();
            } else {
                $this->error('请完整填写表单项', U('User/add'));
                exit();
            }
        }

        $this->display('add');
    }

    public function update() {
        if (isset($_GET['uid'])) {
            $res = D("user")->where('user_id=' . $_GET['uid'])->find();
        }
        if (isset($_POST['user_name'])) {
            if (empty($_POST['user_name'])) {
                $this->error('请填写用户名称', U('User/update?uid=' . $_POST['uid']));
                exit();
            }
            if ($_POST['type'] == "" || $_POST['type'] == null) {
                $this->error('请填写用户性质', U('User/update?uid=' . $_POST['uid']));
                exit();
            }
            if (empty($_POST['password'])) {
                $this->error('请填写用户密码', U('User/update?uid=' . $_POST['uid']));
                exit();
            }
            $data = array('user_password' => md5($_POST['password']),
                'user_name' => $_POST['user_name'],
                'user_gender' => $_POST['user_gender'],
                'campus_id' => $_POST['school'],
                'academy_id' => $_POST['academy'],
                'major_id' => $_POST['major'],
                'grade_id' => $_POST['grade'],
                'class_id' => $_POST['class'],
                'team_id' => $_POST['group'],
                'campus_name' => $_POST['school_name'],
                'academy_name' => $_POST['academy_name'],
                'major_name' => $_POST['major_name'],
                'grade_name' => $_POST['grade_name'],
                'class_name' => $_POST['class_name'],
                'team_name' => $_POST['group_name'],
                'type' => $_POST['type'],);

            $res = D('user')->where(array('user_id' => trim($_POST['uid'])))->save($data);
            if ($res) {
                $this->success("修改成功", U('User/index'));
                exit();
            } else {
                $this->error('填写错误，请检查', U('User/update?uid=' . $_POST['uid']));
                exit();
            }
        }

        $this->Major_list = $this->Major_model->get_list($this->major_fields, 'academy_id=' . $res['academy_id']);
        $this->Class_list = $this->Class_model->get_list($this->Class_fields, 'academy_id=' . $res['academy_id'] . ' and major_id=' . $res['major_id'] . ' and grade_id=' . $res['grade_id']);
        $this->assign('Class_list', $this->Class_list);
        $this->assign('Major_list', $this->Major_list);
        $this->assign('userinfo', $res);

        $this->display('update');
    }

    public function set_org() {

        if (isset($_GET['uids'])) {
            $this->assign('uids', trim($_GET['uids']));
        }
        if (isset($_POST['uids'])) {
            $res = D("user")->where("user_id in(" . $_POST['uids'] . ")")->save(array(
                'campus_id' => $_POST['school'],
                'academy_id' => $_POST['academy'],
                'major_id' => $_POST['major'],
                'grade_id' => $_POST['grade'],
                'class_id' => $_POST['class'],
                'team_id' => $_POST['group'],
                'campus_name' => $_POST['school_name'],
                'academy_name' => $_POST['academy_name'],
                'major_name' => $_POST['major_name'],
                'grade_name' => $_POST['grade_name'],
                'class_name' => $_POST['class_name'],
                'team_name' => $_POST['group_name'],
            ));

            $return = array(
                'status' => $res,
                'info' => '操作成功',
                'url' => U("index")
            );
            $this->ajaxReturn($return);
        }

        $this->Major_list = $this->Major_model->get_list($this->major_fields, 'academy_id=' . $res['academy_id']);
        $this->Class_list = $this->Class_model->get_list($this->Class_fields, 'academy_id=' . $res['academy_id'] . ' and major_id=' . $res['major_id'] . ' and grade_id=' . $res['grade_id']);
        $this->assign('Class_list', $this->Class_list);
        $this->assign('Major_list', $this->Major_list);
        $this->display('set_org');
    }

    public function ajax_for_academy() {
        if (isset($_POST['school_id'])) {

            $this->Academy_list = $this->Academy_model->get_list($this->academy_fields, 'campus_id=' . $_POST['school_id']);
            $this->ajaxReturn($this->Academy_list);
        }
    }

    public function ajax_for_major() {

        if (isset($_POST['academy_id'])) {

            $this->Major_list = $this->Major_model->get_list($this->major_fields, 'academy_id=' . $_POST['academy_id']);
            $this->ajaxReturn($this->Major_list);
        }
    }

    public function ajax_for_class() {
        $where = "";
        if (isset($_POST)) {

            $conditions = array();
            if (!empty($_POST['school_id'])) {
                $conditions['campus_id'] = $_POST['school_id'];
            }
            if (!empty($_POST['academy_id'])) {
                $conditions['academy_id'] = $_POST['academy_id'];
            }
            if (!empty($_POST['major_id'])) {
                $conditions['major_id'] = $_POST['major_id'];
            }
            if (!empty($_POST['grade_id'])) {
                $conditions['grade_id'] = $_POST['grade_id'];
            }
            $num = count($conditions);
            $i = 0;
            foreach ($conditions as $key => $val) {
                if ($i < $num - 1)
                    $where.=$key . "='" . $val . "' and ";
                else
                    $where.=$key . "='" . $val . "'";
                $i++;
            }
        }

        $this->class_list = $this->class_model->get_list($this->class_fields, $where);
        $this->ajaxReturn($this->class_list);
    }

    public function delete() {
        if (isset($_GET['ids'])) {
            $user = D('user');
            $scoreModel = D('Target_score');
            $targetModel = D('Target_competition');
            $scoreArray = $scoreModel->where('user_id in (%s)', $_GET['ids'])->select();
            $compIdArr = array();
            foreach ($scoreArray as $sc) {
                $compIdArr[] = $sc['competition_id'];
            }
            $compIds = implode(',', $compIdArr);
            $compArr = $targetModel->where('competition_id in (%s) and endtime > NOW()', $compIds)->select();
            if (!empty($compArr)) {
                $this->success("删除失败，有用户正在参与比赛，请等待比赛结束后删除！", $_SERVER['HTTP_REFERER']);
            }
            $res = $user->where('user_id in(' . $_GET['ids'] . ')')->save(array('status' => 0));
        }
        $this->success("删除成功", $_SERVER['HTTP_REFERER']);
        exit();
    }

    /**
     * @function 审核通过
     */
    public function change_status() {
        $return = array(
            'status' => 0,
            'info' => '操作成功'
        );

        if (empty($_GET['uids'])) {
            $res['info'] = '操作异常';
            $this->ajaxReturn($return);
        }
        $system_info = D("system_info")->find();
        if ($system_info['isverify'] == 0) {
            $this->error("操作失败,请在系统设置开启注册审核", U("User/index"));
            exit();
        }
        $res = D('user')->where("user_id in(" . trim($_GET['uids'] . ")"))->save(array('status' => 2));
        if ($res) {
            $this->success("操作成功", U("User/index"));
            exit();
        } else {
            $this->error("操作失败", U("User/index"));
            exit();
        }
    }

    public function get_teacher_list() {
        $teacher_list = D('user')->where(array(
                    'campus_id' => $_POST['school'],
                    'academy_id' => $_POST['academy'],
                    'major_id' => $_POST['major'],
                    'grade_id' => $_POST['grade'],
                    'type' => 1,
                    'status' => 2,
                ))->field('user_id,user_name')->select();

        $this->ajaxReturn($teacher_list);
    }

    public function get_class_member_list() {
        $teacher_list = D('user')->where(array(
                    'campus_id' => $_POST['school'],
                    'academy_id' => $_POST['academy'],
                    'major_id' => $_POST['major'],
                    'grade_id' => $_POST['grade'],
                    'class_id' => $_POST['class'],
                    'status' => 2,
                    'team_id' => array(
                        array('eq', '0'),
                        'or',
                        array('eq', ''),
                        'or',
                        array('exp', 'is Null'),
                        'or'
                    ),
                ))->field('user_id,user_name')->select();
        $this->ajaxReturn($teacher_list);
    }

    /**
     * 处理批量添加用户
     */
    public function upload_xls() {
        $return = array(
            "status" => 0, //上传失败
            "msg" => "",
        );
        if (!empty($_POST)) {
            $model = new ExceltoarrayModel();
            $file_info = $model->save_file();

            if ($file_info) {
                $res = $model->read_xml($file_info[0]);
                if ($res) {
                    $this->success("导入成功", U("User/index", array('status' => 2)));
                    exit();
                } else {
                    $this->error("导入失败", U("User/index", array('status' => 2)));
                    exit();
                }
            }
        }
        $this->display();
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

    public function get_user() {
        $where = array(//搜索过滤条件
        );
        if (empty($_POST['grade'])) {
            unset($where['grade_id']);
        } else {
            $where['grade_id'] = array("like", "%" . $_POST['grade'] . "%");
        }
        if (empty($_POST['class'])) {
            unset($where['class_id']);
        } else {
            $where['class_id'] = array("like", "%" . $_POST['class'] . "%");
        }
        if (empty($_POST['group'])) {
            unset($where['team_id']);
        } else {
            $where['team_id'] = array("like", "%" . $_POST['group'] . "%");
        }
        if (empty($_POST['type'])) {
            unset($where['type']);
        } else {
            $where['type'] = array("eq", $_POST['type']);
        }
        if (empty($_POST['user_name'])) {
            unset($where['user_name']);
        } else {
            $where['user_name'] = array("like", "%" . $_POST['user_name'] . "%");
        }
        $where['status'] = 2;
        $all = D('user')->where($where)->select();
        $res = array();
        foreach ($all as $user) {
            $u = array(
                'id' => $user['user_id'],
                'name' => $user['user_name'],
            );
            $res[] = $u;
        }
        $this->ajaxReturn($res, $where, 0);
    }

    public function test() {
        $this->error('aaaaaaaa');
        exit();
    }

}
