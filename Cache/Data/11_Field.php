<?php
return array ( 'reg_name' => array ( 'id' => '118', 'moduleid' => '11', 'field' => 'reg_name', 'name' => '姓名', 'tips' => '', 'required' => '1', 'minlength' => '4', 'maxlength' => '20', 'pattern' => '0', 'errormsg' => '姓名超出范围', 'class' => '', 'type' => 'text', 'setup' => 'array (
  \'size\' => \'50\',
  \'default\' => \'\',
  \'ispassword\' => \'0\',
  \'fieldtype\' => \'varchar\',
)', 'ispost' => '0', 'unpostgroup' => '', 'listorder' => '0', 'status' => '1', 'issystem' => '0', ), 'reg_idcardno' => array ( 'id' => '119', 'moduleid' => '11', 'field' => 'reg_idcardno', 'name' => '身份证号', 'tips' => '', 'required' => '1', 'minlength' => '0', 'maxlength' => '0', 'pattern' => 'idcard', 'errormsg' => '身份证号码填写错误', 'class' => '', 'type' => 'text', 'setup' => 'array (
  \'size\' => \'50\',
  \'default\' => \'\',
  \'ispassword\' => \'0\',
  \'fieldtype\' => \'varchar\',
)', 'ispost' => '0', 'unpostgroup' => '', 'listorder' => '0', 'status' => '1', 'issystem' => '0', ), 'reg_mobile' => array ( 'id' => '120', 'moduleid' => '11', 'field' => 'reg_mobile', 'name' => '手机号码', 'tips' => '', 'required' => '1', 'minlength' => '0', 'maxlength' => '0', 'pattern' => 'mobile', 'errormsg' => '手机号码填写错误', 'class' => '', 'type' => 'text', 'setup' => 'array (
  \'size\' => \'50\',
  \'default\' => \'\',
  \'ispassword\' => \'0\',
  \'fieldtype\' => \'varchar\',
)', 'ispost' => '0', 'unpostgroup' => '', 'listorder' => '0', 'status' => '1', 'issystem' => '0', ), 'reg_remark' => array ( 'id' => '121', 'moduleid' => '11', 'field' => 'reg_remark', 'name' => '备注', 'tips' => '', 'required' => '0', 'minlength' => '0', 'maxlength' => '0', 'pattern' => '0', 'errormsg' => '', 'class' => '', 'type' => 'textarea', 'setup' => 'array (
  \'fieldtype\' => \'mediumtext\',
  \'rows\' => \'5\',
  \'cols\' => \'50\',
  \'default\' => \'\',
)', 'ispost' => '0', 'unpostgroup' => '', 'listorder' => '0', 'status' => '1', 'issystem' => '0', ), 'createtime' => array ( 'id' => '116', 'moduleid' => '11', 'field' => 'createtime', 'name' => '发布时间', 'tips' => '', 'required' => '1', 'minlength' => '0', 'maxlength' => '0', 'pattern' => '', 'errormsg' => '', 'class' => '', 'type' => 'datetime', 'setup' => '', 'ispost' => '0', 'unpostgroup' => '3,4', 'listorder' => '93', 'status' => '1', 'issystem' => '1', ), 'status' => array ( 'id' => '117', 'moduleid' => '11', 'field' => 'status', 'name' => '状态', 'tips' => '', 'required' => '0', 'minlength' => '0', 'maxlength' => '0', 'pattern' => '', 'errormsg' => '', 'class' => '', 'type' => 'radio', 'setup' => 'array (
  \'options\' => \'已审核|1
未审核|0\',
  \'fieldtype\' => \'tinyint\',
  \'numbertype\' => \'1\',
  \'labelwidth\' => \'75\',
  \'default\' => \'1\',
)', 'ispost' => '0', 'unpostgroup' => '3,4', 'listorder' => '99', 'status' => '1', 'issystem' => '1', ), ); ?>