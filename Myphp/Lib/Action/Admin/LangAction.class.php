<?php
/**
 * 
 * Lang(多语言管理)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class LangAction extends AdminbaseAction {

	protected  $langpath,$lang;
    function _initialize()
    {	
		parent::_initialize();
		$this->langpath = LANG_PATH.LANG_NAME.'/';//当前语言包所在根目录

    }


	/**
	 * 添加语言操作。
	*/
	function insert() {


		// 设置语言目录。
		$lang_path =LANG_PATH.$_POST['mark'].'/';
		$r =dir_copy(LANG_PATH.'com/',$lang_path); 

		// 获取模型名称。
		$name = MODULE_NAME;
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 创建模型表
		$id = $model->add();
		
		// 如果创建成功，那么插入以下字段和值。
		if ($id !==false) {
			$db=D('');
			//$db =   DB::getInstance();
			$db->execute("INSERT INTO `".C( "DB_PREFIX" )."config`  (`varname`,`info`,`groupid`,`value`,`lang`) VALUES 
			('site_url','网站网址','2','','".$id."'),
			('logo','网站LOGO','2','./Public/Images/logo.gif','".$id."'),
			('site_email','站点邮箱','2','admin@myphp.com','".$id."'),
			('seo_title','网站标题','2','','".$id."'),
			('site_name','网站名称','2','','".$id."'),
			('seo_keywords','关键词','2','','".$id."'),
			('seo_description','网站简介','2','','".$id."'),
			('member_register','允许新会员注册','3','1','".$id."'),
			('member_emailcheck','新会员注册需要邮件验证','3','0','".$id."'),
			('member_registecheck','新会员注册需要审核','3','1','".$id."'),
			('member_login_verify','注册登陆开启验证码','3','1','".$id."'),
			('member_emailchecktpl','邮件认证模板','3','','".$id."'),
			('member_getpwdemaitpl','密码找回邮件内容','3','','".$id."')
			;");
			
			// 如果模型存在缓存文件，那么更新该模型的缓存文件。
			if(in_array($name,$this->cache_model)) savecache($name);		

			
			$jumpUrl = $_POST['forward'] ? $_POST['forward'] : U(MODULE_NAME.'/index');
			$this->assign ( 'jumpUrl',$jumpUrl );
			$this->success (L('add_ok'));
		} else {
			$this->error (L('add_error').': '.$model->getDbError());
		}
	}
	
	/**
	 * 删除语言操作。
	*/
	function delete() 
	{
		// 获取模型名称
        $name = MODULE_NAME;
		
		// 创建模型操作对象
        $model = M($name);
		
		// 获取主键
        $pk = $model->getPk();
		
		// 获取主键ID
        $id = $_REQUEST[$pk];
        if (isset($id)) {
		
			// 如果删除成功
			 if (false !== $model->delete($id)){
			 
			    // 删除配置模型中该语言的信息。
			 	M('config')->where("lang=" . $id)->delete();
				
				// 更新模型的缓存文件。
				if(in_array($name,$this->cache_model)) savecache($name);
				
				$this->assign('jumpUrl', U(MODULE_NAME . '/index'));
				$this->success(L('do_ok'));
            }else {
                $this->error(L('delete_error') . ': ' . $model->getDbError());
            }
		} else {
            $this->error(L('do_empty'));
        }
	}

	/**
	 * 设置语言包页面。
	*/
	function param()
	{
		$files = glob($this->langpath.'*');
		$lang_files=array();
		foreach($files as $key => $file) {
			//$filename = basename($file);
			$filename = pathinfo($file);
	 		$lang_files[$key]['filename'] = $filename['filename'];
			$lang_files[$key]['filepath'] = $file;
			$temp = explode('_',$lang_files[$key]['filename']);
			$lang_files[$key]['name'] = count($temp)>1 ? $temp[0].L('LANG_module') : L('LANG_common') ;
		}
		$this->assign ( 'id', $id );
		$this->assign ( 'lang', LANG_NAME );
		$this->assign ( 'files', $lang_files );
		$this->display();
		
	}
	
	/**
	 * 修改语言包页面。
	*/	
	function editparam()
	{
		$file=  $_REQUEST['file'];
		$value = F($file, $value='', $this->langpath); 
		$this->assign ( 'id', $id );
		$this->assign ( 'file', $file );
		$this->assign ( 'lang', LANG_NAME );
		$this->assign ( 'list', $value );
		$this->display();
	}

	/**
	 * 更新语言包操作。
	*/
	function updateparam()
	{
		$file=  $_REQUEST['file'];
		unset($_POST[C('TOKEN_NAME')]);

		foreach($_POST as $key=>$r){
			if($r)$data[strtoupper($key)]=$r;
		}
		$r = F($file,$data, $this->langpath); // 将当前添加的语言数据$data保存至语言包文件$file中，存储在当前语言包根目下。
		if($r){
			$this->success(L('do_ok'));
		}else{
			$this->error(L('add_error'));
		 }
	}
	
}
?>