<?php
/**
 *
 * Content(内容管理模块模型公共类)
 *
 */
if(!defined("Myphp")) exit("Access Denied");

class ContentAction extends AdminbaseAction
{
    protected  $dao,$fields;
    public function _initialize()
    {
        parent::_initialize();
		$module =$this->module[$this->moduleid]['name'];
		
		$this->dao = D($module);

		$fields = F($this->moduleid.'_Field');
		foreach($fields as $key => $res){
			$res['setup']=string2array($res['setup']);
			$this->fields[$key]=$res;
		}
		unset($fields);
		unset($res);
		$this->assign ('fields',$this->fields);
    }

    /**
	 * 公共列表页
	 *
	 */
    public function index()
    {
		$template =  file_exists(THEME_PATH.MODULE_NAME.'_index.html') ? MODULE_NAME.':index' : 'Content:index';
	    $this->_list(MODULE_NAME);
        $this->display ($template);
    }
	
    /**
	 * 公共添加页
	 *
	 */
	public function add()
    {
		$vo['catid']= intval($_GET['catid']);
		$form=new Form($vo);
		$form->isadmin=1;
		$this->assign ( 'form', $form );
		$template =  file_exists(THEME_PATH.MODULE_NAME.'_edit.html') ? MODULE_NAME.':edit' : 'Content:edit';
		$this->display ( $template);
	}

    /**
	 * 公共编辑页
	 *
	 */
	public function edit()
    {
		
		$id = $_REQUEST ['id'];		
		if(MODULE_NAME=='Page'){
					$Page=D('Page');
					$p = $Page->find($id);
					if(empty($p)){
					$data['id']=$id;
					$data['title'] = $this->categorys[$id]['catname'];
					$data['keywords'] = $this->categorys[$id]['keywords'];
					$Page->add($data);	
					}
		}
		$vo = $this->dao->getById ( $id );
		$vo['content'] = htmlspecialchars($vo['content']);
 		$form=new Form($vo);
		
 
		$this->assign ( 'vo', $vo );
		$this->assign ( 'form', $form );
		$template =  file_exists(THEME_PATH.MODULE_NAME.'_edit.html') ? MODULE_NAME.':edit' : 'Content:edit';
		$this->display ( $template);
	}

