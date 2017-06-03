<?php
/**
 * 
 * Empty (空模块)
 * 
 * MODULE_NAME: String 当前请求的模块名称
 * ACTION_NAME：String 当前请求的动作名称
 * $_REQUEST：Array 当前的请求参数
 * F(): Function 写入和读取缓存数据
 */
if(!defined("Myphp")) exit("Access Denied");
class EmptyAction extends Action
{	
	public function _empty()
	{
		//空操作 空模块
		if(MODULE_NAME!='Urlrule'){
			// 从缓存文件中获取模型，如果从配置文件中找不到请求的模型，则跳转至错误页面。
			$Mod = F('Mod');			
			if(!$Mod[MODULE_NAME]){ 
				throw_exception('404');
			}
		}

		$a=ACTION_NAME; //请求的动作
		$id =  intval($_REQUEST['id']);// 请求的内容id
		$catid = intval($_REQUEST['catid']);// 请求的类别id
		$moduleid =  intval($_REQUEST['moduleid']);// 请求的模块id
		if(MODULE_NAME=='Urlrule'){
			if(APP_LANG){
				$l =get_safe_replace($_REQUEST['l']);
				$lang= $l ? '_'.$l : '_'.C('DEFAULT_LANG');
			}
			$catdir =get_safe_replace($_REQUEST['catdir']);
			if($catdir){
				$Cat = F('Cat'.$lang);
				$catid = $catid ? $catid : $Cat[$catdir];
				unset($Cat);
			}
			if($_REQUEST['module']){
				$m=get_safe_replace($_REQUEST['module']);						
			}elseif($moduleid){
				$Module =F('Module');
				$m=$Module[$moduleid]['module'];
				unset($Module);
			}elseif($catid){
				$Category = F('Category'.$lang);
				$m=$Category[$catid]['module'];
				unset($Category);
			}else{
				throw_exception('404');
			}
			if($a=='index') $id=$catid;
		}else{				
			if(empty($id)){
				$Cat = F('Cat'.$lang);
				$id = $Cat[$id];
				unset($Cat);
			}
			$m=MODULE_NAME;	//请求的模型		
		}
		// 前台公共模块，执行前台公共动作。
		import('@.Action.Base');
		$bae=new BaseAction();
		// 判断请求的动作是否存在
		if(!method_exists($bae,$a)){
			throw_exception('404');
		}
		// 执行请求的动作,传入请求模型和查询参数，
		$bae->$a($id,$m);
	 
	}
}
?>