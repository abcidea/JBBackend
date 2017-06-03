<?php

/**
 * 
 * Config(系统配置)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class ConfigAction extends AdminbaseAction {
	
	protected $dao, $config,$seo_config ,$user_config, $site_config, $mail_config, $attach_config;
    function _initialize()
    {	
		parent::_initialize();
		$this->dao = M('Config');
		$this->assign($this->Config);

    }
	public function index() {
	  
		$this->config = $config = $this->dao->select();

		foreach($config as $key=>$r) {
			if($r['groupid']==1)$this->user_config[$r['varname']]=$r;
			if($r['groupid']==2){
				if(APP_LANG){
					if($r['lang']==LANG_ID) $this->site_config[$r['varname']]=$r;
				}else{
					$this->site_config[$r['varname']]=$r;
				}
			}
		}
		$this->assign('user_config',$this->user_config);
		$this->assign('site_config',$this->site_config);
		$this->display(); 
	}

	/**
	 * 系统参数设置页面。
	 *
	*/
	public function sys() {
		$sysconfig = F("sys.config");
		$Urlrule=array();
		foreach((array)$this->Urlrule as $key => $r){
			$urls=$r['showurlrule'].':::'.$r['listurlrule'];
			if(empty($r['ishtml']))$Urlrule[$urls]=L('URL_SHOW_URLRULE').":".$r['showexample'].", ".L('URL_LIST_URLRULE').":".$r['listexample'];
		}
		$this->assign('Urlrule',$Urlrule); 

		$this->assign('Lang',F('Lang')); 
		$this->assign('yesorno',array(0 => L('no'),1  => L('yes')));
		$this->assign('openarr',array(0 => L('close_select'),1  => L('open_select')));
		$this->assign('enablearr',array(0 => L('disable'),1  => L('enable')));
		$this->assign('urlmodelarr',array(0 => L('URL_MODEL0').'(m=module&a=action&id=1)',1  => L('URL_MODEL1').'(index.php/Index_index_id_1)',2 => L('URL_MODEL2').'(Index_index_id_1)'));
		$this->assign('readtypearr', array(0=>'readfile',1=> 'redirect'));
		$this->assign($sysconfig);
		$this->display();
	}
 
 
	/**
	 * 添加系统变量页面。
	 *
	*/
	public function add() {		 
		$this->display();
	}
	
	/**
	 * 删除系统变量操作。
	 *
	*/
	public function delete() {		
		
		// 模型名称
		$name = MODULE_NAME;
		
		// 模型操作对象
		$model = M ( $name );
		
		// 系统变量名称
		$id = $_REQUEST ['varname'];
		
		// 如果存在删除的系统变量名称，按么执行操作，否则提示一个缺少必要参数的错误提示。
		if (isset ( $id )) {
		
			// 删除系统变量。
			if(false!==$model->where("varname='$id'")->delete()){
			
				// 如果模型名称存在于与缓存模型数组中，那么更新这个模型的缓存。
				if(in_array($name,$this->cache_model)) savecache($name);
				
				$this->success(L('delete_ok'));
			}else{
				$this->error(L('delete_error').': '.$model->getDbError());
			}
		}else{
			$this->error (L('do_empty'));
		}
		 
	}

	public function insert() {
	
		// 插入数据库时的多语言检查，如果开启了多语言，则增加语言字段。
		if(APP_LANG)$_POST['lang']=LANG_ID; 

		// 如果创建失败，则提示错误信息。
		if (false === $this->dao->create ()) {
			$this->error ( $this->dao->getError () );
		}
		
		// 插入数据。
		$list=$this->dao->add ();
		
		// 更新配置缓存数据文件。
		savecache('Config');
		
		if ($list!==false) {
			$this->success (L('add_ok'));
		}else{
			$this->error (L('add_error'));
		}
	}

	/**
	 * 用户中心设置页面。
	 *
	*/	
	public function member() {
		
		if(APP_LANG)$where = ' and lang='.LANG_ID; 
		$config = $this->dao->where("groupid=3".$where)->select();
		$this->assign('member_config',$config);
		$this->display();
	}

	/**
	 * 附件配置页面。
	 *
	*/	
	public function attach(){
		$this->display();
	}
	
	/**
	 * 系统邮箱页面。
	 *
	*/
	public function mail() {
		$this->display();
	}
 
    /**
	 * 保存系统配置操作。
	 *
	*/
 	public function dosite() {

		// 如果URL规则为重写。
		if($_POST['URL_MODEL'] == 2){
		
			$sapi = php_sapi_name();
			
			// 设置apache环境的数组。
			$apacheArr = array('apache2handler','apache2filter','apache');
			
			// 检查是否为apache环境
			if(in_array($sapi,$apacheArr)){
				$result = apache_get_modules();  
				
				// 检查apache是否开启rewrite模式
				if(!in_array('mod_rewrite', $result)){  
					$this->error (L('do_url_rewrite_error'));  
				}
			}			
		}
		
		if(C('TOKEN_ON') && !$this->dao->autoCheckToken($_POST))$this->error (L('_TOKEN_ERROR_'));
		
		// 自动保存网站域名
		if(empty($_POST['site_url']) || ($_POST['site_url'] !=(get_domain_name())) ){
			$_POST['site_url'] = get_domain_name();
		}
		
		//如果获取到site_name参数与member_emailchecktpl，则设置查询条件为多语言。	
		if(APP_LANG && (isset($_POST['site_name']) || isset($_POST['member_emailchecktpl'])))$where = ' and lang='.LANG_ID;
		
		// 循环遍历获取到的post值，将键值保存至varname字段中，将值保存至value字段中
		foreach($_POST as $key=>$value){			
			$data['value']=$value;
			$f = $this->dao->where("varname='".$key."'".$where)->save($data);				 
		}
		
		// 更新保存模型的缓存。
		$f = savecache(MODULE_NAME);
		
		// 如果首页不生成html静态文件，那么删除存在的首页静态文件。
		if(isset($_POST['HOME_ISHTML']) && $_POST['HOME_ISHTML']=='')@unlink(__ROOT__.'index.html');
		
		// 如果重置了网站默认语言，那么更新一下url重写规则。
		if($_POST['DEFAULT_LANG'])routes_cache($_POST['URL_URLRULE']);

		if($f){
			$this->success(L('do_ok'));
		}else{
			$this->error (L('do_error'));
		}
	}

	/**
	 * 邮件测试发送操作。
	 *
	*/
	public function testmail(){		

		$mailto = $_GET['mail_to'];
		$message = 'CMS test mail';
		$r = sendmail($mailto,$this->Config['site_name'],$message,$_POST); 
				
		if($r==true){
			$this->ajaxReturn($r,L('mailsed_ok'),1);
		}else{
			$this->ajaxReturn(0,L('mailsed_error').$r,1);
		}
	}
}
?>