    /**
     * 录入数据库操作。
     *
     */
    public function insert($module='',$fields=array(),$userid=0,$username='',$groupid=0)
    {
		// 如果模型名称为空，则创建空模型。
		$model = $module ?  M($module) : $this->dao;
		
		// 如果字段为空，则获取所有字段。
		$fields = $fields ? $fields : $this->fields ;

		// 如果更新时需要输入验证码，则进行验证码验证。
		if($fields['verifyCode']['status'] && (md5($_POST['verifyCode']) != $_SESSION['verify'])){
			$this->assign ( 'jumpUrl','javascript:history.go(-1);');
			$this->error(L('error_verify'));
        }
		
		// 对post数据进行字段检查,如果检查后的post为空，则返回缺少必要的参数错误提示。
		$_POST = checkfield($fields,$_POST);
		if(empty($_POST)) $this->error (L('do_empty'));
		
		// 加入创建的时间。
		$_POST['createtime'] = time();	
		
		// 设置更新时间与创建时间相同。
		$_POST['updatetime'] = $_POST['createtime'];	
		
		// 创建者id
        $_POST['userid'] = $module ? $userid : $_SESSION['userid'];
		
		// 创建者名称
		$_POST['username'] = $module ? $username : $_SESSION['username'];
		
		// 更新时对标题进行着色，加粗设置。
		if($_POST['style_color']) $_POST['style_color'] = 'color:'.$_POST['style_color'];
		if($_POST['style_bold']) $_POST['style_bold'] =  ';font-weight:'.$_POST['style_bold'];
		if($_POST['style_color'] || $_POST['style_bold'] ) $_POST['title_style'] = $_POST['style_color'].$_POST['style_bold'];
 
		$module = $module? $module : MODULE_NAME ;
		if(GROUP_NAME=='User')$_POST['status'] = $this->Role[$groupid]['allowpostverify'] ? 1 : 0;
		
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		
		// 获取插入后的ID
		$_POST['id'] = $id= $model->add();

		if ($id !==false) {
			$catid = $module =='Page' ? $id : $_POST['catid'];


			// 附件数据处理。
			if($_POST['aid']) {
				$Attachment =M('Attachment');		
				$aids =  implode(',',$_POST['aid']);
				$data['id']=$id;
				$data['catid']= $catid;
				$data['status']= '1';
				$Attachment->where("aid in (".$aids.")")->save($data);
			}
			
			
			// 新插入数据的url生成与保存。
			$tablename=C('DB_PREFIX').strtolower($module);
			$db=D('');
			$db =   DB::getInstance();
			$tables = $db->getTables();			
			$Fields=$db->getFields($tablename); 
			
			if(isset($Fields['url'])){
				$data='';
				$cat = $this->categorys[$catid];
				$url = geturl($cat,$_POST,$this->Urlrule);
				$data['id']= $id;
				$data['url']= $url[0];
				$model->save($data);
			}

			// 新插入关键词的tag标签处理。
			if($_POST['keywords'] && $module !='Page'){
				$keywordsarr=explode(',',$_POST['keywords']);
				$i=0;
				$tagsdata =M('Tags_data');
				$tagsdata->where("id=".$id)->delete();
				foreach((array)$keywordsarr as $tagname){
					if($tagname){
						$tagidarr=$tagdatas=$where=array();
						$where['name']=array('eq',$tagname);
						$where['moduleid']=array('eq',$cat['moduleid']);
						$tagid=M('Tags')->where($where)->field('id')->find();
						$tagidarr['id']=$id;
						if($tagid){
							$num = $tagsdata->where("tagid=".$tagid[id])->count();
							$tagdatas['num']=$num+1;
							M('Tags')->where("id=".$tagid[id])->save($tagdatas);
							$tagidarr['tagid']=$tagid['id'];
						}else{
							$tagdatas['moduleid']=$cat['moduleid'];
							$tagdatas['name'] = $tagname;
							$tagdatas['slug'] = Pinyin($tagname);
							$tagdatas['num']=1;
							$tagdatas['lang']=$_POST['lang'];
							$tagdatas['module']= $cat['module'];
							$tagidarr['tagid']=M('Tags')->add($tagdatas);
						}
						$i++;
						$tagsdata->add($tagidarr);
					}
				}
			}

			if($cat['presentpoint']){
				$user =M('User');
				if($cat['presentpoint']>0) $user->where("id=".$_POST['userid'])->setInc('point',$cat['presentpoint']);
				if($cat['presentpoint']<0) $user->where("id=".$_POST['userid'])->setDec('point',$cat['presentpoint']);
			}
 
			// 如果所属分类设置了生成静态，则进行内容页面与列表页面的静态更新操作。
			if($cat['ishtml'] && $_POST['status']){
			
				// 生成内容页html操作。
				if($module!='Page'   && $_POST['status'])	$this->create_show($id,$module);
				
				// 生成列表页html文件。
				$arrparentid = array_filter(explode(',',$cat['arrparentid'].','.$cat['id']));
				foreach($arrparentid as $catid) {
					if($this->categorys[$catid]['ishtml'])	$this->clisthtml($catid);					
				}
 			}
			
			// 如果首页设置了生成静态，则进行首页的静态更新操作。
			if($this->sysConfig['HOME_ISHTML']) $this->create_index();
			if(GROUP_NAME=='Admin'){
				$this->assign ( 'jumpUrl', U($module.'/index') );
			}elseif(GROUP_NAME=='User'){
				$this->assign ( 'jumpUrl',$_SERVER['HTTP_REFERER']);
				//$this->assign ( 'jumpUrl', U(GROUP_NAME.'-'.MODULE_NAME.'/add?moduleid='.$cat['moduleid']) );
			}
			$this->success (L('add_ok'));
		} else {
			$this->error (L('add_error').': '.$model->getDbError());
		}
	
    }
	
