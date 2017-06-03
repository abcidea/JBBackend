<?php
/**
 * 
 * Createhtml(网站更新管理)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class CreatehtmlAction extends AdminbaseAction {
	
    protected  $module;
    public function _initialize()
    {
        parent::_initialize(); 
        foreach ((array)$this->module as $rw){
			if($rw['type']==1  && $rw['status']==1)  $data['module'][$rw['id']] = $rw;
        }
		$this->module=$data['module'];
		$this->assign('module',$this->module);
		$this->assign('menuid',intval($_GET['menuid']));
    }

    public function index()
    {
		$this->display('Createhtml:index');
    }
	
	/**
	 * 生成首页html文件
	*/
	public function docreateindex()
	{
		$this->create_index();
		$this->assign ( 'jumpUrl', U(MODULE_NAME.'/index') );
		$this->success(L('index_create_OK'));		 
	}
	
	/**
	 * 生成列表页html页面
	*/
 	public function createlist()
    {
		$moduleid = intval($_GET['moduleid']);
		$this->assign('moduleid',$moduleid);
			if($this->categorys){
				foreach ($this->categorys as $r){
					if($r['type']==1 && $r['ishtml']==0) continue;
					if($moduleid && $r['moduleid'] !=  $moduleid) continue;
					if(ACTION_NAME=='Updateurl' && $r['module']=='Page') continue;
					if(ACTION_NAME=='Createlist' && $r['ishtml']!=1) continue;
					if((ACTION_NAME=='Createshow' && $r['ishtml']!=1) || (ACTION_NAME=='Createshow' && $r['module']=='Page')) continue;				
					$array[] = $r;
				}
				import ( '@.ORG.Tree' );	
				$str  = "<option value='\$id'  \$disabled>\$spacer \$catname</option>";
				$tree = new Tree ($array);	
				$tree->icon = array('&nbsp;&nbsp;&nbsp;'.L('tree_1'),'&nbsp;&nbsp;&nbsp;'.L('tree_2'),'&nbsp;&nbsp;&nbsp;'.L('tree_3'));
				$select_categorys = $tree->get_tree(0, $str);
				$this->assign('select_categorys', $select_categorys);
			}

			$this->display('Createhtml:show');	 
		 
    }

	/**
	 * 生成列表页html文件操作。
	*/
	public function doCreatelist()
    {
			$this->assign ( 'waitSecond', 0);
			extract($_GET,EXTR_SKIP);
			$moduleid = intval($_GET['moduleid']);
			$doid = $doid ? intval($doid) : 0;
			$count = intval($_GET['count']);
			if($dosubmit!=1){
				$catids=array();
				if($_GET['catids'][0]){
					$catids = $_SESSION['catids'] = $_GET['catids'];
				}else{
					foreach($this->categorys as $id=>$cat) {
						if($cat['type']!=0  || $cat['ishtml']!=1) continue;
						if($moduleid){									
							if($cat['moduleid']!=$moduleid) continue;
						}
						$catids[] = $id;
					}
					$catids = $_SESSION['catids'] = $catids;
				}
			}else{
				$catids =$_SESSION['catids'];	
			}
			if(!isset($catids[$doid])){
					unset($_SESSION['catids']);
					$forward = U("Createhtml/createlist");
					$this->assign ( 'jumpUrl', $forward);
					$this->success(L('create_update_success'));
			}else{
					$id = $catids[$doid];					
					if(empty($count)){
						$module = $this->categorys[$id]['module'];
						$dao= M($module);
						$where['status']=1;
						if(empty($this->categorys[$id]['listtype'])){
							if($this->categorys[$id]['child']){
								$where['catid']=array('in',$this->categorys[$id]['arrchildid']);
							}else{
								$where['catid']=$id;
							}
							
							if(APP_LANG){
								$where['lang'] = LANG_ID;// 进行多语言查询
							}
							
							$count = $dao->where($where)->count();
						}else{
							$count=1;
						}
								
					}
					if(empty($pages)){
						$cat_pagesize =  !empty($this->categorys[$id]['pagesize']) ? $this->categorys[$id]['pagesize'] : C('PAGE_LISTROWS');
						$pages = ceil($count/$cat_pagesize);
					}

					$p = max(intval($p), 1);
					$j = 1;
					do {
						$this->create_list($id,$p,$count);					
						$j++;
						$p++;
						$pages = isset($pages) ? $pages : PAGESTOTAL;
						 
					} while ($p <= $pages && $j < $pagesize);

					if($p <= $pages)  {
						$endpage = intval($p+$pagesize);
						$percent = round($p/$pages, 2)*100;
						$urlarray=array(
							'count' => $count,
							'doid' => $doid,
							'dosubmit' => 1,
							'pages' => $pages,
							'p' => $p,
							'pagesize' => $pagesize,
							'iscreatehtml'=>1,
						);						
						$message = L('updating').$this->categorys[$id]['catname'].L('create_update_count').$pages.L('create_update_list_num').$p.L('items_list').$percent.L('items1');
						$forward = U("Createhtml/".ACTION_NAME,$urlarray);
					} else {
						$doid++;
						$urlarray=array(
							'doid' => $doid,
							'dosubmit' => 1,
							'p' => 1,
							'pagesize' => $pagesize,
							'iscreatehtml'=>1,
						);
						$message = L('start_updating').$this->categorys[$id]['catname']." ...";
						$forward = U("Createhtml/".ACTION_NAME,$urlarray);						
					}
					$this->assign ( 'jumpUrl', $forward);
					$this->success($message);
			}
	}

	/**
	 * 更新内容页url操作。
	*/
	public function doUpdateurl()
    {
		$this->assign ( 'waitSecond', 0);
		$moduleid = intval($_GET['moduleid']);// 获取更新的某个模型，如果为空值，则为不限模型。
		extract($_GET,EXTR_SKIP);// 对于数组中的每个元素，键名用于变量名，键值用于变量值。
		
		if($moduleid<=0 && $catids[0] <= 0){
				
			// 如果存在模型缓存数据并且会话moduleids为空。
			if($this->module && !$_SESSION['moduleids']){
			
					// 循环读取后台定义的模块缓存数据
					foreach($this->module as $moduleid=>$r){
					
						// 使用$moduleid获取当前需要更新的模型对应的表名
						$tablename=C('DB_PREFIX').$this->module[$moduleid]['name'];
						
						$db=D(''); // 实例化一个空模型
						$db =   DB::getInstance();// 创建一个数据库操作对象
						$tables = $db->getTables();// 获取数据库中所有的表名			

						// 获取当前模型对应表的字段
						$Fields=$db->getFields($tablename); 
						
						// 循环遍历当前更新模型的表字段
						foreach ( $Fields as $key =>$r){
						
							// 如果当前模型对应表存在url字段，则将需要更新的模型id赋值到moduleids会话中
							if($key=='url') $_SESSION['moduleids'][] = $moduleid;
						}
					}
			}
			
			// 设置需要更新url模型的索引
			$doid = $doid ? intval($doid) : 0;
			
			// 如果$doid索引到的更新的url模型不存在，则销毁更新url模型的会话变量，跳转，并提示更新成功。
			if(!isset($_SESSION['moduleids'][$doid])){
					unset($_SESSION['moduleids']);
					$forward = U("Createhtml/updateurl");
					$this->assign ( 'jumpUrl', $forward);
					$this->success(L('create_update_success'));
			}else{// 否则进行包含url字段的模型的更新操作
					$moduleid = $_SESSION['moduleids'][$doid];// 获取更新url的模型id
					$module=$this->module[$moduleid]['name'];// 获取更新url的模型名称
					
					$dao = M($module);// 创建更新url的模型的操作对象
					
					$p = max(intval($p), 1);
					
					$start = $pagesize*($p-1);

					if(APP_LANG){
						$where = "`lang` = ".LANG_ID;// 进行多语言查询
					}
					
					//获取需要更新url模型记录的总条数。
					if(!isset($count)){
						$count = $dao->where($where)->count();
					}
					
					$pages = ceil($count/$pagesize);// 获取不小于余数的下一个整数
					
					// 如果存在更新url的模型记录条数
					if($count){
						
						// 根据更新到的当前条数与每轮更新的条数获取到当前需要更新的url模型的字段结果集
						$list = $dao->field('id,catid,url,createtime')->where($where)->limit($start . ',' . $pagesize)->select();	
						
						// 循环遍历更新的字段列表，生成url链接，并更新到数据库中
						foreach($list as $r) {
							if($r['islink']) continue;
							$url = geturl($this->categorys[$r['catid']],$r,$this->Urlrule);
							unset($r['catid']);
							$r['url'] = $url['0'];
							$dao->save($r);
						}					 
					}

					//如果为true，则提示正在更新统计信息
					if($pages > $p) {
							
							// 组织更新每轮跳转的查询参数
							$p++;
							$creatednum = $start + count($list);
							$percent = round($creatednum/$count, 2)*100;
							$urlarray=array(
								'doid' => $doid,
								'dosubmit' => 1,
								'count' => $count,
								'pages' => $pages,
								'p' => $p,
								'pagesize' => $pagesize,
							);
							
							// 组织更新每轮跳转的提示信息
							$message = L('updating').$this->module[$moduleid]['title'].L('create_update_count').$count.L('create_update_num').$creatednum.L('items').$percent.L('items1');
							
							// 组织更新每轮跳转的跳转地址
							$forward = U("Createhtml/".ACTION_NAME,$urlarray);
							
							// 进行跳转并提示操作成功。
							$this->assign ( 'jumpUrl', $forward);
							$this->success($message);
							
						// 否则提示当前正在开始更新哪个模型
						} else {
							$doid++;
							$urlarray=array(
								'doid' => $doid,
								'dosubmit' => 1,
								'p' => 1,
								'pagesize' => $pagesize,
							);
							$message = L('start_updating').$this->module[$moduleid]['title']." ...";
							$forward = U("Createhtml/".ACTION_NAME,$urlarray);
							$this->assign ( 'jumpUrl', $forward);
							$this->success($message);
						}
				}
			}elseif($moduleid){
				$module=$this->module[$moduleid]['name'];
				$dao = M($module);

				$p = max(intval($p), 1);
				$start = $pagesize*($p-1);

				if(is_array($catids) && $catids[0] > 0){
					$cids = implode(',',$catids);
					$where = " catid IN($cids) ";
					$_SESSION['catids'] = $catids;					
				}
				if(!$catids && $_SESSION['catids'] && $_SESSION['catids'][0] > 0){
					$catids = implode(',',$_SESSION['catids']);;
					$where = " catid IN($catids) ";
				}
				if (APP_LANG) {
						$where .= " AND `lang` = ".LANG_ID;
				}
				if(!isset($count)){
					$count = $dao->where($where)->count();
				}
				$pages = ceil($count/$pagesize);
					
				if($count){
					$list = $dao->field('id,catid,url')->where($where)->limit($start . ',' . $pagesize)->select();		 
					foreach($list as $r) {
						if($r['islink']) continue;
						$url = geturl($this->categorys[$r['catid']],$r,$this->Urlrule);
						unset($r['catid']);
						$r['url'] = $url['0'];
						$dao->save($r);
					}					 
				}

				if($pages > $p) {
					$p++;
					$creatednum = $start + count($list);
					$percent = round($creatednum/$count, 2)*100;
					$urlarray=array(
						'moduleid' => $moduleid,
						'dosubmit' => 1,
						'count' => $count,
						'pages' => $pages,
						'p' => $p,
						'pagesize' => $pagesize,
					);
					 
					$message = L('create_update_count').$count.L('create_update_num').$creatednum.L('items').$percent.L('items1');
					$forward = U("Createhtml/updateurl",$urlarray);
					$this->assign ( 'jumpUrl', $forward);					
					$this->success($message);
				} else {
					unset($_SESSION['catids']);
					$forward = U("Createhtml/updateurl");
					$this->assign ( 'jumpUrl', $forward);
					$this->success(L('create_update_success'));
				}
			}else{
				//按照栏目更新url
				extract($_GET,EXTR_SKIP);
				$doid = $doid ? intval($doid) : 0;
				if(empty($_SESSION['catids']) && $catids){
					if($catids[0] == 0) { 
							foreach($this->categorys as $id=>$cat) {
								if($cat['child'] || $cat['type']!=0 || $cat['module']=='Page') continue;
								$catids[] = $id;
							}
					}
					$_SESSION['catids'] = $catids;
				}else{
					$catids =$_SESSION['catids'];	
				}
				if(!isset($catids[$doid])){
					unset($_SESSION['catids']);
					$forward = U("Createhtml/updateurl");
					$this->assign ( 'jumpUrl', $forward);
					$this->success(L('create_update_success'));
				}elseif($catids[$doid]<=0){
					$forward = U("Createhtml/updateurl");
					$this->assign ( 'jumpUrl', $forward);
					$this->success(L('create_update_success'));
				
				}else{
					$id = $catids[$doid];					
					$module=$this->categorys[$id]['module'];
					$dao = M($module);
					$where = "catid=$id";
					if (APP_LANG) {
						$where .= " AND `lang` = ".LANG_ID;
					}
					$p = max(intval($p), 1);
					$start = $pagesize*($p-1);
					if(!isset($count)){
						$count = $dao->where($where)->count();
					}
					$pages = ceil($count/$pagesize);
					
					if($count){
						$list = $dao->field('id,catid,url')->where($where)->limit($start . ',' . $pagesize)->select();				 
						foreach($list as $r) {
							if($r['islink']) continue;
							$url = geturl($this->categorys[$r['catid']],$r,$this->Urlrule);
							unset($r['catid']);
							$r['url'] = $url['0'];
							$dao->save($r);
						}
					}
 
					if($pages > $p) {
						$p++;
						$creatednum = $start + count($list);
						$percent = round($creatednum/$count, 2)*100;
						$urlarray=array(
							'doid' => $doid,
							'dosubmit' => 1,
							'count' => $count,
							'pages' => $pages,
							'p' => $p,
							'pagesize' => $pagesize,
						);
						 
						$message = L('updating').$this->categorys[$id]['catname'].L('create_update_count').$count.L('create_update_num').$creatednum.L('items').$percent.L('items1');
						$forward = U("Createhtml/".ACTION_NAME,$urlarray);
						$this->assign ( 'jumpUrl', $forward);
						$this->success($message);
					} else {
						$doid++;
						$urlarray=array(
							'doid' => $doid,
							'dosubmit' => 1,
							'p' => 1,
							'pagesize' => $pagesize,
						);
						$message = L('start_updating').$this->categorys[$id]['catname']." ...";
						$forward = U("Createhtml/".ACTION_NAME,$urlarray);
						$this->assign ( 'jumpUrl', $forward);
						$this->success($message);
					}
				}
			}
	}

	/**
	 * 更新内容页URL页面	 
	 *
	*/
	public function updateurl()
    {
			$moduleid = intval($_GET['moduleid']);
			$this->assign('moduleid',$moduleid);
			if($this->categorys){
				foreach ($this->categorys as $r){
					if($r['type']==1 && $r['ishtml']==0) continue;
					if($_GET['moduleid'] && $r['moduleid'] !=  $_GET['moduleid']) continue;
					if(ACTION_NAME=='Updateurl' && $r['module']=='Page') continue;
					if(ACTION_NAME=='Createlist' && $r['ishtml']!=1) continue;
					if((ACTION_NAME=='Createshow' && $r['ishtml']!=1) || (ACTION_NAME=='Createshow' && $r['module']=='Page')) continue;				
					if($r['child'] && ACTION_NAME!='Createlist'){ 
						$r['disabled'] = 'disabled';
					}else{
						$r['disabled'] = '';
					}
					$array[] = $r;
				}
				import ( '@.ORG.Tree' );	
				$str  = "<option value='\$id'  \$disabled>\$spacer \$catname</option>";
				$tree = new Tree ($array);	
				$tree->icon = array('&nbsp;&nbsp;&nbsp;'.L('tree_1'),'&nbsp;&nbsp;&nbsp;'.L('tree_2'),'&nbsp;&nbsp;&nbsp;'.L('tree_3'));
				$select_categorys = $tree->get_tree(0, $str);
				$this->assign('select_categorys', $select_categorys);
			}
			$this->display('Createhtml:show');	 
		 
    }
	
	/**
	 * 更新内容页面
	 *
	*/
	public function createshow()
    { 
		$moduleid = intval($_GET['moduleid']);
		$this->assign('moduleid',$moduleid);
			if($this->categorys){
				foreach ($this->categorys as $r){
					if($r['type']==1 && $r['ishtml']==0) continue;
					if($moduleid && $r['moduleid'] !=  $moduleid) continue;
					if(ACTION_NAME=='Updateurl' && $r['module']=='Page') continue;
					if(ACTION_NAME=='Createlist' && $r['ishtml']!=1) continue;
					if((ACTION_NAME=='Createshow' && $r['ishtml']!=1) || (ACTION_NAME=='Createshow' && $r['module']=='Page')) continue;				
					if($r['child'] && ACTION_NAME!='Createlist'){ 
						$r['disabled'] = 'disabled';
					}else{
						$r['disabled'] = '';
					}
					$array[] = $r;
				}
				import ( '@.ORG.Tree' );	
				$str  = "<option value='\$id'  \$disabled>\$spacer \$catname</option>";
				$tree = new Tree ($array);	
				$tree->icon = array('&nbsp;&nbsp;&nbsp;'.L('tree_1'),'&nbsp;&nbsp;&nbsp;'.L('tree_2'),'&nbsp;&nbsp;&nbsp;'.L('tree_3'));
				$select_categorys = $tree->get_tree(0, $str);
				$this->assign('select_categorys', $select_categorys);
			}
			$this->display('Createhtml:show');	 
	}

	/**
	 * 更新内容页操作
	 */
	public function doCreateshow()
    {
		 
			$this->assign ( 'waitSecond', 0);
			extract($_GET,EXTR_SKIP);
			$moduleid = intval($_GET['moduleid']);
			$doid = $doid ? intval($doid) : 0;

			if($dosubmit!=1){
					if($catids[0] == 0) { 
						$catids=array();
						foreach($this->categorys as $id=>$cat) {
							if($cat['child'] || $cat['type']!=0 || $cat['module']=='Page' || $cat['ishtml']!=1) continue;
							if($moduleid){									
								if($cat['moduleid']!=$moduleid) continue;
							}
							$catids[] = $id;
						}
					}	
					$_SESSION['catids'] = $catids;
			}else{
					$catids =$_SESSION['catids'];	
			}
			if(!isset($catids[$doid])){
					unset($_SESSION['catids']);
					$forward = U("Createhtml/Createshow");
					$this->assign ( 'jumpUrl', $forward);
					$this->success(L('create_update_success'));
			}else{
					$id = $catids[$doid];
					$module=$this->categorys[$id]['module'];
					$dao = M($module);
					$where = "catid=$id";
					if (APP_LANG) {
						$where .= " AND `lang` = ".LANG_ID;
					}
					$p = max(intval($p), 1);
					$start = $pagesize*($p-1);

					if(!isset($count)){
						$count = $dao->where($where)->count();
					}
					$pages = ceil($count/$pagesize);
					
					if($count){
						$list = $dao->field('id,catid,url')->where($where)->limit($start . ',' . $pagesize)->select();				 
						foreach($list as $r) {
							if($r['islink']) continue;
							$module = $this->categorys[$r['catid']]['module'];
							
							// 生成内容页html操作。
							$this->create_show($r['id'],$module);
						}
					}

					if($pages > $p) {
						$p++;
						$creatednum = $start + count($list);
						$percent = round($creatednum/$count, 2)*100;
						$urlarray=array(
							'doid' => $doid,
							'dosubmit' => 1,
							'count' => $count,
							'pages' => $pages,
							'p' => $p,
							'pagesize' => $pagesize,
							'iscreatehtml'=>1,
						);
						 
						$message = L('updating').$this->categorys[$id]['catname'].L('create_update_count').$count.L('create_update_num').$creatednum.L('items').$percent.L('items1');
						$forward = U("Createhtml/".ACTION_NAME,$urlarray);
						$this->assign ( 'jumpUrl', $forward);
						$this->success($message);
					} else {
						$doid++;
						$urlarray=array(
							'doid' => $doid,
							'dosubmit' => 1,
							'p' => 1,
							'pagesize' => $pagesize,
							'iscreatehtml'=>1,
						);
						$message = L('start_updating').$this->categorys[$id]['catname']." ...";
						$forward = U("Createhtml/".ACTION_NAME,$urlarray);
						$this->assign ( 'jumpUrl', $forward);
						$this->success($message);
					}
			}

		}
		
		/**
		 * 生成网站地图页面
		*/
		public function createsitemap()
		{

			foreach((array)$this->module as $r){
				if($r['issearch'])$search_module[$r['name']] =  $r;
			}
			$this->assign('module',$search_module);
			
			if (APP_LANG) {
				$lang = C('URL_LANG') != LANG_NAME ? $lang = LANG_NAME . '/' : '';
			}
			
			$xmlmap = './' . $lang . 'sitemap.xml';
			$htmlmap = './' . $lang . 'sitemap.html';			
			$is_xmlmap=file_exists($xmlmap);
			$is_htmlmap=file_exists($htmlmap);
			if(!$is_xmlmap){
				$xmlmap = '/';
			}
			if(!$is_xmlmap){
				$htmlmap = '/';
			}			
			
			$this->assign('siteurl',$this->Config['site_url']);
			$this->assign('xmlmap',$xmlmap);
			$this->assign('htmlmap',$htmlmap); 
			$this->assign('is_xmlmap',$is_xmlmap);
			$this->assign('is_htmlmap',$is_htmlmap); 			
			$this->assign('yesorno',array(0 => L('no'),1  => L('yes')));
			$this->display('Createhtml:sitemap');	 
			
		}

		/**
		 * 生成网站地图
		*/
		public function docreatesitemap()
		{
			// 生成HTML地图
			if($_GET['htmlmap']){
				$r = $this->create_index(1);
			}else{// 删除HTML地图
				if (APP_LANG) {
					$lang = C('URL_LANG') != LANG_NAME ? $lang = LANG_NAME . '/' : '';
				}
				$htmlmap = './' . $lang . 'sitemap.html';
				if(file_exists($htmlmap)){
					$del_htmlmap = unlink($htmlmap);
				}
				
			}
			
			// 生成XML地图
			if($_GET['xmlmap']){
				import("@.ORG.Cxml");
				$array=array();

				$array[0]['NodeName']['value'] ='url';
				$array[0]['loc']['value']=$this->Config['site_url'];
				$array[0]['lastmod']['value']= date('Y-m-d',time());
				$array[0]['changefreq']['value'] ='weekly';
				$array[0]['priority']['value'] =1;

				foreach((array)$this->module as $r){
				
					// 如果为前台搜索类型。
					if($r['issearch']){
					
						// 获取模型信息生成数量。
						$num = intval($_GET[$r['name']]);
						if(!$num) continue;
						
						$where['status']=1;
						if (APP_LANG) {
							$where['lang'] = LANG_ID;// 进行多语言查询
						}
						
						// 从数据库中获取模型的数据内容信息，比如文章模型的文章，产品模型的产品，如果不存在内容，则创建失败。
						$data = M($r['name'])->field('id,title,url,createtime')->where($where)->order('id desc')->limit('0,'.$num)->select();

						// 循环遍历结果集，赋值到xml节点中。
						foreach($data as $key=> $res){
							$arraya[$key]['NodeName']['value'] ='url';
							$arraya[$key]['loc']['value'] = $this->Config['site_url'].$res['url'];					
							$arraya[$key]['lastmod']['value'] = date('Y-m-d',$res['createtime']);					
							$arraya[$key]['changefreq']['value'] ='weekly';
							$arraya[$key]['priority']['value'] =0.7;
						}
						$array =array_merge($array,$arraya);												
					}
				}
				
				// 生成XML文件。
				$Cxml = new Cxml();
				$Cxml->root='urlset';
				$Cxml->root_attributes=array('xmlns'=>'http://www.sitemaps.org/schemas/sitemap/0.9');
				
				if (APP_LANG) {
					$lang = C('URL_LANG') != LANG_NAME ? $lang = LANG_NAME . '/' : '';
				}
				
				$sitmappath = './' . $lang;
				if(!file_exists($sitmappath)){
					dir_create($sitmappath);
				}
				
				$sitemap = $sitmappath . 'sitemap.xml';				
				$xmldata = $Cxml->Cxml($array,$sitemap);
				$d=file_exists($sitemap);
				
			}else{// 删除XML地图
			
				if (APP_LANG) {
					$lang = C('URL_LANG') != LANG_NAME ? $lang = LANG_NAME . '/' : '';
				}

				$xmlmap = './' . $lang . 'sitemap.xml';
				if(file_exists($xmlmap)){
					$del_xmlmap = unlink($xmlmap);
				}
				
			}
			// 执行结果跳转。
			if(($_GET['htmlmap'] && $r) || ($_GET['xmlmap']&& $d) || (!$_GET['htmlmap'] && $del_htmlmap) || (!$_GET['xmlmap'] && $del_xmlmap)){
				$this->success(L('DO_OK'));
			}else{
				$this->error(L('Create error.'));
			}
			
		}

}
?>