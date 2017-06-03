<?php
/**
 * 
 * TagLib (标签库)
 *
 */
if(defined('APP_NAME')!='Myphp' && !defined("MFCDREPHP"))  exit("Access Denied");
class TagLibFcdrep extends TagLib
{
	/**
	 * list:内容列表
	 * subcat:子栏目列表
	 * catpos:栏目位置表
	 * nav:菜单树型列表
	 * link:友情链接列表
	 * kefu:在线客服列表
	 * db:数据库查询
	 * block:碎片
	 * flash:幻灯片
	 * tags:标签
	 * pre:上一篇
	 * next:下一篇	 
	*/
	protected $tags   =  array(
        //attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'list'=>array('attr'=>'name,field,limit,order,catid,thumb,posid,where,sql,key,page,mod,id,ids,status','level'=>3),
		'subcat'=>array('attr'=>'catid,type,self,key,id','level'=>3),
		'catpos' => array('attr'=>'catid,space','close'=>0),
		'nav' => array('attr'=>'catid,bcid,level,id,class,home,enhomefont','close'=>0),
		'link' => array('attr'=>'typeid,linktype,field,limit,order,field','level'=>3),
		'kefu' => array('attr'=>'left,top,level,id,class,type','close'=>0),
		'db' => array('attr'=>'dbname,sql,key,mod,id','level'=>3),
		'block' => array('attr'=>'blockid,pos,key,id','close'=>0),
		'flash' => array('attr'=>'flashid,key,mod,id','close'=>0),
		'tags' => array('attr'=>'keywords,list,key,mod,moduleid,id,limit,order','close'=>3),
		'pre' => array('attr'=>'blank,msg','close'=>0),
		'next' => array('attr'=>'blank,msg','close'=>0),
    );

	/**
	 * 内容的上一个
	*/
	public function _pre($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'tags');
		$msg    = !empty($tag['msg'])?$tag['msg']:'';// 没有上一条的提示
		$target = !empty($tag['blank'])? ' target="_blank" ' :'';//打开上一条链接的方式

		$m = $this->tpl->get('module');
		$id = $this->tpl->get('id');
		$catid = $this->tpl->get('catid');
		$r = M($m)->where("catid=$catid and id<$id")->order("id DESC")->find();

