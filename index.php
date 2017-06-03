<?php
/**
 *
 * index(入口文件)
 *
 */
if(!is_file('./Cache/config.php'))header("location: ./Install");// 判断是否已安装
header("Content-type: text/html;charset=utf-8");// 设置html页面编码为utf-8
ini_set('memory_limit','32M');// 使用内存容量
error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('Myphp',true);// 非法访问控制
define('UPLOAD_PATH','./Uploads/');// 上传目录
define('VERSION','1.0.0');// CMS版本号
define('UPDATETIME','20110823');// CMS更新日期
define('APP_NAME','Myphp');// 项目名称
define('APP_PATH','./Myphp/');// 项目目录
define('APP_LANG',true);// 多语言
define('APP_DEBUG',true);// 调试或者部署模式
define('THINK_PATH','./Core/');// 框架目录
require_once('./phplog.php');// 加载日志扩展库
__log_start(); //记录开始运行时间
__log('日志记录入口');
error_log(__log_get_http_raw(), 3, "./Log/http.log");
require(THINK_PATH.'Core.php');// 框架入口
?>