    /**
     * 更新数据库操作。
     *
     */
	function update($module='',$fields=array(),$userid=0,$username='')
	{  
		// 如果模型名称为空，则创建空模型。
		$model = $module ?  M($module) : $this->dao;

		// 如果字段为空，则获取所有字段。
		$fields = $fields ? $fields : $this->fields ;

		// 如果更新时需要输入验证码，则进行验证码验证。
		if($fields['verifyCode']['status'] && (md5($_POST['verifyCode']) != $_SESSION['verify'])){
			$this->assign ( 'jumpUrl','javascript:history.go(-1);');
			$this->error(L('error_verify'));
        }

		// 对post数据进行字段检查,如果检查后的post为空，则返回缺少必要的参数错误提示。
		$_POST = checkfield($fields,$_POST);
		if(empty($_POST)) $this->error (L('do_empty'));

		// 加入更新的时间。
		$_POST['updatetime'] = time();

		// 更新时对标题进行着色，加粗设置。
		if($_POST['style_color']) $_POST['style_color'] = 'color:'.$_POST['style_color'];
		if($_POST['style_bold']) $_POST['style_bold'] =  ';font-weight:'.$_POST['style_bold'];
		if($_POST['style_color'] || $_POST['style_bold'] ) $_POST['title_style'] = $_POST['style_color'].$_POST['style_bold'];

		// 获取修改后的更新分类
		$cat = $this->categorys[$_POST['catid']];
		$module = $module? $module : MODULE_NAME ;
		
		// 获取修改后的更新url
		$_POST['url'] = geturl($cat,$_POST,$this->Urlrule);
		$_POST['url'] =$_POST['url'][0];

		// 从数据库中查询到更新前的旧数据。
		$olddata = $model->find($_POST['id']);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		

		// 更新数据到数据库
		$list=$model->save ();
		

		if (false !== $list) {
			$id= $_POST['id'];
			$catid = $module =='Page' ? $id : $_POST['catid'];

			// 如果关键词被更新，则更新tag标签。
			if($olddata['keywords']!=$_POST['keywords']  && $module !='Page'){
				 

				$tagidarr=$tagdatas=$where=array();
				$where['name']=array('in',$olddata['keywords']);
				$where['moduleid']=array('eq',$cat['moduleid']);
				$where['lang']=array('eq',$_POST['lang']);
				M('Tags')->where($where)->setDec('num'); 

				$tagsdata =M('Tags_data');
				$tagsdata->where("id=".$id)->delete();

				$keywordsarr=explode(',',$_POST['keywords']);			
				foreach((array)$keywordsarr as $tagname){
					if($tagname){
						$tagidarr=$tagdatas=$where=array();
						$where['name']=array('eq',$tagname);
						$where['moduleid']=array('eq',$cat['moduleid']);
						$where['lang']=array('eq',$_POST['lang']);
						$tagid=M('Tags')->where($where)->field('id')->find();
						$tagidarr['id']=$id;
						if($tagid['id']>0){
							M('Tags')->where("id=".$tagid[id])->setInc('num'); ;
							$tagidarr['tagid']=$tagid['id'];
						}else{
							$tagdatas['moduleid']=$cat['moduleid'];
							$tagdatas['name'] = $tagname;
							$tagdatas['slug'] = Pinyin($tagname);
							$tagdatas['num']=1;
							$tagdatas['lang']=$_POST['lang'];
							$tagdatas['module']= $cat['module'];
							$tagidarr['tagid']=M('Tags')->add($tagdatas);
						}
						$tagsdata->add($tagidarr);
					}
				}
				M('Tags')->where('num<=0')->delete();
			}

			// 附件数据处理
			if($_POST['aid']) {
				$Attachment =M('Attachment');		
				$aids =  implode(',',$_POST['aid']);
				$data['id']= $id;
				$data['catid']= $catid;
				$data['status']= '1';
				$Attachment->where("aid in (".$aids.")")->save($data);
			}
			$cat = $this->categorys[$catid];
			
			// 如果所属分类设置了生成静态，则进行内容页面与列表页面的静态更新操作。
			if($cat['ishtml']){
			
				// 生成内容页html操作。
				if($module!='Page'  && $_POST['status'])	$this->create_show($_POST['id'],$module);
				
				// 生成列表页html文件。
				$arrparentid = array_filter(explode(',',$cat['arrparentid'].','.$cat['id']));
				foreach($arrparentid as $catid) {
					if($this->categorys[$catid]['ishtml'])	$this->clisthtml($catid);					
				}
 			}
			
			// 如果首页设置了生成静态，则进行首页的静态更新操作。
			if($this->sysConfig['HOME_ISHTML']) $this->create_index();
			$this->assign ( 'jumpUrl', $_POST['forward'] );
			$this->success (L('edit_ok'));
		} else {
			//错误提示
			$this->success (L('edit_error').': '.$model->getDbError());
		}
	}

