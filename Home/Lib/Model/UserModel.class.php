<?php

/**
 * Description of UserModel
 *
 * @author tingyu
 */
class UserModel extends Model {

    protected $fileds = array(
        'user_id',
        'user_name',
        'user_password',
        'campus_id',
        'campus_name',
        'academy_id',
        'academy_name',
        'major_id',
        'major_name',
        'grade_id',
        'grade_name',
        'class_id',
        'class_name',
        'type',
        'status',
        '_pk' => 'user_id',
        '_autoinc' => true,
    );
    protected $_validate = array(
        array('user_name', 'require', '用户名不能为空！'),
        array('user_password', 'require', '用户密码不能为空！'),
        array('campus_id', 'require', '请选择学校'),
        array('academy_id', 'require', '请选择学院'),
        array('major_id', 'require', '请选择专业'),
        array('grade_id', 'require', '请选择年级'),
        array('class_id', 'require', '请选择班级'),
    );

}
