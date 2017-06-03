<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo ($seo_title); ?>-<?php echo ($site_name); ?></title>
<meta name="robots" content="index, follow" />
<meta name="author" content="<?php echo ($site_name); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta content="<?php echo ($seo_description); ?>" name="description">
<meta name="Copyright" content="Copyright (c) 2013 www.jinnt.com" />
<meta content="<?php echo ($seo_keywords); ?>" name="keywords">
<link data-module="10001" id="metuimodule" href="../Public/css/style.css" type="text/css" rel="stylesheet">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<link href="__ROOT__/index.php?m=Format&a=rss" rel="alternate" type="application/rss+xml" title="RSS 2.0" />
<script src="__PUBLIC__/Js/App.js"></script>
<script src="http://siteapp.baidu.com/static/webappservice/uaredirect.js" type="text/javascript"></script>
<script type="text/javascript">uaredirect("/index.php?g=Wap");</script>
<script type="text/javascript">
var APP	 =	 '__APP__';
var ROOT =	 '__ROOT__';
var PUBLIC = '__PUBLIC__';
</script>
</head>
<body id="page">
<div class="top">

	     <div class="maxtop w1000">
		   <div class="logo"><img src="../Public/images/logo.jpg"/></div>
		   <div class="adyu"><img src="../Public/images/adyu.jpg"/></div>
		   <div class="favourite"><a onclick="javascript:try{window.external.AddFavorite(document.location.href,'丰韬广告')} catch (e) {alert('您的浏览器不支持此功能，请使用Ctrl+D进行添加'); }" href="#">加入收藏</a> |  <a href="http://weibo.com/ledzhaopai" target="_blank">企业微博</a> | <a href="/index.php?m=Format&a=rss">RRS订阅</a> | <a href="/sitemap.html">网站地图</a> | <a href="/sitemap.xml"　target="_blank">XML地图</a></div>
		   <div class="tles"><p>全国统一热线、企业QQ：</p><p><font>400-0326-008</font></p></div>
		 </div>

         <div class="navsbg">
		 <div class="navs w1000">
		 <ul>
              <li class="<?php if($ishome=='home') : ?>cur<?php endif;?>"><a href="/">丰韬首页</a></li>
			  <?php $i=0;foreach($Categorys as $key=>$nav):if( $nav['ismenu']==1 && intval(0)==$nav["parentid"] ) :++$i; if($i < 7) : ?>
				   
				   <li class="<?php if($nav[id]==$bcid) : ?>cur<?php endif;?>"><a href="<?php echo ($nav["url"]); ?>"><?php echo ($nav["catname"]); ?></a></li>
				   
				  <?php endif; endif; endforeach;?>
			  
		 </ul>
         </div>
		 </div>

		 <div class="search w1000">
		                
              <div class="gjc l"><span>服务直通车>>></span> 
			  <?php $n=0;foreach($Categorys as $key=>$r):if( $r['ismenu']==1 && intval(13)==$r["parentid"] ) :++$n;?><a href="<?php echo ($r["url"]); ?>"><?php echo ($r["catname"]); ?></a><?php endif; endforeach;?>
			  </div>
              
			  <div class="searching r">
                   <form name="infosearch" method="post" action="/index.php?m=Search">
				   <input type="hidden" name="lang" value="1">
				   <div class="yi_input l">
				    <input onfocus="if (this.value=='请输入关键词') this.value='';" onblur="if (this.value=='') this.value='请输入关键词';" value="请输入关键词" name="keyword" type="text" class="infoInput" size="20"/>
				   </div>
				   <span class="r"><input type="submit" value="搜索"></span>
				   </form>
			  </div>

		 </div>

	
</div>
<?php if($ishome=='home') : ?>
<div class="banner">

