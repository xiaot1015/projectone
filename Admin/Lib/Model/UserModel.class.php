<?php

/**
 * Description of UserModel
 *
 * @author tony
 */
class UserModel extends Model {

  protected $ta = 'user';
  protected $tableName = 'user';
  protected $trueTableName = 'hy_user';
  protected $_validate = array(
      array('user_name', '', '帐号名称已经存在！', 0, 'unique', 1), // 在新增的时候验证name字段是否唯一
      array('user_name', 60, '帐号名称长度不能超过60！', 0, 'length', 1), // 在新增的时候验证name字段是否唯一
      array('user_password', 'checkPwd', '密码格式不正确', 0, 'function'), // 自定义函数验证密码格式
  );

  public function save_data($data = array(), $type = 0, $pk = 0) {
    $this->_validationField($data, $this->_validate);
    if ($type == 1) {//修改
      $pk_name = $this->getPk();
      $where = $pk_name . "in(" . $pk . ")";
      return $this->where($where)->save($data);
    }

    if ($type == 0) {
      return $this->data($data)->add();
    }
  }

}
