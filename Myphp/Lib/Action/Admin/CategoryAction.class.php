<?php 
/**
 * 
 * Category(分类管理)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class CategoryAction extends AdminbaseAction
{
    protected $dao,$categorys,$module;
    function _initialize()
    {
        parent::_initialize();
        foreach ((array)$this->module as $rw){
			if($rw['type']==1 && $rw['status']==1)  $data['module'][$rw['id']] = $rw;
        }
		$this->module=$data['module'];
        $this->assign($data);
		unset($data);
        $this->dao = D('Admin/category');
    }

    /**
     * 分类列表
     *
     */
    public function index()
    {
        if($this->categorys){
			foreach($this->categorys as $r) {
			
				// 如果模型为单页模型，则操作提示字符显示为修改内容
				if($r['module']=='Page'){
					$r['str_manage'] = '<a href="?g=Admin&m=Page&a=edit&id='.$r['id'].'">'.L('edit_page').'</a> | ';
				}elseif($r['module']==''){// 如果模型为空，则操作提示字符显示为空
					$r['str_manage'] = '';
				}else{// 否则链接如下显示，对应的模型名称，提示添加内容。
					$r['str_manage'] = '<a href="?g=Admin&m='.$r['module'].'&a=add&catid='.$r['id'].'">'.L('add_content').'</a> | ';
				}
				
				// 统一类别操作：添加子栏目，修改栏目，删除栏目。
				$r['str_manage'] .= '<a href="'.U('Category/add',array( 'parentid' => $r['id'],'type'=>$r['type'])).'">'.L('add_catname').'</a> | <a href="'.U('Category/edit',array( 'id' => $r['id'],'type'=>$r['type'])).'">'.L('edit').'</a> | <a href="javascript:confirm_delete(\''.U('Category/delete',array( 'id' => $r['id'])).'\')">'.L('delete').'</a> ';
				
				// 提示所属模型名称
				$r['modulename']=$this->module[$r['moduleid']]['title'] ? $this->module[$r['moduleid']]['title'] : L('Module_url');
				
				// 提示是否为导航显示
				$r['dis'] =  $r['ismenu'] ? '<font color="green">'.L('display_yes').'</font>' : '<font color="red">'.L('display_no').'</font>' ;				
				$array[] = $r;
			}
			
			// 设置列表结构：排序，ID,类别名称，所属模型，是否为导航显示，访问链接，管理菜单。
			$str  = "<tr>
						<td width='40' align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
						<td align='center'>\$id</td>
						<td >\$spacer\$catname &nbsp;</td>
						<td align='center'>\$modulename</td>
						<td align='center'>\$dis</td>
						<td align='center'><a href='\$url' target='_blank'>".L('fangwen')."</a></td>
						<td align='center'>\$str_manage</td>
					</tr>";
			import ( '@.ORG.Tree' );
			$tree = new Tree ($array);
			unset($array);
			$tree->icon = array('&nbsp;&nbsp;&nbsp;'.L('tree_1'),'&nbsp;&nbsp;&nbsp;'.L('tree_2'),'&nbsp;&nbsp;&nbsp;'.L('tree_3'));
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categorys = $tree->get_tree(0, $str);
			$this->assign('categorys', $categorys);
		}
        $this->display();
    }

	/**
	 * 添加分类之前的操作。显示的视图为添加分类页面。
	 *
	*/
	public function _before_add()
    {
		
		// 获取URL设置规则，如果有URL规则设置为生成静态，则在添加页面下拉框内显示。
		foreach((array)$this->Urlrule as $key =>$r){
			if($r['ishtml'])$Urlrule[$key]=$r;
		}
		$this->assign('Urlrule', $Urlrule);

		// 设置权限。
		$myphp_auth_key = sysmd5(C('ADMIN_ACCESS').$_SERVER['HTTP_USER_AGENT']);
		$myphp_auth = authcode('1-1-0-1-jpeg,jpg,png,gif-3-0', 'ENCODE',$myphp_auth_key);
		$this->assign('myphp_auth',$myphp_auth);

		// 类别模板设置。
		$templates= template_file();
		$this->assign ( 'templates',$templates );

		// 上级栏目ID
		$parentid =	intval($_GET['parentid']); 
		
		// 是否为导航
		$vo['ismenu']=1;
		
		// 所属模型ID
		$vo['moduleid'] =$this->categorys[$parentid]['moduleid'];
		$this->assign('vo', $vo);
		foreach($this->categorys as $r) {
			$array[] = $r;
		}
		
		// 下拉类别列表。
		import ( '@.ORG.Tree' );	
		$str  = "<option value='\$id' \$selected>\$spacer \$catname</option>";
		$tree = new Tree ($array);		 
		$select_categorys = $tree->get_tree(0, $str,$parentid);
		
		// 会员组。
		$usergroup=F('Role');
		$this->assign('rlist',$usergroup);
		$this->assign('select_categorys', $select_categorys);
	}

    /**
     * 提交录入（类别添加至数据库）
     *
     */
    public function insert()
    {
		
		/*
		if($_POST['parentid']){
			if($_POST['moduleid']!=$this->categorys[$_POST['parentid']]['moduleid']){
				$this->success(L('chose_notop_module'));
			}			
		}
		*/
		// 是否生成html。
		if(empty($_POST['urlruleid']) && $_POST['ishtml']) $this->error(L('do_empty'));
		
		// 访问权限。
		$_POST['readgroup'] = $_POST['readgroup'] ? implode(',',$_POST['readgroup']) : '';
		
		// 允许投稿会员组。
		$_POST['postgroup'] = $_POST['postgroup'] ? implode(',',$_POST['postgroup']) : '';

	 
		// 获取模型名称。
		$_POST['module'] = $this->module[$_POST['moduleid']]['name'] ? $this->module[$_POST['moduleid']]['name'] : '';
		
		// 获取模型ID。
		$_POST['moduleid']= intval($_POST['moduleid']);
		
		// 多语言设置。
		if(APP_LANG)$_POST['lang']=LANG_ID;
		
		// 如果栏目目录为空，则提取栏目名称的拼音全名作为目录名。
		if(empty($_POST['catdir'])){$_POST['catdir'] = Pinyin($_POST['catname']);}
		
        if($this->dao->create())
        {
			$id = $this->dao->add();
            if($id)
            {
				// 如果模型为单页模型，那么单页模型的类别名称就作为单页模型的标题。
				if($_POST['module']=='Page'){
					$_POST['id']=$id;
					if(empty($_POST['title']))$_POST['title'] = $_POST['catname'];
					$Page=D('Page');
					if($Page->create()){
						$Page->add();
					}
				}
				
				// 附件处理。
				if($_POST['aid']) {
					$Attachment =M('Attachment');		
					$aids =  implode(',',$_POST['aid']);
					$data['catid']= $_POST['catid'];
					$data['moduleid']= $_POST['moduleid'];
					$data['status']= '1';
					$Attachment->where("aid in (".$aids.")")->save($data);
				}

				// 恢复栏目数据。
				$this->repair();
				
				// 更新栏目缓存文件。
				savecache('Category');

				// 如果栏目设置生成html，那么更新相关页面。
				if($_POST['ishtml']){
				
					// 更新列表页面。
					$this->categorys = F('Category');				
					$cat = $this->categorys[$id];					
					$arrparentid = array_filter(explode(',',$cat['arrparentid'].','.$cat['id']));
					foreach($arrparentid as $catid) {
						if($this->categorys[$catid]['ishtml'])	$this->clisthtml($catid);					
					}
					
					// 如果首页设置了生成静态，那么更新首页静态文件。
					if($this->sysConfig['HOME_ISHTML']) $this->create_index();
				}
				$this->assign ( 'jumpUrl', U(MODULE_NAME.'/index') );
                $this->success(L('add_ok'));
			}else{
			   $this->error(L('add_error'));
			}
        }else{
            $this->error($this->dao->getError());
        }
    }

    /**
     * 编辑。显示的视图为编辑分类页面。
     *
     */
    public function edit()
    {
	
		// 获取编辑的类别ID。
		$id = intval($_GET['id']);

		// 获取URL设置规则，如果有URL规则设置为生成静态，则在添加页面下拉框内显示。
		foreach((array)$this->Urlrule as $key =>$r){
			if($r['ishtml'])$Urlrule[$key]=$r;
		}
		$this->assign('Urlrule', $Urlrule);
		
		// 设置权限。
		$myphp_auth_key = sysmd5(C('ADMIN_ACCESS').$_SERVER['HTTP_USER_AGENT']);
		$myphp_auth = authcode('1-1-0-1-jpeg,jpg,png,gif-3-0', 'ENCODE',$myphp_auth_key);
		$this->assign('myphp_auth',$myphp_auth);
		
		// 类别模板设置。
		$templates= template_file();
		$this->assign ( 'templates',$templates );

		// 访问权限。
        $record = $this->categorys[$id];
		$record['readgroup'] = explode(',',$record['readgroup']);
        if(empty($id) || empty($record)) $this->error(L('do_empty'));

		// 模型列表，栏目列表。
       	$parentid =	intval($record['parentid']);
		import ( '@.ORG.Tree' );		
		$result = $this->categorys;
		foreach($result as $r) {
			//if($r['type']==1) continue;
			$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
			$array[] = $r;
		}
		$str  = "<option value='\$id' \$selected>\$spacer \$catname</option>";
		$tree = new Tree ($array);		 
		$select_categorys = $tree->get_tree(0, $str,$parentid);
		$this->assign('select_categorys', $select_categorys);
        $this->assign('vo', $record);
		
		// 会员组。
		$usergroup=F('Role');
		$this->assign('rlist',$usergroup); 
		$this->display ();
		
    }

    /**
     * 提交编辑，将数据更新至数据库。
     *
     */
    public function update()
    {
		// 如果设置生成了静态，但没有静态URL规则，那么提示缺少必要的参数错误提示。
		if(empty($_POST['urlruleid']) && $_POST['ishtml']) $this->error(L('do_empty'));
		
		// 获取所属模型名称。
		$_POST['module'] = $this->module[$_POST['moduleid']]['name'];		
		
		// 访问权限。		
		$_POST['readgroup'] = $_POST['readgroup'] ? implode(',',$_POST['readgroup']) : '';
		
		// 会员组。
		$_POST['postgroup'] = $_POST['postgroup'] ? implode(',',$_POST['postgroup']) : '';
		
		// 上级栏目ID
		$_POST['arrparentid'] = $this->get_arrparentid($_POST['id']);
		
		// 是否为封面栏目。
		if(empty($_POST['listtype']))$_POST['listtype']=0;
		
		// 如果栏目目录为空，则提取栏目名称的拼音全名作为目录名。
		if(empty($_POST['catdir'])){$_POST['catdir'] = Pinyin($_POST['catname']);}

		// 如果栏目为外部链接，那么模型ID为0，模型名称为空。
		if($_POST['type']){
			$_POST['moduleid']=0;
			$_POST['module']='';
		}
		if (false === $this->dao->create ()) {
			$this->error ( $this->dao->getError () );
		}
		if (false !== $this->dao->save ()) {

			// 附件数据处理。
			if($_POST['aid']) {
					$Attachment =M('Attachment');		
					$aids =  implode(',',$_POST['aid']);
					$data['moduleid']= $_POST['moduleid'];
					$data['catid']= $_POST['id'];
					$data['status']= '1';
					$Attachment->where("aid in (".$aids.")")->save($data);
				}

			// 如果设置了多栏目设置，则更新该栏目的子栏目设置。
			if($_POST['chage_all']){
				$data=array();
				$arrchildid = $this->get_arrchildid($_POST['id']);
				$data['urlruleid'] = $_POST['urlruleid'] ? $_POST['urlruleid'] : '0' ;
				$data['presentpoint'] = $_POST['presentpoint'];
				$data['postgroup'] = $_POST['postgroup'] ? $_POST['postgroup'] : '';
				$data['chargepoint'] = $_POST['chargepoint'];
				$data['paytype'] = $_POST['paytype'];
				$data['repeatchargedays'] = $_POST['repeatchargedays'];
				$data['ismenu'] = $_POST['ismenu'];
				$data['ishtml'] = $_POST['ishtml'];
				$data['pagesize'] = $_POST['pagesize'];
				$data['template_list'] = $_POST['template_list'];
				$data['template_show'] = $_POST['template_show'];
				$data['readgroup'] = $_POST['readgroup'] ? $_POST['readgroup'] : '';
				$r = $this->dao->where( ' id in ('.$arrchildid.')')->data($data)->save();
			}
			
			// 恢复栏目数据。
			$this->repair();
			$this->repair();
			
			// 更新栏目缓存文件。
			savecache('Category');
			
			// 如果栏目设置生成html，那么更新相关页面。
			if($_POST['ishtml']){
			
				// 更新列表html静态页。
				$cat=$this->categorys[$_POST['id']];
				$arrparentid = array_filter(explode(',',$cat['arrparentid'].','.$cat['id']));
				foreach($arrparentid as $catid) {
					if($this->categorys[$catid]['ishtml'])	$this->clisthtml($catid);					
				}
				
				// 如果首页设置了生成静态，那么更新首页静态文件。
				if($this->sysConfig['HOME_ISHTML']) $this->create_index();
			}
			$this->assign ( 'jumpUrl', U(MODULE_NAME.'/index') );
			$this->success (L('edit_ok'));
		} else {
			$this->success (L('edit_error').': '.$this->dao->getDbError());
		}
 
    }
	
	/**
	 * 更新栏目数据。
	*/
	public function repair_cache() {
	
		// 恢复栏目数据。
		$this->repair();
		$this->repair();
		
		// 更新栏目缓存文件。
		savecache('Category');
		
		// 执行成功跳转。
		$this->assign ( 'jumpUrl', U(MODULE_NAME.'/index') );
		$this->success(L('do_success'));
	}

	/**
	 * 恢复栏目数据。
	*/
	public function repair() {
	 
		@set_time_limit(500);
		$this->categorys = $categorys = array();
		
		// 如果为多语言站点，则进行多语言条件查询。
		if(APP_LANG)$langwhere =  " and lang = ".LANG_ID;
		
		// 获取所有顶级栏目。
		$categorys = $this->dao->where("parentid=0".$langwhere)->Order('listorder ASC,id ASC')->select();
		
		// 对获取的栏目数据进行整理保存至$this->categorys中。
		$this->set_categorys($categorys);

		if(is_array($this->categorys)) {
			foreach($this->categorys as $id => $cat) {
				//if($id == 0 || $cat['type']==1) continue;
				$this->categorys[$id]['arrparentid'] = $arrparentid = $this->get_arrparentid($id); // 根据栏目id获取父级id列表
				$this->categorys[$id]['arrchildid'] = $arrchildid = $this->get_arrchildid($id);// 根据栏目id获取子级id列表
				$this->categorys[$id]['parentdir'] =	$cat['parentdir'] = $parentdir = $this->get_parentdir($id);// 根据栏目id获取父级栏目目录
	 
				$child = is_numeric($arrchildid) ? 0 : 1;
				
				/**
				 * 栏目类型判断：type 1为外部链接，0为站内链接。根据url规则生成栏目链接。
				 */
				if( $cat['type']==1){ // 如果栏目类型为外部链接。
					$url=$cat['url'];
				}else{ // 如果栏目类型不是外部链接。
					$url =  geturl($cat,'',$this->Urlrule);// 生成栏目url。
					$url = $url[0];
				}
				
				/**
				 * 将生成的url保存至数据库栏目表中。
				 */
				 
				// url：栏目url，parentdir：父级缓存目录，arrparentid：父级id列表，arrchildid：子级id列表，child：子级，id：自己的id索引
				$this->dao->save(array('url'=>$url,'parentdir'=>$parentdir,'arrparentid'=>$arrparentid,'arrchildid'=>$arrchildid,'child'=>$child,'id'=>$id));
			}
		}
	}

	/**
	 * 以id为索引获取所有指定语言的的栏目数组。
	*/
	public function set_categorys($categorys = array()) {
		if (is_array($categorys) && !empty($categorys)) {
			foreach ($categorys as $id => $c) {
				$this->categorys[$c[id]] = $c;	
				if(APP_LANG)$langwhere =  " and lang = ".LANG_ID;
				$r = $this->dao->where("parentid = $c[id]".$langwhere)->Order('listorder ASC,id ASC')->select();
				$this->set_categorys($r);
			}
		}
		return true;
	}
	
	/**
	 *  根据栏目id获取父级栏目目录
	 *
	*/
	public function get_parentdir($id) {
		if($this->categorys[$id]['parentid']==0) return '';
		 
		$arrparentid = $this->categorys[$id]['arrparentid'];
		unset($r);
		if ($arrparentid) {
				$arrparentid = explode(',', $arrparentid);
				$arrcatdir = array();
				foreach($arrparentid as $pid) {
					if($pid==0) continue;
					$arrcatdir[] = $this->categorys[$pid]['catdir'];
				}
				return implode('/', $arrcatdir).'/';
		}
	}

	/**
	 * 根据栏目id获取父级id列表
	 *
	*/
	public function get_arrparentid($id, $arrparentid = '') {
		if(!is_array($this->categorys) || !isset($this->categorys[$id])) return false;
		$parentid = $this->categorys[$id]['parentid'];
		$arrparentid = $arrparentid ? $parentid.','.$arrparentid : $parentid;
		if($parentid) {
			$arrparentid = $this->get_arrparentid($parentid, $arrparentid);
		} else {
			$this->categorys[$id]['arrparentid'] = $arrparentid;
		}
		return $arrparentid;
	}

	/**
	 * 根据栏目id获取子级id列表
	 *
	*/
	public function get_arrchildid($id) {
		$arrchildid = $id;
		if(is_array($this->categorys)) {
			foreach($this->categorys as $catid => $cat) {
				if($cat['parentid'] && $id != $catid) {
					$arrparentids = explode(',', $cat['arrparentid']);
					if(in_array($id, $arrparentids)) $arrchildid .= ','.$catid;
				}
			}
		}
		return $arrchildid;
	}
	/**
	 * 删除栏目操作
	 *
	 * 备注：
	 * 建立的模型是说明一组相关数据集合的主题特性，而栏目是对这些模型具体某一条数据的归类。
	 * 所以在删除某个栏目的时候，要注意到该模型是否建立了catid字段，是否给该模型的每条数据指定栏目类别。
	 * 如果该模型没有建立catid字段，则删除该模型建立的栏目时，会报错。所以建立模型是最好定义出catid栏目字段。
	 *
	 */
	public function delete() {
		$catid = intval($_GET['id']);//获取删除栏目的id
		$moduleName = $this->categorys[$catid]['module'];//从栏目id获取删除栏目的模型名称

		// 判断栏目类型，如果为外部链接，删除外部链接类型的栏目
		if($this->categorys[$catid]['type']==1){
		
			if($this->categorys[$catid]['arrchildid']!=$catid)$this->error(L('category_does_not_allow_delete'));
			
			$this->dao->delete($catid);// 依据栏目id删除该栏目
			delattach("catid in($catid)");//删除栏目的附件，例栏目的缩略图
			
		}else{
		
			$moduleObject  = M($moduleName);// 根据模型名称创建模型对象
			$arrchildid = $this->categorys[$catid]['arrchildid'];//获取该栏目的子栏目id列表,包括该栏目本身
			$where =  "catid in(".$arrchildid.")";
			$count = $moduleObject->where($where)->count();// 统计出栏目数量
			
			if($count) $this->error(L('category_does_not_allow_delete'));// 如果包含子栏目则禁止删除。
			$this->dao->delete($arrchildid);// 删除栏目及其子栏目。

			$moduleid = $this->mod[$moduleName];// 根据模型名称获取该模型的id
			delattach("moduleid =$moduleid and catid in($arrchildid)");//删除栏目附件，例栏目的缩略图
			
			$arr=explode(',',$arrchildid);// 将子栏目列表转化成数组形式
			
			// 循环遍历该数组，如果子栏目类型为单页，则执行删除操作。
			foreach((array)$arr as $r){
				if($this->categorys[$r]['module']=='Page'){
				$moduleObjectPage=M('Page');
				$moduleObjectPage->delete($r);
				}
			}
		}
		$this->repair();// 恢复栏目数据。
		savecache('Category');// 更新栏目类型缓存文件
		$this->assign ( 'jumpUrl', U(MODULE_NAME.'/index') );
		$this->success(L('do_success')); 
	}
}
