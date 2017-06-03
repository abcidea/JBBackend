<?php

if(!defined("Myphp")) exit("Access Denied");
class CaseAction extends BaseAction
{

     public function cats()
	 {
	     $catid=intval($_GET['id']);
		 $cid=intval($_GET['cid']);
         $this->Urlrule =F('Urlrule');
		 $p= max(intval($_REQUEST[C('VAR_PAGE')]),1);
         
		 if($catid){
			$cat = $this->categorys[$catid];
			$bcid = explode(",",$cat['arrparentid']); 
			$bcid = $bcid[1]; 
			
			if($bcid == '') $bcid=intval($catid);
			if(empty($module))$module=$cat['module'];
			$this->assign('module_name',$module);
			unset($cat['id']);
			$this->assign($cat);
			$cat['id']=$catid;
			$this->assign('catid',$catid);
			$this->assign('bcid',$bcid);
		}
		 $seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
		 $this->assign ('seo_title',$seo_title);
		 $this->assign ('seo_keywords',$cat['keywords']);
		 $this->assign ('seo_description',$cat['description']);

		 $where = " status=1 ";
		 if($cat['child'])
		 {							
			  $where .= " and catid in(".$cat['arrchildid'].")";			
		 }
		 else
		 {
			  $where .=  " and catid=".$catid;			
		 }
		 
		 if($cid)
		 {
		     $where .= " and cid in(".$cid.")";
		 }

		 $this->dao= M($module);
		 $count = $this->dao->where($where)->count();
		 if($count)
		 {
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

	     $template = 'cats';
		 $this->display($module.':'.$template);
	 }
}
?>