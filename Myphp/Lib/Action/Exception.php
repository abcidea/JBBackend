<?php 
/**
 * 
 * 系统异常控制
 *
 */
if(!defined("Myphp")) exit("Access Denied");

// 调试模式下，显示详细的异常调试信息
if( isset($e['file']) || isset($e['line']) || isset($e['trace']) ) {

	require_once APP_PATH.'/Tpl/Exception/Exception.tpl';
}else{// 部署模式下，显示一个简单的404错误提示信息

	require_once APP_PATH.'/Tpl/Exception/Exception404.tpl';
}
