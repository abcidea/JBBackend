<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="./Public/Js/jquery.min.js"></script> 
<script type="text/javascript" src="./Public/Js/jquery.artDialog.js?skin=default"></script> 
<script type="text/javascript" src="./Public/Js/iframeTools.js"></script>
<link rel='stylesheet' type='text/css' href='./Public/Css/style.css'>
<title><?php echo L('welcome');?>后台管理管理系统</title>
</head>
<body style="background:#E2E9EA">
<!--后台管理主页-->
<div id="header" class="header">
	<div class="logo"><a href="#" target="_blank"><img src="./Public/Images/admin_logo.gif" width="204"></a></div>
	<div class="nav f_r"> <!--<a href="#" target="_blank"><?php echo L('org_url');?></a> <i>|</i> <a href="#" target="_blank"><?php echo L('license');?></a>  <i>|</i> <a href="#" target="_blank"><?php echo L('org_bbs');?></a>  <i>|</i> <a href="#" target="_blank"><?php echo L('help');?></a> <i>|</i> &nbsp;&nbsp;--></div>

	<div class="nav">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo L('welcome_user'); echo (session('username')); ?>  <i>|</i> [<?php echo ($usergroup); ?>]  <i>|</i> [<a href="<?php echo U('Login/logout');?>" target="_top"><?php echo L('logout');?></a>]  <i>|</i> <a href="<?php echo ($site_url); ?>" target="_blank"><?php echo L('home_index');?></a> <i>|</i> <a href="javascript:void(0);" onclick="gocacheurl();"><?php echo L('UPDATE_CACHE');?></a> </div>

	<div class="topmenu">
		<ul>
		<?php if(is_array($menuGroupList)): $i = 0; $__LIST__ = $menuGroupList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?><li id="menu_<?php echo ($tag["id"]); ?>"><span><a href="javascript:void(0);" onClick="sethighlight(<?php echo ($tag["id"]); ?>);"><?php echo ($tag["name"]); ?></a></span></li><?php endforeach; endif; else: echo "" ;endif; ?>
		</ul>
	</div>
	<div class="header_footer"></div>
</div>


<div id="Main_content">

	<div id="MainBox" >
	    <div class="main_box">
			<iframe name="main" id="Main" src='<?php echo U("Index/main");?>' frameborder="false" scrolling="auto"  width="100%" height="auto" allowtransparency="true"></iframe>
		</div>
    </div>

	<div id="leftMenuBox">
    	<div id="leftMenu">
			<div style="padding-left:12px;_padding-left:10px;">				
				<?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?><dl id="nav_<?php echo ($r['bnav']['id']); ?>">
						<dt><?php echo ($r['bnav']['name']); ?></dt>
						<?php if(is_array($r[nav])): $i = 0; $__LIST__ = $r[nav];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><dd id="nav_<?php echo ($item['id']); ?>"><span  onclick="javascript:gourl('<?php echo ($item['id']); ?>','<?php echo U($item['model'].'/'.$item['action'],$item['data']);?>')"><a href="<?php echo U($item['model'].'/'.$item['action'],$item['data']);?>" target="main"><?php echo ($item['name']); ?></a><?php if($r[bnav]['model']=='Category') : ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo U($item['model'].'/add',$item['data']);?>" id="<?php echo ($i); ?>" target="main"><?php echo L(add);?></a><?php endif;?></span></dd><?php endforeach; endif; else: echo "" ;endif; ?>
					</dl><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>
		</div>

		<div id="Main_drop">
			<a  href="javascript:toggleMenu(1);" class="on"><img src="./Public/Images/admin_barclose.gif" width="11" height="60" border="0"  /></a>   
			<a  href="javascript:toggleMenu(0);" class="off" style="display:none;"><img src="./Public/Images/admin_baropen.gif" width="11" height="60" border="0"  /></a>  
		</div>
    </div>

</div>

<div id="footer" class="footer" >Powered by <a href="#" target="_blank">丽博网络</a>&nbsp;<?php echo VERSION;?>&nbsp;<?php echo UPDATETIME;?> Copyright 2005-2013 <span id="run"></span></div>

<script language="JavaScript">
if(!Array.prototype.map)
Array.prototype.map = function(fn,scope) {
  var result = [],ri = 0;
  for (var i = 0,n = this.length; i < n; i++){
	if(i in this){
	  result[ri++]  = fn.call(scope ,this[i],i,this);
	}
  }
return result;
};
var getWindowWH = function(){
	  return ["Height","Width"].map(function(name){
	  return window["inner"+name] ||
		document.compatMode === "CSS1Compat" && document.documentElement[ "client" + name ] || document.body[ "client" + name ]
	});
}
window.onload = function (){
	if(!+"\v1" && !document.querySelector) { //IE6 IE7
	 document.body.onresize = resize;
	} else { 
	  window.onresize = resize;
	}
	function resize() {
		wSize();
		return false;
	}
}
function wSize(){
	var str=getWindowWH();
	var strs= new Array();
	strs=str.toString().split(","); //字符串分割
	var h = strs[0] - 95-30;
	$('#leftMenu').height(h);
	$('#Main').height(h); 
}
wSize();


function sethighlight(n) {
	 $('.topmenu li span').removeClass('current');
	 $('#menu_'+n+' span').addClass('current');
	 $('#leftMenu dl').hide();
	 $('#nav_'+n).show();
	 $('#leftMenu dl dd').removeClass('on');
	 $('#nav_'+n+' dd').eq(0).addClass('on');
	 url = $('#nav_'+n+' dd a').eq(0).attr('href');
	 window.main.location.href= url;
}
sethighlight(1);
function gourl(n,url){
	$('#leftMenu dl dd').removeClass('on');
	$('#nav_'+n).addClass('on');
	window.main.location.href=url;
}

function gocacheurl(){
	mainurl = window.main.location.href;
	window.main.location.href= "<?php echo U('Admin/Index/cache');?>&forward="+encodeURIComponent(mainurl);
}

function toggleMenu(doit){
	if(doit==1){
		$('#Main_drop a.on').hide();
		$('#Main_drop a.off').show();
		$('#MainBox .main_box').css('margin-left','24px');
		$('#leftMenu').hide();
	}else{
		$('#Main_drop a.off').hide();
		$('#Main_drop a.on').show();
		$('#leftMenu').show();
		$('#MainBox .main_box').css('margin-left','224px');
	}
}	
</script>
</body>
</html>