<script type="text/javascript" src="__PUBLIC__/Js/jquery.switchable.min.js"></script>
<style>
.slide-panel {position:relative;width:920px;height:300px;overflow:hidden;}
.slide-panel  li {width:920px;height:300px;}
.switchable-triggers { position:absolute; right:3px; bottom:6px; }
.switchable-triggers li {display:inline-block;border:1px solid #333;float:left;width:16px;height:16px;margin:0 3px;background:#454545;;color:#FFF;font-size:13px;
    line-height:16px; text-align:center; cursor:pointer; }
.switchable-triggers li.current {border:1px solid #BF0000;background:#EF0000;color:#fff;}
</style>
<div class="slide1">
	<div id="slide_1" class="slide-panel"><ul>
		<?php  $_result=M('Slide_data')->where(" status=1 and  fid=1  and lang=1")->order(" listorder ASC ,id DESC ")->limit("5")->select();;if ($_result): $i=0;foreach($_result as $key=>$r):$i++;$mod = ($i % 2 );parse_str($r['data'],$r['param']);?>
		 <li><a href="<?php echo ($r["link"]); ?>" target="_blank"><img src="<?php echo ($r['pic']); ?>" alt="<?php echo ($r['title']); ?>"></a> </li>
		<?php endforeach; endif;?>
	</ul></div>
</div> 
<script>
jQuery(document).ready(function($){
   var slide = $('#slide_1').switchable({
    putTriggers: 'appendTo',
    panels: 'li',
    initIndex: -1,
    effect: 'scrollUp',
    easing: 'cubic-bezier(.455, .03, .515, .955)',
    end2end: true,
    loop: false, 
    autoplay: true,
    interval: 5,
    api: true
  });
});
</script>
</div>
<?php endif;?>
		<div class="main">

    <div class="main_rows w1000">

	<div class="main_row n8">

	     <div class="main_row_l l">
		      <div class="main_row_title">年专注LED招牌亮化工程，经验丰富</div>
              
			  <div class="main_row_content">
			  <p><b>深圳市丰韬广告有限公司</b>是一家集设计、制作、安装于一体的综合性LED发光工程的广告工程公司，我公司专业致力于各行业企事业单位的LED发光字工程。</p>
			  <p>本公司拥有专业化先进的生产设备、现代化完善的管理制度及质量保证体系，更拥有高素质的技术开发人员及技术过硬、训练有素的员工。</p>
			  </div>

		 </div>

		 <div class="main_row_r r">
		      <img src="../Public/images/yi_yjp.jpg" alt="">
		 </div>

	</div>

	<div class="main_row gcc">

	     <div class="main_row_l l">
              <img src="../Public/images/yi_cf.jpg" alt="">
		 </div>

		 <div class="main_row_r r">
              <div class="main_row_title r">为您提供最优质的定制服务</div>
			  
			  <div class="main_row_content">
                   <p>丰韬广告现拥有1000平米的制作工场； 10多年广告工程设计和施工经验；多年的金牌设计师团队；雄厚的施工实力和高效的执行团队为您提供性价比最优的一站式品牌服务。并与中国移动通信、7天连锁酒店、华润万家、腾邦国际、能源物流、招商银行、DHL等大型龙头企业建立长期合作关系。我们用心把控每一个环节，注重每一个细节。</p>
				   <div class="bghj">
                          <img src="../Public/images/yi_bghj.jpg" alt="">
				   </div>
			  </div>
		 </div>
	</div>

	<div class="main_row clou">
	     <div class="main_row_title">全国首家承诺免费维保</div>

        <div class="main_row_l l">
              <div class="anquan"><img src="../Public/images/yi_dp.jpg" alt=""></div>
			  <p>丰韬广告与中国人寿保险有长期战略合作伙伴关系,心系员工安全问题，从业8年，无施工安全记录！让您放心,两年高空坠物有中国人寿保证第三方财产保险。</p>
		</div>

		<div class="main_row_r r">
         <ul>
              <li>
			       <div class="small_block">
				        <div class="small_block_title"><img src="../Public/images/yi_yx.jpg" alt=""><span><font>优秀</font>的工程团队</span></div>
						<div class="small_block_content r">
						<p>• 严谨的管理班子</p>
						<p>• 专业的生产人员</p>
						<p>• 经验丰富的施工队伍</p>
						</div>
				   </div>
			  </li>

              <li>
			       <div class="small_block r">
				        <div class="small_block_title"><span><font>严谨</font>的采购流程</span> <img src="../Public/images/yi_yj.jpg" alt=""></div>
						<div class="small_block_content l">
						<p>• 对供应商采用品质淘汰制</p>
						<p>• 丰韬自主研发老化系统</p>
						<p>• 内部二次QC自检</p>
						</div>
				   </div>
			  </li>

              <li>
			       <div class="small_block">
				        <div class="small_block_title"><img src="../Public/images/yi_xj.jpg" alt=""><span><font>先进</font>的施工设备</span></div>
                        <div class="blank20"></div>
						<div class="small_block_content r">
						<p>• 雕刻机</p>
						<p>• 开槽拉边机</p>
						<p>• 吸塑机</p>
						<p>• 专业的生产线</p>
						</div>
				   </div>
			  </li>

              <li>
			       <div class="small_block r">
				        <div class="small_block_title"><span><font>两年</font>免费维护</span> <img src="../Public/images/yi_ln.jpg" alt=""></div>
						<div class="blank20"></div>
						<div class="small_block_content l">
						<p>• 大面积局部灭灯全程维护</p>
						<p>• 保险公司第三方财产险</p>
						</div>
				   </div>
			  </li>

		 </ul>
		</div>

	</div>

	</div><!--END main_rows-->
    
    <div class="blank10"></div>

	<div class="ab w1000">
         
	</div>

   <div class="blank10"></div>

    <div class="pps w1000">
	     <div class="pps_title"><h3><b><font>1000</font></b><b>家大型客户案例共同见证</b><font>对品质负责，让客户满意，您的需求就是我们的价值！</font></h3></div>
		 <div class="pps_contents">
         <span class="l"><a href="javascript:;" class="pprev"><img  src="../Public/images/yi_bth_l.jpg"></a></span>
		 <div class="pps_content l">
		 <ul>
		     <?php  $_result=M("Product")->field("id,catid,url,title,title_style,keywords,description,thumb,createtime")->where(" 1  and lang=1 AND status=1  AND catid=7")->order("id desc")->limit("5")->select();; if ($_result): $i=0;foreach($_result as $key=>$r):++$i;$mod = ($i % 2 );?><li><div class="pps-box"><a href="javascript:;"><img  width="160" height="54" alt="<?php echo ($r["title"]); ?>" src="<?php echo (thumb($r["thumb"],160,54,1)); ?>"></a><p><?php echo ($r["title"]); ?></p></div></li><?php endforeach; endif;?>
			  
		 </ul>
		 </div>
		 <span class="l"><a href="javascript:;" class="pnext"><img src="../Public/images/yi_bth_r.jpg"></a></span>
		 </div>
	</div>
    
	<div class="blank10"></div>

    <div class="hzp w1000">
         <img width="1000" src="../Public/images/yi_hzp.jpg" alt=""/>
    </div>
    
	<div class="blank10"></div>

	<div class="ab w1000">
         
	</div>

	<div class="vs w1000">
	  <div class="vsbox">
         <div class="vs_title">

		 <img src="../Public/images/yi_vs_title.jpg" alt=""/>
			 <ul id="iClassIndex">
				<li class="link" onmouseover="nTab2(this,0);"><a title="接线方式" href=""></a></li>
				<li class="current" onmouseover="nTab2(this,1);"><a title="变压器" href=""></a></li>
				<li class="link" onmouseover="nTab2(this,2);"><a title="线材" href=""></a></li>
				<li class="link" onmouseover="nTab2(this,3);"><a title="灯" href=""></a></li>
			</ul>
		 </div>
		 
		 <script src="../Public/Js/mytabs.js" type="text/javascript"></script>
         <div class="vs_content">
		 <div id="iClassIndex_sub0" style="display: none;">
		 <div class="vs_l l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/hanjie_you_1.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/hanjie_you_2.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/hanjie_you_3.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
			  </div>

         </div>

		 <div class="vs_m l">
         <img src="../Public/images/yi_vsing.jpg" >
         </div>

		 <div class="vs_r l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/hanjie_lie_1.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/hanjie_lie_2.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/hanjie_lie_3.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
			  </div>
         </div>
		 </div>
		 <div id="iClassIndex_sub1" style="display: none;">
		 
		 <div class="vs_l l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/yi_vs_cl.jpg" ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.孔间距比较密，保证亮度.孔间距米，</p>
                   <p>2.光夜间穿透力和效果才有保证</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/yi_vs_cl.jpg" ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.孔间距比较密，保证亮度.孔间距米，</p>
                   <p>2.光夜间穿透力和效果才有保证</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/yi_vs_cl.jpg" ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.孔间距比较密，保证亮度.孔间距米，</p>
                   <p>2.光夜间穿透力和效果才有保证</p>
			  </div>

         </div>

		 <div class="vs_m l">
         <img src="../Public/images/yi_vsing.jpg" >
         </div>

		 <div class="vs_r l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/yi_vs_cl.jpg" ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.孔间距比较密，保证亮度.孔间距米，</p>
                   <p>2.光夜间穿透力和效果才有保证</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/yi_vs_cl.jpg" ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.孔间距比较密，保证亮度.孔间距米，</p>
                   <p>2.光夜间穿透力和效果才有保证</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/yi_vs_cl.jpg" ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.孔间距比较密，保证亮度.孔间距米，</p>
                   <p>2.光夜间穿透力和效果才有保证</p>
			  </div>
         </div>		 
		 
		 </div>
		 <div id="iClassIndex_sub2" style="display: block;">
		 
		 <div class="vs_l l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_you_1.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_you_2.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_you_3.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_you_4.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>			  

         </div>

		 <div class="vs_m l">
         <img src="../Public/images/yi_vsing.jpg" >
         </div>

		 <div class="vs_r l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_lie_1.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_lie_2.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_lie_3.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>
             <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/xian_lie_4.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.根据字的大小来为客户建议围边。</p>
                   <p>2.围边高度够了，立体效果就非常好。</p>
				   <p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>			  
         </div>
		 
		 </div>
		 <div id="iClassIndex_sub3" style="display: none;">
		 
		 <div class="vs_l l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/deng_you_1.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.孔间距比较密，保证亮度。</p>
                   <p>2.孔间距米，光夜间穿透力和效果才有保证。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/deng_you_2.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
				   <p>1.边沿是激光焊焊接。</p>
                   <p>2.边沿打磨2遍。</p>
				   <p>3.打磨好后不够精细的地方补原子灰。</p>
				   <p>4.补好原子灰再打磨一遍。</p>
				   <p>5.最后才烤漆。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/deng_you_3.jpg" width=235 height=172 ><span><img src="../Public/images/yi_you.jpg" ></span></div>
					<p>1.根据字的大小来为客户建议围边。</p>
					<p>2.围边高度够了，立体效果就非常好。</p>
					<p>3.围边厚度一定要与字的大小成正比。</p>
			  </div>

         </div>

		 <div class="vs_m l">
         <img src="../Public/images/yi_vsing.jpg" >
         </div>

		 <div class="vs_r l">
              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/deng_lie_1.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.孔间距比较疏，亮度不够。</p>
                   <p>2.孔间距米，光夜间穿透力和效果没有保证。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/deng_lie_2.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.边沿是手工焊接。</p>
                   <p>2.边沿没打磨</p>
				   <p>3.粗糙的边沿直接烤漆了。</p>
			  </div>

              <div class="vs_cl">
			       <div class="vs_cl_img"><img src="../Public/images/deng_lie_3.jpg" width=235 height=172 ><span><img src="../Public/images/yi_nie.jpg" ></span></div>
				   <p>1.如果字比较大围边太矮出来的立体效果不好。</p>
                   <p>2.围边厚度与字不成正比挂上去会看上去像一张纸。</p>
			  </div>
         </div>
		 
		 </div>
		 </div><!--END VScontent-->
		 
		 
		 
       </div>
	</div><!--END vs-->
    
	<div class="blank10"></div>

	<div class="block case w1000">
         
		 <div class="block-l l">
		      <div class="block-title case-l-title">丰韬案例中心</div>
			  <div class="catsbox">
			  <?php $k=0;foreach($Categorys as $key=>$r):if( $r['ismenu']==1 && intval(1)==$r["parentid"] ) :++$k;?><dl <?php if($k==2) : ?>class="frist"<?php endif;?>>
			  <dt><?php echo ($r["catname"]); ?></dt>
			  <?php if($r[child] && $k==1) : ?>
			  
			     <?php $n=0;foreach($Categorys as $key=>$rr):if( $rr['ismenu']==1 && intval($r[id])==$rr["parentid"] ) :++$n;?><dd class="case-cats"><a href="<?php echo ($rr["url"]); ?>"><?php echo ($rr["catname"]); ?></a></dd><?php endif; endforeach;?>
			  
			  <?php endif;?>
			  </dl><?php endif; endforeach;?>
			  </div>
		 </div>
		 
		 <div class="block-r r">
		      <div class="block-r-title"><span class="l">工程案例</span><font>做良心工程，让时间来验证！</font><a class="gmoer r" href="<?php echo ($Categorys[1][url]); ?>">更多</a></div>
			  <ul>

                   <?php  $_result=M("Article")->field("id,catid,url,title,title_style,keywords,description,thumb,createtime")->where(" 1  and lang=1 AND status=1  AND catid in(1,2,3,10,16)  AND posid =1")->order("id desc")->limit("6")->select();; if ($_result): $i=0;foreach($_result as $key=>$r):++$i;$mod = ($i % 2 );?><li>
				   <div class="case-box">
				        <a href="<?php echo ($r["url"]); ?>"><img width="180" height="135" src="<?php echo (thumb($r["thumb"],180,135,1)); ?>" alt="<?php echo ($r["title"]); ?>"></a><p><a href="<?php echo ($r["url"]); ?>"><b><?php echo (str_cut($r["title"],30)); ?></b><?php echo (str_cut($r["description"],50)); ?></a></p>
					</div>
				   </li><?php endforeach; endif;?>

			  </ul>
		 </div>

	</div><!--END case-->

    <div class="blank10"></div>
	<div class="ab w1000">
         
	</div>
    <div class="blank10"></div>

	<div class="block service w1000">
         <div class="block-l l">
		      <div class="block-title case-l-title">服务项目</div>
			  <div class="catsbox">

			  <?php $k=0;foreach($Categorys as $key=>$r):if( $r['ismenu']==1 && intval(2)==$r["parentid"] ) :++$k;?><dl <?php if($k==2) : ?>class="frist"<?php endif;?>>
			  <dt><?php echo ($r["catname"]); ?></dt>
			  <?php if($r[child]) : ?>
			     <?php $n=0;foreach($Categorys as $key=>$rr):if( $rr['ismenu']==1 && intval($r[id])==$rr["parentid"] ) :++$n;?><dd class="case-cats"><a href="<?php echo ($rr["url"]); ?>"><?php echo ($rr["catname"]); ?></a></dd><?php endif; endforeach;?>
			  <?php endif;?>
			  </dl><?php endif; endforeach;?>

			  </div>
		 </div>
		 <div class="block-r r">
              <div class="block-r-title"><span class="l">丰韬知道</span><font>LED招牌常见问题解答</font><a class="gmoer r" href="<?php echo ($Categorys[21][url]); ?>">更多</a></div>

			  <div class="block-content" style="height:267px;">
			      
				    
				  
			  </div><!--END block-content-->

			  <div class="block-r-title"><span class="l">行业资讯</span> <a class="gmoer r" href="<?php echo ($Categorys[22][url]); ?>">更多</a></div>
			  <div class="block-content hyzx">
			       
			  </div><!--END block-content-->

			  <div class="block-r-title"><span class="l">展会信息</span> <a class="gmoer r" href="<?php echo ($Categorys[23][url]); ?>">更多</a></div>
			  <div class="block-content zhxx">
			  <div class="block-con-content">
			  <ul>
			       
			  </ul>
			  </div>
			  <ul class="list">
			       
			  </ul>
			  </div><!--END block-content-->
			  </div>
 

		 </div>
	</div><!--END service-->

    <div class="blank10"></div>

    <div class="block gsnew w1000">
    <div class="block-l l">
         <div class="block-title case-l-title">公司新闻</div>
		 <div class="catsbox">
	     <dl>
             
			  
			  

		 </dl>
		 </div>
	</div>
	<div class="block-r r">
	     <div class="block-r-title"><span class="l">公司活动</span> <a class="gmoer r" href="<?php echo ($Categorys[25][url]); ?>">更多</a></div>
		 <ul> 
		      
		 </ul>
	</div>
	</div><!--END gsnew-->

    <div class="blank10"></div>
	<div class="gswj w1000">
         <div class="gswj-title"><span class="l">公司环境</span> <a class="gmoer r" href="<?php echo ($Categorys[26][url]); ?>">更多</a></div>
		 <div class="gswj-content">
		    
			<span class="first l"><a href="javascript:;" class="gprev"><img src="../Public/images/yi_bth_l.jpg"></a></span>
            <div class="gswj-content-lists l">
			
			<div class="highslide-gallery-backup">
			<ul>
            
			</ul>
			</div>
			
			</div>
			<span class="end l"><a href="javascript:;" class="gnext"><img src="../Public/images/yi_bth_r.jpg"></a></span>
		
		 </div>
	</div><!--END gswj-->
    
	<div class="blank10"></div>

    <div class="dnhb w1000">
         <div class="dnhb-title"><span class="l">战略合作伙伴</span> <a class="gmoer r" href="<?php echo ($Categorys[27][url]); ?>">更多</a></div>
		 <div class="dnhb-content">
			<ul>
			
			</ul>		 
		 </div>
	</div><!--END dnhb-->

    <div class="blank10"></div>

    <div class="hye w1000">
         <div class="hye-title">行业分类导航条</div>
		 <div class="hye-content">
			<ul>
		    <li><div class="dnhb-title"><span class="l">地产</span><a class="r" href="/index.php?m=Case&a=cats&id=1&cid=7">More</a></div><div class="dnhb-li">
			<a href="/index.php?m=Case&a=cats&id=1&cid=7"></a></div></li>
			<li><div class="dnhb-title"><span class="l">市政</span><a class="r" href="/index.php?m=Case&a=cats&id=1&cid=6">More</a></div><div class="dnhb-li"><a href="/index.php?m=Case&a=cats&id=1&cid=6"></a></div></li>
			<li><div class="dnhb-title"><span class="l">企业</span><a class="r" href="/index.php?m=Case&a=cats&id=1&cid=5">More</a></div><div class="dnhb-li"><a href="/index.php?m=Case&a=cats&id=1&cid=5"></a></div></li>
			<li><div class="dnhb-title"><span class="l">银行</span><a class="r" href="/index.php?m=Case&a=cats&id=1&cid=8">More</a></div><div class="dnhb-li"><a href="<?php echo ($r["url"]); ?>"><a href="/index.php?m=Case&a=cats&id=1&cid=8"></a></div></li>
			</ul>		 
		 </div>
	</div><!--END hye-->

<div class="blank10"></div>
</div>

<div class="bottom">
    <div class="w1000">
	<div class="likes"><font><b>友情链接:</b></font> <?php  $_result=M("Link")->field("*")->where(" status = 1  and lang=1 and typeid=2 and  linktype=1")->order("id desc")->limit("20")->select();; if ($_result): $i=0;foreach($_result as $key=>$r):++$i;$mod = ($i % 2 );?><a href="<?php echo ($r['siteurl']); ?>" target="_blank" title="<?php echo ($r['name']); ?>" ><?php echo ($r['name']); ?></a><?php endforeach; endif;?> </div>
	<div class="lxfs">电话：&nbsp;<?php echo ($site_tel); ?>&nbsp;&nbsp;传真：&nbsp;<?php echo ($site_fax); ?>&nbsp;&nbsp;邮件：&nbsp;<?php echo ($site_email); ?>&nbsp;&nbsp;地址：&nbsp;<?php echo ($site_ass); ?>&nbsp;&nbsp;<?php echo ($ipc); ?></div>
	<div class="copyright"><p>Copyright©2003-2012 版权所有：<?php echo ($site_name); ?></p></div>
	</div>
</div>

<?php if($ishome=='home') : ?>
<script type="text/javascript" src="../Public/js/jcarousellite_1.0.1.min.js"></script>
<script type="text/javascript">
$(function() {

    $(".pps_content").jCarouselLite({
        btnNext: ".pnext",
        btnPrev: ".pprev",
		visible: 5
    });

	$(".gswj-content-lists").jCarouselLite({
        btnNext: ".gnext",
        btnPrev: ".gprev",
		visible: 4
    });
})
</script>

<script type="text/javascript" src="../Public/js/highslide-with-gallery.js"></script>
<link rel="stylesheet" type="text/css" href="../Public/css/highslide.css" />
<script type="text/javascript">
//图片预览效果
	hs.graphicsDir = '../graphics/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.outlineType = 'rounded-white';
	hs.fadeInOut = true;
	//hs.dimmingOpacity = 0.75;

	// Add the controlbar
	hs.addSlideshow({
		//slideshowGroup: 'group1',
		interval: 5000,
		repeat: false,
		useControls: true,
		fixedControls: 'fit',
		overlayOptions: {
			opacity: 0.75,
			position: 'bottom center',
			hideOnMouseOut: true
		}
	});
</script>
<style>
.highslide-gallery ul li{background:none; border:0;}
.highslide img{border:0;}
</style>

<?php endif;?>

<script>
//百度分享代码
window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"16"},"slide":{"type":"slide","bdImg":"4","bdPos":"left","bdTop":"100"},"image":{"viewList":["qzone","tsina","tqq","renren","weixin"],"viewText":"分享到：","viewSize":"16"},"selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];</script>


