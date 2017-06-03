<?php
/**
 * 
 * Base (前台公共模块)
 * 
 * 常用模板变量
 *
 * 全局模板变量
 * $T,$Categorys
 * list列表页模板变量
 * $list,$page
 * show内容页模板变量
 * $cat,$catid,$data,$page
 * page单页面模板变量
 * $data
 */
if(!defined("Myphp")) exit("Access Denied");
class BaseAction extends Action
{

	protected   $Config ,$sysConfig,$categorys,$module,$moduleid,$mod,$dao,$Type,$Role,$_userid,$_groupid,$_email,$_username ,$forward ,$user_menu,$Lang,$member_config;
	
    /**
     * 初始化前台展示
     */	
    /**
     * 请求参数
     * $_GET['l']//语言id
     */
    /**
     * 常量定义
	 * define('LANG_NAME', $lang); //定义当前的语言包标识常量。
	 * define('LANG_ID', $this->Lang[$lang]['id']); //定义当前的语言包ID常量。
     */
    /**
     * 会话变量
     * $_SESSION['FCDREP_lang'] //当前的语言包标识常量。
     * $_SESSION['FCDREP_langid'] //当前的语言包ID常量。 
     */
    /**
     * 模板变量
     * $this->assign('Lang',$this->Lang);
	 * $this->assign('l',$lang);
	 * $this->assign('langid',LANG_ID);
	 * $this->assign('T',$T);
	 * $this->assign($this->Config);
	 * $this->assign('Role',$this->Role);
	 * $this->assign('Type',$this->Type);
	 * $this->assign('Module',$this->module);
	 * $this->assign('Categorys',$this->categorys);	
	 * $this->assign( 'form',new Form());	 
	 * $this->assign('user_menu',$this->user_menu);
	 * $this->assign('jumpUrl',URL('User-Login/emailcheck'));
	 * $this->assign('waitSecond',3);
	 * $this->assign('header',TMPL_PATH.'Home/'.THEME_NAME.'/Home_header.html');			
	 * $this->assign('forward',$this->forward);
	 * $this->assign('search_module',$search_module);
	 * $this->assign('module_name',MODULE_NAME); //将请求的模块名称赋值到模板中。
	 * $this->assign('action_name',ACTION_NAME); //将请求的动作名称赋值到模板中。
     */	
    public function _initialize() {
		
			/**
			* 读取后台配置缓存文件
			*/
			//获取后台设置。后台设置文件被保存在Cache/Data文件夹下，每次配置更改都会更新相应的文件。			
			$this->sysConfig = F('sys.config'); //获取后台定义的系统参数
			$this->module = F('Module'); //获取后台定义的模块
			$this->Role = F('Role'); //获取后台定义的会员组
			$this->Type =F('Type'); //获取后台定义的类别
			$this->mod= F('Mod'); //获取后台定义的模型
			$this->moduleid=$this->mod[MODULE_NAME]; //获取模型的ID

			/**
			* 读取后台多语言or单语言配置，并赋值到模板中。
			*/
			
			// 多语言设置。
			if(APP_LANG){
				$this->Lang = F('Lang'); //获取后台定义的语言包
				$this->assign('Lang',$this->Lang);
				
				// $_GET['l'] 的值为语言包的语言标识
				// 如果有请求的语言标识，则赋值给$lang，否则为系统默认语言设置。
				if(get_safe_replace($_GET['l'])){
					if(!$this->Lang[$_GET['l']]['status'])$this->error ( L ( 'NO_LANG' ) );
					$lang=$_GET['l'];
				}else{
					$lang=$this->sysConfig['DEFAULT_LANG'];
				}
				
				define('LANG_NAME', $lang); //定义当前的语言包标识常量。
				define('LANG_ID', $this->Lang[$lang]['id']); //定义当前的语言包ID常量。
				
				$this->categorys = F('Category_'.$lang); //获取相应语言的后台栏目设置。
				$this->Config = F('Config_'.$lang);  //获取相应语言的后台站点配置。
				
				// 将语言标识和语言ID赋值到模板。
				$this->assign('l',$lang);
				$this->assign('langid',LANG_ID);
				$this->assign('langname',LANG_NAME);
				
				$T = F('config_'.$lang,'', APP_PATH.'Tpl/Home/'.$this->sysConfig['DEFAULT_THEME'].'/');// 将配置信息保存至模板根目录下。
				C('TMPL_CACHFILE_SUFFIX','_'.$lang.'.php'); // 使用语言标识来配置模板缓存文件后缀。
				cookie('think_language',$lang);//设置语言至cookie中。
			}else{
				// 如果是单语言，则$T，$this->categorys，$this->Config，think_language设置不区分语言。
				$T = F('config_'.$this->sysConfig['DEFAULT_LANG'],'',  APP_PATH.'Tpl/Home/'.$this->sysConfig['DEFAULT_THEME'].'/');
				$this->categorys = F('Category');//获取后台栏目设置。
				$this->Config = F('Config');//获取后台站点配置。
				cookie('think_language',$this->sysConfig['DEFAULT_LANG']);//设置语言至cookie中。
			}
			
			//将$T,后台设置信息（站点配置，会员组，类别，模块，栏目）赋值到模板中。将Form对象赋值到模板中。
			$this->assign('T',$T);
			$this->assign($this->Config);
			$this->assign('Role',$this->Role);
			$this->assign('Type',$this->Type);
			$this->assign('Module',$this->module);
			$this->assign('Categorys',$this->categorys);
			import("@.ORG.Form");			
			$this->assign('form',new Form());
 
			//将后台系统设置保存至配置参数中。
			C('HOME_ISHTML',$this->sysConfig['HOME_ISHTML']);
			C('PAGE_LISTROWS',$this->sysConfig['PAGE_LISTROWS']);
			C('URL_M',$this->sysConfig['URL_MODEL']);
			C('URL_M_PATHINFO_DEPR',$this->sysConfig['URL_PATHINFO_DEPR']);
			C('URL_M_HTML_SUFFIX',$this->sysConfig['URL_HTML_SUFFIX']);
			C('URL_LANG',$this->sysConfig['DEFAULT_LANG']);
			C('DEFAULT_THEME_NAME',$this->sysConfig['DEFAULT_THEME']);


			import("@.ORG.Online");
			$session = new Online();
			if(cookie('auth')){
				$myphp_auth_key = sysmd5($this->sysConfig['ADMIN_ACCESS'].$_SERVER['HTTP_USER_AGENT']);
				list($userid,$groupid, $password) = explode("-", authcode(cookie('auth'), 'DECODE', $myphp_auth_key));
				$this->_userid = $userid;
				$this->_username =  cookie('username');
				$this->_groupid = $groupid; 
				$this->_email =  cookie('email');
			}else{
				$this->_groupid = cookie('groupid') ?  cookie('groupid') : 4;
				$this->_userid =0;
			}


			foreach((array)$this->module as $r){
				if($r['issearch'])$search_module[$r['name']] = L($r['name']);
				if($r['ispost'] && (in_array($this->_groupid,explode(',',$r['postgroup']))))$this->user_menu[$r['id']]=$r;
			}
			if(GROUP_NAME=='User'){
				$langext = $lang ? '_'.$lang : '';
				$this->member_config=F('member.config'.$langext);
				$this->assign('member_config',$this->member_config);
				$this->assign('user_menu',$this->user_menu);
				if($this->_groupid=='5' &&  MODULE_NAME!='Login'){ 
					$this->assign('jumpUrl',URL('User-Login/emailcheck'));
					$this->assign('waitSecond',3);
					$this->success(L('no_regcheckemail'));
					exit;
				}
				$this->assign('header',TMPL_PATH.'Home/'.THEME_NAME.'/Home_header.html');
			}
			if($_GET['forward'] || $_POST['forward']){	
				$this->forward = get_safe_replace($_GET['forward'].$_POST['forward']);
			}else{
				if(MODULE_NAME!='Register' || MODULE_NAME!='Login' )
				$this->forward =isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] :  $this->Config['site_url'];
			}
			$this->assign('forward',$this->forward);