		//下面拼接输出语句，若果查询结果不为空，则拼接上一个链接，否则做出没有上一条的提示。
		$parsestr  = $r ? '<a class="pre_a" href="'.$r['url'].'"'.$target.'>'.$r['title'].'</a>' : $msg; 
		return  $parsestr;
	}
	
	/**
	 * 内容的下一个
	*/
	public function _next($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'tags');
		$msg    = !empty($tag['msg'])?$tag['msg']:'';// 没有下一条的提示
		$target = !empty($tag['blank'])? ' target="_blank" ' :'';//打开下一条链接的方式

		$m = $this->tpl->get('module');
		$id = $this->tpl->get('id');
		$catid = $this->tpl->get('catid');
		$r = M($m)->where("catid=$catid and id>$id")->order("id ASC")->find();

		//下面拼接输出语句，若果查询结果不为空，则拼接下一个链接，否则做出没有下一条的提示。
		$parsestr  = $r ? '<a class="next_a" href="'.$r['url'].'"'.$target.'>'.$r['title'].'</a>' : $msg; 
		return  $parsestr;
	}

	/**
	 * tag标签
	*/
	public function _tags($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'tags');
		$id = !empty($tag['id'])?$tag['id']:'r';  // 定义数据查询的结果存放变量
		$key    = !empty($tag['key'])?$tag['key']:'i';// 循环输出时的键值
		$mod    = isset($tag['mod'])?$tag['mod']:'2';// 列表的奇偶性
		$limit  = isset($tag['limit'])?$tag['limit']: '12';//读取的条数
		$order  = isset($tag['order'])?$tag['order']:'id desc';// 排序
		
		$keywords   = !empty($tag['keywords'])? $tag['keywords'] : ''; 
		$list = !empty($tag['list'])? $tag['list'] : ''; 
		$moduleid = !empty($tag['moduleid'])? $tag['moduleid'] : ''; //模型id

		// 如果不为数字，则为一个别名，模型id别名处理。
		if($moduleid && !is_numeric($moduleid)){
			if(substr($moduleid,0,2)=='T['){
				$T = $this->tpl->get('T');
				preg_match_all("/T\[(.*)\]$/",$moduleid,$arr);
				$moduleid=$T[$arr[1][0]];
			}else{
				$moduleid= $this->tpl->get($moduleid);
			}
		}
		
		// 匹配到大小写字母至少一次时，说明这个参数值为一个变量。
		preg_match("/[a-zA-Z]+/",$keywords,$keywordsa);
		
		// 获取这个变量的值。
		if($keywordsa[0]){
			$keywords = $this->tpl->get($keywords);
		}
		
		// 如果取得了值，那么用英文逗号分隔关键词，并给每个关键词加上英文单引号。
		if($keywords){
			$keyarr = explode(',',$keywords);
			$keywords="'".implode('\',\'',$keyarr)."'";
		}
 
		// 多语言查询
		if(APP_LANG){
			$lang=$this->tpl->get('langid');
			$where= " lang=".$lang;
		}else{
			$where = ' 1 ';
		}
		$where .= $moduleid ? ' and moduleid='.$moduleid : '';
		$where .= $keywords ? " and name in($keywords)" : '';
 
		// 下面拼接出查询语句。使用标签列出相关阅读。
		if($list){ 
			$tagids = M("Tags")->where($where)->order($order)->limit($limit)->select();
			$where = " b.status=1 ";
			if($tagids[0]){
				foreach((array)$tagids as $r)$tagidarr[]=$r['id'];
				$tagid=implode(',',$tagidarr);
				$where .= " and a.tagid in($tagid)";
			}

			$M = $this->tpl->get('Module');
			$prefix=C( "DB_PREFIX" );
			$moduleid = $moduleid ? $moduleid : '2' ;
			$mtable=$prefix.strtolower($M[$moduleid]['name']);
			$field =  'b.id,b.catid,b.userid,b.url,b.username,b.title,b.keywords,b.description,b.thumb,b.createtime';
			$table=$prefix.'tags_data as a';
			$mtable = $mtable." as b on a.id=b.id";
			$sql  = "M(\"Tags_data\")->field(\"{$field}\")->table(\"{$table}\")->join(\"{$mtable}\")->where(\"{$where}\")->order(\"{$order}\")->group(\"b.id\")->limit(\"{$limit}\")->select();";
		}else{//找出关键词标签。
			$sql  = "M(\"Tags\")->where(\"{$where}\")->order(\"{$order}\")->limit(\"{$limit}\")->select();";
		}	 

		//下面拼接输出语句
		$parsestr  = '';
		$parsestr .= '<?php  $_result='.$sql.'; if ($_result): $'.$key.'=0;';
		$parsestr .= 'foreach($_result as $key=>$'.$id.'):';
		$parsestr .= '++$'.$key.';$mod = ($'.$key.' % '.$mod.' );?>';
		$parsestr .= $content;//解析在article标签中的内容
		$parsestr .= '<?php endforeach; endif;?>';
		return  $parsestr;
	}

	/**
	 * 幻灯片
	*/
	public function _flash($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'flash');
		$id = !empty($tag['id'])?$tag['id']:'r';  //定义数据查询的结果存放变量
		$key    = !empty($tag['key'])?$tag['key']:'i';//循环输出时的键值，默认为i
		$mod    = isset($tag['mod'])?$tag['mod']:'2';//列表的奇偶性
		$flashid   = !empty($tag['flashid'])? $tag['flashid'] : '';//幻灯片的id
		$where = ' status=1 ';
		// 多语言查询
		if(APP_LANG){
			$lang=$this->tpl->get('langid');
			$wherelang = ' and lang='.$lang;
		}

		// 幻灯片id的别名处理
		if($flashid && !is_numeric($flashid)){
			if(substr($flashid,0,2)=='T['){
				$T = $this->tpl->get('T');
				preg_match_all("/T\[(.*)\]$/",$flashid,$arr);
				$flashid=$T[$arr[1][0]];
			}else{
				$flashid= $this->tpl->get($flashid);
			}
		}

		if($flashid)$where .=" and id=$flashid ";
		$flash = M('Slide')->where($where)->find();
		if(empty($flash)) return  '';
		
		//查询出幻灯片的图片
		$wherepic = " status=1 and  fid=$flashid ".$wherelang;
		$order=" listorder ASC ,id DESC ";
		$limit= $flash['num'] ? $flash['num'] : 5;//读取幻灯片图片的条数，如果为空，默认为5条。
		$sql="M('Slide_data')->where(\"{$wherepic}\")->order(\"{$order}\")->limit(\"{$limit}\")->select();";
		
		//下面拼接输出语句
		$parsestr  = '';
		$parsestr .= '<?php  $_result='.$sql.';if ($_result): $'.$key.'=0;';
		$parsestr .= 'foreach($_result as $key=>$'.$id.'):';
		$parsestr .= '$'.$key.'++;$mod = ($'.$key.' % '.$mod.' );parse_str($'.$id.'[\'data\'],$'.$id.'[\'param\']);?>';
		$loopend = '<?php endforeach; endif;?>';
 
		$Tpl = TMPL_PATH.'Home/'.THEME_NAME.'/Slide_'.$flash['tpl'].'.html';
		//$flashpath = TMPL_PATH.C('DEFAULT_THEME_NAME').'/Public/flash/';
		$html = file_get_contents($Tpl);
		$html = str_replace(array('{loop}','{/loop}','{$xmlfile}','{$flashfile}','{$flashwidth}','{$flashheight}','{$flashid}'),array($parsestr,$loopend,$flash['xmlfile'],$flash['flashfile'],$flash['width'],$flash['height'],$flashid),$html);

		return  $html;		
	}
	
	/**
	 * 碎片
	*/
	public function _block($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'block');
		$id = !empty($tag['id'])?$tag['id']:'r';  //定义数据查询的结果存放变量
		$key    = !empty($tag['key'])?$tag['key']:'i'; // 未使用
		$pos   = !empty($tag['pos'])? $tag['pos'] : '';// 碎片名称
		$mod    = isset($tag['mod'])?$tag['mod']:'2';//列表的奇偶性
		$blockid   = !empty($tag['blockid'])? $tag['blockid'] : '';// 碎片ID

		// 碎片id别名处理。
		if($blockid && !is_numeric($blockid)){
			if(substr($blockid,0,2)=='T['){
				$T = $this->tpl->get('T');
				preg_match_all("/T\[(.*)\]$/",$blockid,$arr);
				$blockid=$T[$arr[1][0]];
			}else{
				$blockid= $this->tpl->get($blockid);
			}
		}


		$where = ' 1 ';
		// 多语言查询。
		if(APP_LANG){
			$lang=$this->tpl->get('langid');
			$where .= ' and lang='.$lang;
		}
		if($pos)$where .=" and pos='$pos' "; //使用碎片名称查询
		if($blockid)$where .=" and id=$blockid ";// 使用碎片id查询
		
		// 拼接出查询语句。
		$r = M('Block')->where($where)->find();	
		return  $r['content'];		
	}

	/**
	 * 在模板中使用sql语句。
	*/
	public function _db($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'db');
		$id = !empty($tag['id'])?$tag['id']:'r';  //定义数据查询的结果存放变量
		$key    = !empty($tag['key'])?$tag['key']:'i';
		$sql   = !empty($tag['sql'])? $tag['sql'] : '';
		$dbname    = isset($tag['dbname'])?$tag['dbname']:'';
		$mod    = isset($tag['mod'])?$tag['mod']:'2';//列表的奇偶性

		$dbsource=  F('Dbsource');
		$db=$dbsource[$dbname];
		if(empty($db) || empty($sql)) return '';
		$sql = str_replace('{tablepre}',$db['dbtablepre'],$sql);
		$db = 'mysql://'.$db['username'].':'.$db['password'].'@'.$db['host'].':'.$db['port'].'/'.$db['dbname'];
		$sql="M()->db(1,\"{$db}\")->query(\"{$sql}\");";

		//下面拼接输出语句
		$parsestr  = '';
		$parsestr .= '<?php  $_result='.$sql.'; if ($_result): $'.$key.'=0;';
		$parsestr .= 'foreach($_result as $key=>$'.$id.'):';
		$parsestr .= '++$'.$key.';$mod = ($'.$key.' % '.$mod.' );?>';
		$parsestr .= $content;//解析在article标签中的内容
		$parsestr .= '<?php endforeach; endif;?>';
		return  $parsestr;
	}

	/**
	 * 导航树形菜单。
	 */
	public function _nav($attr,$content) {
		$tag        = $this->parseXmlAttr($attr,'nav');
		$level = !empty($tag['level'])? intval($tag['level']) : '1';
		$catid = !empty($tag['catid'])? $tag['catid'] : '0';
		$bcid = !empty($tag['bcid'])? intval($tag['bcid']) : '0';
		$class = !empty($tag['class'])? $tag['class'] : '';
		$id = !empty($tag['id'])? $tag['id'] : 'nav';
		$homefont = !empty($tag['home'])? $tag['home'] : '';
		$enhomefont = !empty($tag['enhome'])? $tag['enhome'] : '';
		$category_arr = $this->tpl->get('Categorys');
        //$site_url = $this->tpl->get('site_url');
		$parsestr ='';
		
		if($catid && !is_numeric($catid)){
			if(substr($catid,0,2)=='T['){
				$T = $this->tpl->get('T');
				preg_match_all("/T\[(.*)\]$/",$catid,$arr);
				$catid=$T[$arr[1][0]];
			}else{
				$catid= $this->tpl->get($catid);
			}
		}
	 
 		if(is_array($category_arr)){
			foreach($category_arr as $r) {
				if(empty($r['ismenu']))  continue;
				$r['name'] = $r['catname'];
				$r['level']=count(explode(',',$r['arrparentid']));
				$array[] = $r;
			}
			import ( '@.ORG.Tree' );
			$tree = new Tree ($array);
			$parsestr = $tree->get_nav($catid,$level,$id,$class,$homefont,FALSE,'',$enhomefont,$lang);
		}unset($category_arr);
		return  $parsestr;
	}
	
	/**
	 * 获取指定栏目的下一级栏目列表。
	 */
	public function _subcat($attr,$content) {
		$tag        = $this->parseXmlAttr($attr,'subcat');
		$id = !empty($tag['id'])?$tag['id']:'r'; //定义数据查询的结果存放变量 result
		$type = !empty($tag['type'])?$tag['type']:'';
		$self = !empty($tag['self'])?'1':'';// 是否包含自己
		$key    = !empty($tag['key'])?$tag['key']:'n';// 循环输出时的键值。默认为n。
		$catid = !empty($tag['catid'])? $tag['catid'] : '0';// 要取得子栏目的栏目id。默认为0。
		if($type) $condition = ' &&  $'.$id.'["type"] == "'.$type.'"';

		
		// $catid别名处理。如果为别名，则取出别名对应的id值。
		if($catid && !is_numeric($catid)){
			if(substr($catid,0,2)=='T['){
				$T = $this->tpl->get('T');
				preg_match_all("/T\[(.*)\]$/",$catid,$arr);
				$catid=$T[$arr[1][0]];
			}elseif(substr($catid,0,1)=='$') {
				$catid   = $catid;
			}else{
				$catid= $this->tpl->get($catid);
			}
		}

		// 拼接出输出语句。
		if($self){ $ifleft = '('; $selfcondition = ') or (  $'.$id.'[\'ismenu\']==1 && intval('.$catid.')==$'.$id.'["id"])';}
		$parsestr ='';
		$parsestr .= '<?php $'.$key.'=0;';
		$parsestr .= 'foreach($Categorys as $key=>$'.$id.'):';
		$parsestr .= 'if('.$ifleft.' $'.$id.'[\'ismenu\']==1 && intval('.$catid.')==$'.$id.'["parentid"] '.$condition.$selfcondition.' ) :';
		$parsestr .= '++$'.$key.';?>';
		$parsestr .= $content;
		$parsestr .= '<?php endif;?>';
		$parsestr .= '<?php endforeach;?>';
		return  $parsestr;
	}

	/**
	 * 所在栏目的位置
	*/
	public function _catpos($attr) {
		$tag        = $this->parseXmlAttr($attr,'catpos');
		$space		= !empty($tag['space']) ? $tag['space'] : '';//分割符号。
 		if(is_numeric($tag['catid'])){
            $catid   = intval($tag['catid']);
			$category_arr = $this->tpl->get('Categorys');
			if(!isset($category_arr[$catid])) return '';
			$arrparentid = array_filter(explode(',', $category_arr[$catid]['arrparentid'].','.$catid));
			foreach($arrparentid as $cid) {
				$parsestr[] = '<a href="'.$category_arr[$cid]['url'].'">'.$category_arr[$cid]['catname'].'</a>';
			}unset($category_arr);
			return implode($space,$parsestr);
		}else{
			//下面拼接输出语句
			$parsestr  = '';
			$parsestr .= '<?php  $arrparentid = array_filter(explode(\',\', $Categorys[$'.$tag['catid'].'][\'arrparentid\'].\',\'.$'.$tag['catid'].'));';
			$parsestr .= 'foreach($arrparentid as $cid):';
			$parsestr .= '$parsestr[] = \'<a href="\'.$Categorys[$cid][\'url\'].\'">\'.$Categorys[$cid][\'catname\'].\'</a>\';?>';
			$parsestr .= '<?php endforeach;echo implode("'.$space.'",$parsestr);?>';
			return  $parsestr;
		}
		
	}
	
	/**
	 * 友情链接
	*/	
	public function _link($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'link');
		$id = !empty($tag['id'])?$tag['id']:'r';  //定义数据查询的结果存放变量，如果为空，默认值为r
		$key    = !empty($tag['key'])?$tag['key']:'i';//循环时获取键值，默认为i。
		$mod    = isset($tag['mod'])?$tag['mod']:'2';//列表的奇偶性

		//$typeid    = isset($tag['$typeid'])?$tag['$typeid']:'';
		$linktype    = isset($tag['linktype'])?$tag['linktype']:'';// 友情链接的类型，默认为空。
		$order  = isset($tag['order'])?$tag['order']:'id desc';// 排序
		$limit  = isset($tag['limit'])?$tag['limit']: '10';// 读取数量
		$field  = isset($tag['field'])?$tag['field']:'*';// 查询的字段，默认查询所有。
		$where = ' status = 1 ';// 数据状态。
		
		// 多语言查询
		if(APP_LANG){
			$lang=$this->tpl->get('langid');
			$where .= ' and lang='.$lang;
		}

		if(substr($tag['typeid'],0,1)=='$') {
			$where .= ' and  typeid=$tag["typeid"]';
        }elseif(is_numeric($tag['typeid'])){
			$where .= " and typeid=".intval($tag['typeid']);
		}else{
            $typeid   = $this->tpl->get($tag['typeid']);
			if($typeid)	$where .= "  and typeid=".intval($typeid);
        }
		
		//链接类型
		if($linktype){
			$where .=  " and  linktype=".$linktype;
		}
		
		// 拼接处查询语句。
		$sql  = "M(\"Link\")->field(\"{$field}\")->where(\"{$where}\")->order(\"{$order}\")->limit(\"{$limit}\")->select();";

		//下面拼接输出语句
		$parsestr  = '';
		$parsestr .= '<?php  $_result='.$sql.'; if ($_result): $'.$key.'=0;';
		$parsestr .= 'foreach($_result as $key=>$'.$id.'):';
		$parsestr .= '++$'.$key.';$mod = ($'.$key.' % '.$mod.' );?>';
		$parsestr .= $content;//解析在article标签中的内容
		$parsestr .= '<?php endforeach; endif;?>';
		return  $parsestr;
	}
	
	/**
	 * 在线客服
	*/
	public function _kefu($attr,$content) {
		$tag    = $this->parseXmlAttr($attr,'kefu');
		$id = !empty($tag['id'])?$tag['id']:'r';  //定义数据查询的结果存放变量
		$type   = !empty($tag['type'])?$tag['type']: '';// 如果类型为空，则为自定义ID或者代码。
		$order  = isset($tag['order'])?$tag['order']:'listorder desc ,id desc';// 排序
		$left =  isset($tag['left'])?$tag['left']: '0';
		$top =  isset($tag['top'])?$tag['top']: '100';
		$where ="status=1";
		
		// 多语言查询。
		if(APP_LANG){
			$lang=$this->tpl->get('langid');
			$where .= ' and lang='.$lang;
		}
		//如果存在客服类别。则查询类别。
		if($type)$where .=" and type in($type)";
		
		$data  = M("Kefu")->field("*")->where($where)->order($order)->select();
		if(empty($data))return  '';// 如果没有添加任何客服，则直接返回控制。
		
		$site_name = $this->tpl->get('site_name');
		
		foreach($data as $key =>$r){
				if($r['name']) $datas.='<li class="tit">'.$r['name'].':</li>';
			// 客服类型为QQ
			if($r['type']==1){
				//http://wpa.qq.com/msgrd?v=3&uin=123456&site=qq&menu=yes
				$skin =	str_replace('q','',$r['skin']);
				$codes=explode("\n",$r['code']);
				foreach((array)$codes as $code){
					if($code){
						$codearr=explode("|",$code);
						$code= $codearr[0];$codename= $codearr[1];
						$datas.='<li><a href="http://wpa.qq.com/msgrd?v=3&uin='.$code.'&site=qq&menu=yes" rel="nofollow"><img border="0" SRC="http://wpa.qq.com/pa?p=1:'.$code.':'.$skin.'" alt="'.$r['name'].'">'.$codename.'</a>';
					}
				}
			}
			// 客服类型为MSN
			elseif($r['type']==2){
				$skin =	str_replace('m','',$r['skin']);
				$codes=explode("\n",$r['code']);
				foreach((array)$codes as $code){
					if($code){
						$codearr=explode("|",$code);
						$code= $codearr[0];$codename= $codearr[1];
						$datas.='<li><a href="msnim:chat?contact='.$code.'"><img src="'.__ROOT__.'/Public/Images/kefu/msn'.$skin.'.gif">'.$codename.'</a></li>';
					}
				}
			}
			// 客服类型为旺旺
			elseif($r['type']==3){
				$skin =	str_replace('w','',$r['skin']);
				$codes=explode("\n",$r['code']);
				foreach((array)$codes as $code){
					if($code){
						$codearr=explode("|",$code);
						$code= $codearr[0];$codename= $codearr[1];
						$datas.='<a target="_blank" href="http://www.taobao.com/webww/ww.php?ver=3&touid='.$code.'&siteid=cntaobao&status='.$skin.'&charset=utf-8" rel="nofollow"><img border="0" src="http://amos.alicdn.com/online.aw?v=2&uid='.$code.'&site=cntaobao&s='.$skin.'&charset=utf-8" alt="'.$r['name'].'" />'.$codename.'</a>';
					}
				}
			}
			// 客服类型为电话
			elseif($r['type']==4){
				$codes=explode("\n",$r['code']);
				foreach((array)$codes as $code){
					if($code){
						$codearr=explode("|",$code);
						$code= $codearr[0];$codename= $codearr[1];
						if($codename) $codename='<label>'.$codename.'</label>';
						$datas.='<li>'.$codename.$code.'</li>';
					}
				}
			}
			// 客服类型为代码
			elseif($r['type']==5){
				$datas.='<li>'.stripcslashes($r['code']).'</li>';
			}
			// 客服类型为贸易通
			elseif($r['type']==6){
				$skin =	str_replace('al','',$r['skin']);
				$codes=explode("\n",$r['code']);
				foreach((array)$codes as $code){
					if($code){
						$codearr=explode("|",$code);
						$code= $codearr[0];$codename= $codearr[1];
						$datas.='<a href="http://web.im.alisoft.com/msg.aw?v=2&uid='.$code.'&site=cnalichn&s=1" target="_blank"><img alt="'.$r['name'].'" src="http://web.im.alisoft.com/online.aw?v=2&uid='.$code.'&site=cnalichn&s='.$skin.'" border="0" />'.$codename.'</a>';
					}
				}
			}
			// 自定义ID或者代码
			else{
				$datas.='<li>'.$r['code'].'</li>';
			}
		}
		$parsestr='';
		$parsestr .='<div class="kefu" id="'.$id.'"><div class="kftop"></div><div class="kfbox"><ul>';
		$parsestr .=$datas;
		$parsestr .='</ul></div><div class="kfbottom"></div></div>';
		$parsestr .='<script> var '.$id.' = new Floaters(); '.$id.'.addItem("'.$id.'",'.$left.','.$top.',"");'.$id.'.play("'.$id.'");</script>';

		return  $parsestr;
	}
	
	/**
	 * 模型内容列表查询
	*/
	public function _list($attr,$content) {

			$tag    = $this->parseXmlAttr($attr,'list');
			$id = !empty($tag['id'])?$tag['id']:'r';  //定义数据查询的结果存放变量，如果为空，默认值为r
			$key    = !empty($tag['key'])?$tag['key']:'i';// 循环输出时的键值，默认为i
			$page   = !empty($tag['page'])? '1' : '0'; // 是否为单页
			$mod    = isset($tag['mod'])?$tag['mod']:'2';//列表的奇偶性
			

			//如果指定了模型名称或者是栏目ID
			if ($tag['name'] || $tag['catid'])
			{   //根据用户输入的值拼接查询条件
				$sql='';
				
				/**
				 * 设置标签值。
				*/
				$module = $tag['name'];//模型名称
				$order  = isset($tag['order'])?$tag['order']:'id desc';//排序字段
				$field  = isset($tag['field'])?$tag['field']:'id,catid,url,title,title_style,keywords,description,thumb,createtime';//输出字段
				$where  = isset($tag['where'])?$tag['where']: ' 1 ';//查询条件，默认为1
				$limit  = isset($tag['limit'])?$tag['limit']: '10';//输出数量，默认为10条
				$status = isset($tag['status'])? intval($tag['status']) : '1';//数据状态
				$ids =  isset($tag['ids'])?$tag['ids']:'';//ID列表
				
				/**
				 *多语言查询。
				 */
				if(APP_LANG){
					$lang=$this->tpl->get('langid');
					$where .= ' and lang='.$lang;
				}
				
				/**
				 *多ID查询。
				 */
				if($ids){
					if($ids && !is_numeric($ids)){
						if(substr($ids,0,2)=='T['){
							$T = $this->tpl->get('T');
							preg_match_all("/T\[(.*)\]$/",$ids,$arr);
							$ids=$T[$arr[1][0]];
						}else{
							$ids= $this->tpl->get($ids);
						}
					}
					if(strpos($ids,',')){
						$where .= " AND id in($ids) ";
					}else{
						$where .= " AND id=$ids ";
					}
				}

				if(ucfirst($module)!='Page')$where .= " AND status=$status ";
				
				// 栏目ID
				if($tag['catid']){
					$onezm  = substr($tag['catid'],0,1);
					if(substr($tag['catid'],0,2)=='T['){
						$T = $this->tpl->get('T');
						preg_match_all("/T\[(.*)\]$/",$tag['catid'],$cidarr);
						$catid=$T[$cidarr[1][0]];
					}elseif(!is_numeric($onezm)) {
						$catid = $this->tpl->get($tag['catid']);

					}else{
						$catid = $tag['catid'];
					}
					if(is_numeric($catid)){
						$category_arr = $this->tpl->get('Categorys');
						$module = $category_arr[$catid]['module'];
						if(!$module) return '';
						if($category_arr[$catid]['child']){
							$where .= " AND catid in(".$category_arr[$catid]['arrchildid'].")";
						}else{
							$where .=  " AND catid=".$catid;
						}
					}elseif($onezm=='$') {
						$where .=  ' AND catid in('.$tag['catid'].')';
					}else{
						$where .=  ' AND catid in('.strip_tags($tag['catid']).')';
					}
				}
				unset($category_arr);

				// 碎片
				if($tag['posid']){
					$posid = $tag['posid'];
					if(!is_numeric($posid) && substr($posid,0,2)=='T['){
						$T = $this->tpl->get('T');
						preg_match_all("/T\[(.*)\]$/",$posid,$cidarr);
						$posid=$T[$cidarr[1][0]];
					}
					if(is_numeric($posid)){
						$where .=  '  AND posid ='.$posid;
					}else{
						$where .=  ' AND posid in('.strip_tags($posid).')';
					}
				}
				
				// 缩略图
				if($tag['thumb']){
					$where .=  ' AND  thumb !=\'\' ';
				}
				
				/**
				 * 拼接出查询语句。
				*/
				$sql  = "M(\"{$module}\")->field(\"{$field}\")->where(\"{$where}\")->order(\"{$order}\")->limit(\"{$limit}\")->select();";
			}else{ 
				/**
				 * 查询语句标签。
				*/
				if (!$tag['sql']) return ''; //排除没有指定model名称，也没有指定sql语句的情况
				$sql = "M()->query(\"{$tag['sql']}\")";
			}

			/**
			 * 拼接出输出语句。
			*/
			$parsestr  = '';
			$parsestr .= '<?php  $_result='.$sql.'; if ($_result): $'.$key.'=0;';
			$parsestr .= 'foreach($_result as $key=>$'.$id.'):';
			$parsestr .= '++$'.$key.';$mod = ($'.$key.' % '.$mod.' );?>';
			$parsestr .= $content;//解析在article标签中的内容
			$parsestr .= '<?php endforeach; endif;?>';
			return  $parsestr;
	}

}
?>