<script type="text/javascript">
	// 返回顶部js效果
	function gotoTop(min_height){
		//预定义返回顶部的html代码，它的css样式默认为不显示
		var gotoTop_html = "<div id='gotoTop' onclick='javascript:scroll(0,0)'>返回顶部</div>";
		//将返回顶部的html代码插入页面上id为page的元素的末尾 
		$("#page").append(gotoTop_html);
		$("#gotoTop").click(//定义返回顶部点击向上滚动的动画
			function(){
			//$('html,body').animate({scrollTop:0},700);
		}).hover(//为返回顶部增加鼠标进入的反馈效果，用添加删除css类实现
			function(){
			//$(this).addClass("hover");
			},
			function(){
			//$(this).removeClass("hover");
		});
		//获取页面的最小高度，无传入值则默认为600像素
		min_height ? min_height = min_height : min_height = 600;
		//为窗口的scroll事件绑定处理函数
		$(window).scroll(function(){
			//获取窗口的滚动条的垂直位置
			var s = $(window).scrollTop();
			//当窗口的滚动条的垂直位置大于页面的最小高度时，让返回顶部元素渐现，否则渐隐
			if( s > min_height){
				$("#gotoTop").fadeIn(100);
			}else{
				$("#gotoTop").fadeOut(200);
			};
		});
	};
	$(function(){gotoTop();});
</script>
</body>

</html>