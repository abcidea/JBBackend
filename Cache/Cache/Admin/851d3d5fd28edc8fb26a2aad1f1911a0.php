<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo C('DEFAULT_CHARSET');?>" />
<title><?php echo L('welcome');?></title>
<link rel="stylesheet" type="text/css" href="__ROOT__/Public/Css/style.css" /> 
<script type="text/javascript" src="__ROOT__/Public/Js/jquery.min.js"></script> 
<script type="text/javascript" src="__ROOT__/Public/Js/jquery.artDialog.js?skin=default"></script> 
<script type="text/javascript" src="__ROOT__/Public/Js/iframeTools.js"></script>
<script type="text/javascript" src="__ROOT__/Public/Js/jquery.form.js"></script> 
<script type="text/javascript" src="__ROOT__/Public/Js/jquery.validate.js"></script> 
<script type="text/javascript" src="__ROOT__/Public/Js/MyDate/WdatePicker.js"></script> 
<script type="text/javascript" src="__ROOT__/Public/Js/jquery.colorpicker.js"></script> 
<script type="text/javascript" src="__ROOT__/Public/Js/my.js"></script> 
<script type="text/javascript" src="__ROOT__/Public/Js/swfupload.js"></script> 

<script language="JavaScript">
<!--
var ROOT =	 '__ROOT__';
var URL = '__URL__';
var APP	 =	 '__APP__';
var PUBLIC = '__PUBLIC__';
function confirm_delete(url){
	art.dialog.confirm("<?php echo L('real_delete');?>", function(){location.href = url;});
}
//-->
</script>
</head>
<body width="100%">
<!--后台主页头部-->
<div id="loader" ><?php echo L('load_page');?></div>
<div id="result" class="result none"></div>
<div class="mainbox">

<?php if(empty($_GET['isajax'])): ?><div id="nav" class="mainnav_title">

	<div id="lang">
	<?php if(APP_LANG): parse_str($_SERVER['QUERY_STRING'],$urlarr); unset($urlarr[l]); $url='index.php?'.http_build_query($urlarr); ?>
		<?php if(is_array($Lang)): $i = 0; $__LIST__ = $Lang;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$langvo): $mod = ($i % 2 );++$i;?><a href="<?php echo ($url); ?>&l=<?php echo ($langvo["mark"]); ?>" <?php if($langvo[mark]==$_SESSION['FCDREP_lang']): ?>class="on"<?php endif; ?>><?php echo ($langvo["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; endif; ?>
	</div>
	<ul><a href="<?php echo U($nav[bnav][model].'/'.$nav[bnav][action],$nav[bnav][data]);?>"><?php echo ($nav["bnav"]["name"]); ?></a>|
	<?php if(is_array($nav["nav"])): $i = 0; $__LIST__ = $nav["nav"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vonav): $mod = ($i % 2 );++$i; if($vonav[data][isajax]): ?><a href="javascript:void(0);" onclick="openwin('<?php echo ($vonav[action]); ?>','<?php echo U($vonav[model].'/'.$vonav[action],$vonav[data]);?>','<?php echo ($vonav["name"]); ?>',600,440)"><?php echo ($vonav["name"]); ?></a>|
	<?php else: ?>
	<a href="<?php echo U($vonav[model].'/'.$vonav[action],$vonav[data]);?>"><?php echo ($vonav["name"]); ?></a>|<?php endif; endforeach; endif; else: echo "" ;endif; ?></ul>
	</div>
	<script>
	//|str_replace=__ROOT__.'/index.php','',###
	var onurl ='<?php echo ($_SERVER["REQUEST_URI"]); ?>';
	jQuery(document).ready(function(){
		$('#nav ul a ').each(function(i){
		if($('#nav ul a').length>1){
			var thisurl= $(this).attr('href');
			thisurl = thisurl.replace('&menuid=<?php echo cookie("menuid");?>','');
			if(onurl.indexOf(thisurl) == 0 ) $(this).addClass('on').siblings().removeClass('on');
		}else{
			$('#nav ul').hide();
		}
		});
		if($('#nav ul a ').hasClass('on')==false){
		$('#nav ul a ').eq(0).addClass('on');
		}
	});
	</script><?php endif; ?>
<!--管理主页框架-->
<!--模块管理 - 创建模块与修改模块-->

<form name="myform" id="myform" action="<?php if($action_name=='add'): echo U($module_name.'/insert'); else: echo U($module_name.'/update'); endif; ?>" method="post">

<table cellpadding=0 cellspacing=0 class="table_form" width="100%">

	<tr>
		  <td width="120"><font color="red">*</font><?php echo L(module_name);?>
		  </td>
		  <td>
			<input type="text" id="title" name="title" value="<?php echo ($vo["title"]); ?>" size="20" class="input-text required" minlength="2"    maxlength="30"  title="<?php echo L(module_tip_title);?>" />
		  </td>
	</tr>
	<tr>
		  <td width="120"><font color="red">*</font><?php echo L(module_table);?>
		  </td>
		  <td>
			<input type="text" id="name" name="name" <?php if($action_name=='edit'): ?>disabled="disabled"<?php endif; ?> value="<?php echo ($vo["name"]); ?>" size="20" class="input-text"  validate="required:true, english:true,remote: '<?php echo U($module_name.'/insert?isajax=1');?>' ,minlength:2, maxlength:30" />
		  </td>
	</tr>
	<tr>
		  <td><?php echo L(module_listfields);?>
		  </td>
		  <td><input type="text" id="listfields" name="listfields"  class="input-text" size="55" value="<?php if(empty($vo['listfields'])): ?>*<?php else: echo ($vo["listfields"]); endif; ?>" /> <?php echo L(examples);?>:id,title,url,catid
		  </td>
	</tr>
	<tr>
		  <td><?php echo L(module_issearch);?>
		  </td>
		  <td><?php echo Form::radio(array('field'=>'issearch','options'=>array('1'=>L('yes'),'0'=>L('no'))),$vo['issearch']);?></td>
		  </td>
	</tr>

	<tr>
		  <td><?php echo L(module_ispost);?>
		  </td>
		  <td><?php echo Form::radio(array('field'=>'ispost','options'=>array('1'=>L('yes'),'0'=>L('no'))),$vo['ispost']);?></td>
		  </td>
	</tr>

	<tr>
		<td><?php echo L('postgroup');?></td>
		<td><?php echo Form::checkbox(array('field'=>'postgroup','options'=>$Role,'options_key'=>'key,name'),$vo['postgroup']);?></td>
	</tr>


	<tr>
		  <td><?php echo L(module_description);?>
		  </td>
		  <td><textarea id="description" name="description" rows=4"" cols="55"><?php echo ($vo["description"]); ?></textarea>
		  </td>
	</tr>

<?php if($action_name=='add'): ?><tr>
		  <td><?php echo L(module_table_type);?>
		  </td>
		  <td><input type="radio" name="emptytable" value="1"   /> <?php echo L(module_empty_table);?> <input  type="radio"  name="emptytable" value="0"  checked /> <?php echo L(module_news_table);?>
		  </td>
	</tr>
	<input type="hidden" id="type" name="type" value="<?php echo ($_GET[type]); ?>"/><?php endif; ?>
</table>
<div  class="btn">
<?php if($vo['id']!=''): ?><input TYPE="hidden" name="id" value="<?php echo ($vo["id"]); ?>"><?php endif; ?>
<INPUT TYPE="submit"  value="<?php echo L('dosubmit');?>" class="button" >
<input TYPE="reset"  value="<?php echo L('cancel');?>" class="button">
</div>
</form>

</div>

</body></html>
</body>
</html>