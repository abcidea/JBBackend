<?php

/**
 * 
 * IndexAction.class.php(后台首页)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class IndexAction extends AdminbaseAction
{
	protected   $cache_model;
	function _initialize()
    {
		parent::_initialize();
		unset($_POST['status']);
		unset($_POST['groupid']);
		unset($_POST['amount']);
		unset($_POST['point']);
    }

    public function index()
    {
		$role	=	F("Role");
		$this->assign('usergroup',$role[$_SESSION['groupid']]['name']); 
 

		foreach((array)$_SESSION['_ACCESS_LIST']['ADMIN'] as $key=>$r){$modules[]=ucwords(strtolower($key));}
		$modules=implode("','",$modules);
		$alltopnode= M('Node')->field('groupid')->where("name in('$modules') and level=2")->group('groupid')->select();
		foreach((array)$alltopnode as $key=>$r){$GroupAccessids[]=$r['groupid'];}	 

		foreach($this->menudata as $key=>$module) {
			if($module['parentid'] != 0 || $module['status']==0) continue;		
			if(in_array($key,$GroupAccessids) || $_SESSION[C('ADMIN_AUTH_KEY')]) {
				if(empty($module['action'])) $module['action']='index';		
					$nav[$key]  = $module;
					if($isnav){
						$array=array('menuid'=> $nav[$key]['parentid']);
						cookie('menuid',$nav[$key]['parentid']);
						//$_SESSION['menuid'] = $nav[$key]['parentid'];
					}else{
						 $array=array('menuid'=> $nav[$key]['id']);
					}
					if(empty($menuid) && empty($isnav)) $array=array();
					$c=array();
					parse_str($nav[$key]['data'],$c);
					$nav[$key]['data'] = $c + $array;				 
			}
		}
		
		//thinkphp ajax请求处理代码，判断是否是ajax请求，获取请求参数，返回ajax请求。
		if(IS_AJAX){
				$menuGroupList = $nav;
				foreach($nav as $key=>$r){
					$menu[$r['id']]  = $this->getnav($r['id']);
				}
				$data['menuGroupList'] = $menuGroupList;
				$data['menu'] = $menu;

				$this->ajaxReturn($data);
			
		}else{
			$this->assign('menuGroupList',$nav); 
			$this->assign($this->Config); 
			foreach($nav as $key=>$r){
				$menu[$r['id']]  = $this->getnav($r['id']);
			}
			$this->assign('menu',$menu);
			$this->display();
		}
    }

	/**
	 * 更新系统缓存
	*/
	public function cache() {
	
		// 删除操作：删除RUNTIME_PATH 下的指定文件和目录
		dir_delete(RUNTIME_PATH.'Html/');
		dir_delete(RUNTIME_PATH.'Cache/');
		if(is_file(RUNTIME_PATH.'~runtime.php'))@unlink(RUNTIME_PATH.'~runtime.php');
		if(is_file(RUNTIME_PATH.'~allinone.php'))@unlink(RUNTIME_PATH.'~allinone.php');	

		// 恢复栏目数据。
		R('Admin/Category/repair');
		R('Admin/Category/repair');


		// 更新操作：系统缓存模型的缓存更新，并存入缓存文件。
		foreach($this->cache_model as $r){			
			savecache($r);
		}
		
		// 执行成功跳转操作。
		$forward = $_GET['forward'] ?   $_GET['forward']  : U('Index/main');
		$this->assign ( 'jumpUrl', $forward );
		$this->success(L('do_success'));
	}

	public function main() {
		
		$db=D('');
		$db =   DB::getInstance();
		$tables = $db->getTables();
		
		$info = array(
           
            'SERVER_SOFTWARE'=>PHP_OS.' '.$_SERVER["SERVER_SOFTWARE"],
            'mysql_get_server_info'=>php_sapi_name(),
			'MYSQL_VERSION' => mysql_get_server_info(),
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'max_execution_time'=>ini_get('max_execution_time').L('miao'),
			'disk_free_space'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
            );
		$myphp_info=array(
			'myphp_VERSION'=> VERSION.' '.UPDATETIME.' [ <a href="#" target="_blank">'.L('view_new_VERSION').'</a> ]',			
			'license'=> '<b id="myphp_license"></b>',
			'SN'=> '<b id="myphp_sn"></b>',
			'update'=>  ' <b id="myphp_update"></b>',
			
		);
		$this->assign('myphp_info',$myphp_info);
        $this->assign('server_info',$info);		
		foreach ((array)$this->module as $rw){
			if($rw['type']==1){  
				$molule= M($rw['name']);
				$rw['counts'] = $molule->count();;
				$mdata['moduledata'][] = $rw;
			}
        }

		$molule= M('User');
		$counts = $molule->count();
		if($counts >1 ){
			$counts = $counts-1;// 隐藏掉系统超级管理员的统计
		}
		$userinfos = $molule->find($_SESSION['adminid']);
		$mdata['moduledata'][]=array('title'=>L('user_counts'),'counts'=>$counts);
		
		$molule= M('Category');$counts = $molule->count(); 
		$mdata['moduledata'][]=array('title'=>L('Category_counts'),'counts'=>$counts);
		$this->assign($mdata);
		$role =F('Role');
		
		$userinfo=array(
			'username'=>$userinfos['username'],	
			'groupname'=>$role[$userinfos['groupid']]['name'],
			'logintime'=>toDate($userinfos['last_logintime']),			
			'last_ip'=>$userinfos['last_ip'],	
			'login_count'=>$userinfos['login_count'].L('ci'),	
		);
		$this->assign('userinfo',$userinfo);

        $this->display();
    }

 
    // 更换密码
    public function password(){
		if($_POST['dosubmit']){
			if(md5($_POST['verify'])	!= $_SESSION['verify']) {
				$this->error(L('error_verify'));
			}
			if($_POST['password'] != $_POST['repassword']){
				$this->error(L('password_repassword'));
			}
			$map	=	array();
			$map['password']= sysmd5($_POST['oldpassword']);
			if(isset($_POST['username'])) {
				$map['username']	 =	 $_POST['username'];
			}elseif(isset($_SESSION['adminid'])) {
				$map['id']		=	$_SESSION['adminid'];
			}
			//检查用户
			$User    =   M("user");
			if(!$User->where($map)->field('id')->find()) {
				$this->error(L('error_oldpassword'));
			}else {
				$User->updatetime = time();
				$User->password	=	sysmd5($_POST['password']);
				$User->save();
				$this->success(L('do_success'));
			 }
		}else{
			 $this->display();
		}
    }

	// 修改个人资料
	public function profile() {
		if($_REQUEST['dosubmit']){
			$User	 =	M("User");
			if(!$User->create()) {
				$this->error($User->getError());
			}
			$User->update_time = time();
			$User->last_ip = get_client_ip();
			$result	=	$User->save();
			if(false !== $result) {
				$this->success(L('do_success'));
			}else{
				$this->error(L('do_error'));
			}
		}else{
			$User	 =	 M("user");
			$vo	=	$User->getById($_SESSION['adminid']);
			$this->assign('vo',$vo);
			$this->display();
		}
	}

}
?>