			$this->assign('search_module',$search_module);
			$this->assign('module_name',MODULE_NAME); //将请求的模块名称赋值到模板中。
			$this->assign('action_name',ACTION_NAME); //将请求的动作名称赋值到模板中。
 
	}

	/**
	 * 列表页_index.html
	 */
	/**
	 * 模板变量
	 * $this->assign('module_name',$module);// 栏目模块名称赋值到模板
	 * $this->assign($cat);// 栏目模块字段信息赋值到模板
	 * $this->assign('catid',$catid);// 栏目模块id赋值到模板
	 * $this->assign('bcid',$bcid);// 栏目模块父级栏目数组赋值到模板
	 * $this->assign('jumpUrl',URL('User-Login/index'));
	 * $this->assign('fields', $fields); // 将处理的请求模型的配置信息赋值到模板
	 * $this->assign('seo_title',$seo_title);// 栏目的seo标题
	 * $this->assign('seo_keywords',$cat['keywords']);// 栏目的seo关键词
	 * $this->assign('seo_description',$cat['description']);// 栏目的seo描述
	 * $this->assign('pages',$pages);//分页数据
	 * $this->assign('list',$list);//列表数据
	 * $this->assign ($data);// 单页模型，字段为content
	 */
    public function index($catid='',$module='')
    {
		$this->Urlrule =F('Urlrule');// 获取后台设置的URL规则。
		if(empty($catid)) $catid =  intval($_REQUEST['id']);// 获取请求的栏目id
		$p= max(intval($_REQUEST[C('VAR_PAGE')]),1);
		
		// 如果存在请求的栏目(id)
		if($catid){
			$cat = $this->categorys[$catid];// 从后台配置中获取栏目模块字段信息
			$bcid = explode(",",$cat['arrparentid']); // 获取栏目父级栏目路径数组
			$bcid = $bcid[1]; 
			if($bcid == '') $bcid=intval($catid);
			if(empty($module))$module=$cat['module'];// 获取栏目模块名称
			$this->assign('module_name',$module);// 栏目模块名称赋值到模板
			unset($cat['id']);
			$this->assign($cat);// 栏目模块字段信息赋值到模板
			$cat['id']=$catid;
			$this->assign('catid',$catid);// 栏目模块id赋值到模板
			$this->assign('bcid',$bcid);// 栏目模块父级栏目数组赋值到模板
		}
		
		// 访问权限判断与设置
		if($cat['readgroup'] && $this->_groupid!=1 && !in_array($this->_groupid,explode(',',$cat['readgroup']))){$this->assign('jumpUrl',URL('User-Login/index'));$this->error (L('NO_READ'));}
		
		$fields = F($this->mod[$module].'_Field');// 获取请求模型的配置信息。
		foreach($fields as $key=>$r){
			$fields[$key]['setup'] =string2array($fields[$key]['setup']);
		}
		$this->assign ( 'fields', $fields); // 将处理的请求模型的配置信息赋值到模板


		$seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
		$this->assign ('seo_title',$seo_title);// 栏目的seo标题
		$this->assign ('seo_keywords',$cat['keywords']);// 栏目的seo关键词
		$this->assign ('seo_description',$cat['description']);// 栏目的seo描述
				
		// 请求在线留言模型
		if($module=='Guestbook'){
			$where['status']=array('eq',1);
			$this->dao= M($module);
			$count = $this->dao->where($where)->count();
			if($count){
				import ( "@.ORG.Page" );
				$listRows =  !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');		
				$page = new Page ( $count, $listRows );
				$page->urlrule = geturl($cat,'');
				$pages = $page->show();
				$field =  $this->module[$cat['moduleid']]['listfields'];
				$field =  $field ? $field : '*';
				$list = $this->dao->field($field)->where($where)->order('createtime desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
				$this->assign('pages',$pages);
				$this->assign('list',$list);
			}
			$template = $cat['module']=='Guestbook' && $cat['template_list'] ? $cat['template_list'] : 'index';
			$this->display(THEME_PATH.$module.'_'.$template.'.html');
			
		// 请求信息反馈模型
		}elseif($module=='Feedback'){
			$template = $cat['module']=='Feedback' && $cat['template_list'] ? $cat['template_list'] : 'index' ;

			$this->display(THEME_PATH.$module.'_'.$template.'.html');
			
		// 请求单页模型
		}elseif($module=='Page'){
			$modle=M('Page');
			$data = $modle->find($catid);
			unset($data['id']);

			//分页
			$CONTENT_POS = strpos($data['content'], '[page]');
			if($CONTENT_POS !== false) {			
				$urlrule = geturl($cat,'',$this->Urlrule);
				$urlrule[0] =  urldecode($urlrule[0]);
				$urlrule[1] =  urldecode($urlrule[1]);
				$contents = array_filter(explode('[page]',$data['content']));
				$pagenumber = count($contents);
				for($i=1; $i<=$pagenumber; $i++) {
					$pageurls[$i] = str_replace('{$page}',$i,$urlrule);
				} 
				$pages = content_pages($pagenumber,$p, $pageurls);
				//判断[page]出现的位置
				if($CONTENT_POS<7) {
					$data['content'] = $contents[$p];
				} else {
					$data['content'] = $contents[$p-1];
				}
				$this->assign ('pages',$pages);	
			}
			
			// 单页面读取多图内容
			foreach($data as $key=>$c_d){
					if($fields[$key]['type']=='images' || $fields[$key]['type']=='files'){ 
					if(!empty($data[$key])){
						$p_data=explode(':::',$data[$key]);
						$data[$key]=array();
						foreach($p_data as $k=>$res){
							$p_data_arr=explode('|',$res);					
							$data[$key][$k]['filepath'] = $p_data_arr[0];
							$data[$key][$k]['filename'] = $p_data_arr[1];
						}
						unset($p_data);
						unset($p_data_arr);
					}
				}
			}			

			$template = $cat['template_list'] ? $cat['template_list'] :  'index' ;
			$this->assign ($data);	
			$this->display(THEME_PATH.$module.'_'.$template.'.html');

		}else{
			
			if($catid){
				$seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
				$this->assign ('seo_title',$seo_title);
				$this->assign ('seo_keywords',$cat['keywords']);
				$this->assign ('seo_description',$cat['description']);
				

				$where = " status=1 ";
				if($cat['child']){							
					$where .= " and catid in(".$cat['arrchildid'].")";			
				}else{
					$where .=  " and catid=".$catid;			
				}
				if(empty($cat['listtype'])){
					$this->dao= M($module);
					$count = $this->dao->where($where)->count();
					if($count){
						import ( "@.ORG.Page" );
						$listRows =  !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');
						$page = new Page ( $count, $listRows );
						$page->urlrule = geturl($cat,'',$this->Urlrule);
						$pages = $page->show();
						$field =  $this->module[$this->mod[$module]]['listfields'];
						$field =  $field ? $field : 'id,catid,userid,url,username,title,title_style,keywords,description,thumb,createtime,hits';
						$list = $this->dao->field($field)->where($where)->order('createtime desc,id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
						$this->assign('pages',$pages);
						$this->assign('list',$list);
					}
					$template_r = 'list';
				}else{
					$template_r = 'index';
				}
			}else{
				$template_r = 'list';
			}
			$template = $cat['template_list'] ? $cat['template_list'] : $template_r;
			$this->display($module.':'.$template);
		}
    }

 
	/**
	 * 内容页_show.html
	 */
	/**
	 * 模板变量
	 * $this->assign('module_name',$module);// 将模块赋值到模板
	 * $this->assign('jumpUrl',URL('User-Login/index'));
	 * $this->assign ('seo_title',$seo_title);// 内容的seo标题
	 * $this->assign ('seo_keywords',$data['keywords']);// 内容的seo关键词
	 * $this->assign ('seo_description',$data['description']);// 内容的seo描述
	 * $this->assign ( 'fields', F($cat['moduleid'].'_Field') ); // 赋值请求的内容所属栏目对应的模型字段数据。
	 * $this->assign ('pages',$pages);	
	 * $this->assign('catid',$catid);// 所属栏目id
	 * $this->assign ($cat);// 所属栏目数据。
	 * $this->assign('bcid',$bcid);// 所属上级目录路径数组。
	 * $this->assign ($data);// 请求的内容数据。	 
	 */
	public function show($id='',$module='')
    {
	
		$this->Urlrule =F('Urlrule');// 获取后台设置的URL规则。
		$p= max(intval($_REQUEST[C('VAR_PAGE')]),1);		
		$id = $id ? $id : intval($_REQUEST['id']);// 获取请求的内容id
		$module = $module ? $module : MODULE_NAME;// 获取请求的模块名称
		$this->assign('module_name',$module);// 将模块赋值到模板
		$this->dao= M($module);// 创建数据模型
		$data = $this->dao->find($id);// 根据内容id获取内容数据
		
		$catid = $data['catid'];// 从结果集中获取内容的类别id
		$cat = $this->categorys[$data['catid']];// 获取当前内容的类别数据。
		if(empty($cat['ishtml']))$this->dao->where("id=".$id)->setInc('hits'); //如果该栏目类别没有生成静态，添加点击次数
		$bcid = explode(",",$cat['arrparentid']); // 获取父级栏目id路径。
		$bcid = $bcid[1]; 
		if($bcid == '') $bcid=intval($catid);

		// 访问权限判断与设置
		if($data['readgroup']){
			if($this->_groupid!=1 && !in_array($this->_groupid,explode(',',$data['readgroup'])) )$noread=1;
		}elseif($cat['readgroup']){
			if($this->_groupid!=1 && !in_array($this->_groupid,explode(',',$cat['readgroup'])) )$noread=1;
		}
		// 如果需要访问权限，则先要进行登陆
		if($noread==1){$this->assign('jumpUrl',URL('User-Login/index'));$this->error (L('NO_READ'));}

		$chargepoint = $data['readpoint'] ? $data['readpoint'] : $cat['chargepoint']; 
		if($chargepoint && $data['userid'] !=$this->_userid){
			$user = M('User');
			$userdata =$user->find($this->_userid);
			if($cat['paytype']==1 && $userdata['point']>=$chargepoint){
				$chargepointok = $user->where("id=".$this->_userid)->setDec('point',$chargepoint);
			}elseif($cat['paytype']==2 && $userdata['amount']>=$chargepoint){
				$chargepointok = $user->where("id=".$this->_userid)->setDec('amount',$chargepoint);
			}else{
				$this->error (L('NO_READ'));
			}
		}
	
		// 内容数据赋值到模板
		$seo_title = $data['title'].'-'.$cat['catname'];
		$this->assign ('seo_title',$seo_title);// 内容的seo标题
		$this->assign ('seo_keywords',$data['keywords']);// 内容的seo关键词
		$this->assign ('seo_description',$data['description']);// 内容的seo描述
		$this->assign ( 'fields', F($cat['moduleid'].'_Field') ); // 赋值请求的内容所属栏目对应的模型字段数据。

		

		$fields = F($this->mod[$module].'_Field');// 获取请求模型的配置信息。
		
		foreach($data as $key=>$c_d){
			$setup='';
			$fields[$key]['setup'] =$setup=string2array($fields[$key]['setup']);
			if($setup['fieldtype']=='varchar' && $fields[$key]['type']!='text'){
				$data[$key.'_old_val'] =$data[$key];
				$data[$key]=fieldoption($fields[$key],$data[$key]);
			}elseif($fields[$key]['type']=='images' || $fields[$key]['type']=='files'){ 
				if(!empty($data[$key])){
					$p_data=explode(':::',$data[$key]);
					$data[$key]=array();
					foreach($p_data as $k=>$res){
						$p_data_arr=explode('|',$res);					
						$data[$key][$k]['filepath'] = $p_data_arr[0];
						$data[$key][$k]['filename'] = $p_data_arr[1];
					}
					unset($p_data);
					unset($p_data_arr);
				}
			}
			unset($setup);
		}
		$this->assign('fields',$fields);// 将处理的请求模型的配置信息赋值到模板


		//手动分页
		$CONTENT_POS = strpos($data['content'], '[page]');
		if($CONTENT_POS !== false) {
			
			$urlrule = geturl($cat,$data,$this->Urlrule);
			$urlrule =  str_replace('%7B%24page%7D','{$page}',$urlrule); 
			$contents = array_filter(explode('[page]',$data['content']));
			$pagenumber = count($contents);
			for($i=1; $i<=$pagenumber; $i++) {
				$pageurls[$i] = str_replace('{$page}',$i,$urlrule);
			} 
			$pages = content_pages($pagenumber,$p, $pageurls);
			//判断[page]出现的位置是否在文章开始
			if($CONTENT_POS<7) {
				$data['content'] = $contents[$p];
			} else {
				$data['content'] = $contents[$p-1];
			}
			$this->assign ('pages',$pages);	
		}

		// 内容模板设置
		
		// 如果内容在后台指定了内容模板，则设置为当前内容模板
		if(!empty($data['template'])){
			$template = $data['template'];
		// 如果内容没有指定内容模板，则判断所属栏目是否指定了内容模板，
		}elseif(!empty($cat['template_show'])){
			$template = $cat['template_show'];
		// 如果所属栏目没有指定内容模板，则设置为默认模板show。
		}else{
			$template =  'show';
		}

		$this->assign('catid',$catid);// 所属栏目id
		$this->assign ($cat);// 所属栏目数据。
		$this->assign('bcid',$bcid);// 所属上级目录路径数组。

		$this->assign ($data);// 请求的内容数据。

		$this->display($module.':'.$template); //载入内容模板
    }

	/**
	 * 下载
	 */
	public function down()
	{

		$module = $module ? $module : MODULE_NAME;
		$id = $id ? $id : intval($_REQUEST['id']);
		$this->dao= M($module);
		$filepath = $this->dao->where("id=".$id)->getField('file');
		$this->dao->where("id=".$id)->setInc('downs');

		if(strpos($filepath, ':/')) { 
			header("Location: $filepath");
		} else {	
			$filepath = '.'.$filepath;
			if(!$filename) $filename = basename($filepath);
			$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
			if(strpos($useragent, 'msie ') !== false) $filename = rawurlencode($filename);
			$filetype = strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
			$filesize = sprintf("%u", filesize($filepath));
			if(ob_get_length() !== false) @ob_end_clean();
			header('Pragma: public');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header('Content-Transfer-Encoding: binary');
			header('Content-Encoding: none');
			header('Content-type: '.$filetype);
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Content-length: '.$filesize);
			readfile($filepath);
		}
		exit;
	}
	
	/**
	 * 点击次数
	 */
	public function hits()
	{
		$module = $module ? $module : MODULE_NAME;
		$id = $id ? $id : intval($_REQUEST['id']);
		$this->dao= M($module);
		$this->dao->where("id=".$id)->setInc('hits');

		if($module=='Download'){
			$r = $this->dao->find($id);
			echo '$("#hits").html('.$r['hits'].');$("#downs").html('.$r['downs'].');';
		}else{
			$hits = $this->dao->where("id=".$id)->getField('hits');
			echo '$("#hits").html('.$hits.');';
		}
		exit;
	}
	
	/**
	 * 验证码
	 */
	public function verify()
    {
		header('Content-type: image/jpeg');
        $type	 =	 isset($_GET['type'])? get_safe_replace($_GET['type']):'jpeg';
        import("@.ORG.Image");
        //Image::buildImageVerify(4,1,$type);
		Image::buildImageVerify(4,1,$type,80,34,'verify');//后台验证码大小设置
    }
	
	// 判断请求的动作是否存在，不存在则跳转错误。
	public function __call($name, $arguments) {

		throw_exception('404');
	}	
}
?>