	// 审核操作。
	function statusallok(){

		// 获取模型名称。
		$module = MODULE_NAME;
		
		// 获取模型操作对象。
		$model = M ( $module );
		
		// 获取审核的ID列表。
		$ids=$_POST['ids'];
		
		// 如果ID列表不为空，且为一个数组，则进行更新操作。否则提示一个缺少必要参数的错误提示。
		if(!empty($ids) && is_array($ids)){
		
			// 分割id列表为数组。
			$id=implode(',',$ids);
			
			// 获取数据库查询结果集。
			$data = $model->select($id);
			
			if($data){
				
				// 循环遍历结果集。
				foreach($data as $key=>$r){	
					
					// 更新当前数据审核通过
					$model->save(array(id=>$r['id'],status=>1));
					
					// 如果类别设置为静态更新，那么更新这条数据的内容页面
					if($this->categorys[$r['catid']]['ishtml'] && $r['status'])$this->create_show($r['id'],$module);	
				}
				
				// 如果类别设置为静态更新，那么更新相关的静态页面。
				$cat =  $this->categorys[$r['catid']];
				if($cat['ishtml']){	
					
					// 如果首页设置了更新静态，那么更新首页静态文件。
					if($this->sysConfig['HOME_ISHTML']) $this->create_index();
					
					// 更新列表页静态文件。
					$arrparentid = array_filter(explode(',',$cat['arrparentid'].','.$cat['id']));
					foreach($arrparentid as $catid) {
						if($this->categorys[$catid]['ishtml'])	$this->clisthtml($catid);					
					}
				}
				$this->success(L('do_ok'));
			}else{
				$this->error(L('do_error').': '.$model->getDbError());
			}
		}else{
			$this->error(L('do_empty'));
		}
	}

	/*状态*/

	public function status(){
		$module = MODULE_NAME;
		$model = D ($module);
		if($model->save($_GET)){
			$_POST ='';
			$_POST = $model->find($_GET['id']);
			$cat =  $this->categorys[$_POST['catid']];
			if($cat['ishtml']){
				if($module!='Page'  && $_POST['status'])	$this->create_show($_POST['id'],$module);				
				if($this->sysConfig['HOME_ISHTML']) $this->create_index();
				$arrparentid = array_filter(explode(',',$cat['arrparentid'].','.$cat['id']));
				foreach($arrparentid as $catid) {
					if($this->categorys[$catid]['ishtml'])	$this->clisthtml($catid);					
				}
 			}

			$this->success(L('do_ok'));
		}else{
			$this->error(L('do_error'));
		}
	}


}?>