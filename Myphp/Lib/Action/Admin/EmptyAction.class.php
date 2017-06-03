<?php
/**
 * 
 * Empty (空模块)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class EmptyAction extends Action
{	
	public function _empty()
	{
		// 从缓存文件中获取模型，如果从配置文件中找不到请求的模型，则跳转至错误页面。
		$Mod = F('Mod');			
		if(!$Mod[MODULE_NAME]){ 
			throw_exception('404');
		}
		
		R('Admin/Content/'.ACTION_NAME);// 调用Admin分组下面的Content控制器的 ACTION_NAME 指定名称的方法
	}
}
?>