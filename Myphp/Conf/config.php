<?php
$database = require (RUNTIME_PATH.'config.php');
$sys_config =  include DATA_PATH.  'sys.config.php';
if(empty($sys_config)){$sys_config=array();$sys_config['LAYOUT_ON']=1;}
if($sys_config['URL_MODEL']) $RULES = include DATA_PATH.  'Routes.php';
$config	= array(
		'DEFAULT_THEME'		=> 'Default',
		'DEFAULT_CHARSET' => 'utf-8',
		'APP_GROUP_LIST' => 'Home,Admin,User,Wap',
		'DEFAULT_GROUP' =>'Home',
		'TMPL_FILE_DEPR' => '_',
		'DB_FIELDS_CACHE' => false,
		'DB_FIELDTYPE_CHECK' => true,
		'URL_ROUTER_ON' => true,
		'DEFAULT_LANG'   => 'cn',
		'LANG_SWITCH_ON'		=> true,
		'TAGLIB_LOAD' => true,
		'TAGLIB_PRE_LOAD' => 'Fcdrep',
		'TMPL_ACTION_ERROR' => APP_PATH.'Tpl/Exception/success.html',
		'TMPL_ACTION_SUCCESS' =>  APP_PATH.'Tpl/Exception/success.html',
		'COOKIE_PREFIX'=>'FCDREP_',
		'COOKIE_EXPIRE'=>'',
		'VAR_PAGE' => 'p',
		'LAYOUT_HOME_ON'=>$sys_config['LAYOUT_ON'],
		'URL_ROUTE_RULES' => $RULES,
		'TMPL_EXCEPTION_FILE' => APP_PATH.'/Lib/Action/Exception.php',
		'URL_404_REDIRECT' =>'/Public/404.html',
		'WECHAT'=>array(
            'token'=>'tokenaccesskey', //填写你设定的key
            'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey
            'appid'=>'wxdk1234567890', //填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'=>'xxxxxxxxxxxxxxxxxxx' //填写高级调用功能的密钥
		)
);
return array_merge($database, $config ,$sys_config);
?>
