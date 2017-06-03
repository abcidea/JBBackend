<?php if (!defined('THINK_PATH')) exit();?><!--字段类型-->
<?php switch($type): case "title": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">是否使用标题图片</td>
			  <td><input type="radio" name="setup[thumb]" value="1"<?php if($thumb==1): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[thumb]" value="0"<?php if($thumb==0): ?>checked<?php endif; ?>> 否</td>
			</tr>
			<tr>
			  <td>是否使用标题样式</td>
			  <td><input type="radio" name="setup[style]" value="1" <?php if($style==1): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[style]" value="0" <?php if($style==0): ?>checked<?php endif; ?>> 否</td>
			</tr>
			<tr>
			  <td>文本框长度</td>
			  <td><input type="text" class="input-text" size="5" name="setup[size]" value="<?php echo ($size); ?>" /></td>
			</tr>
		</table><?php break;?>
	<?php case "text": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">文本框长度</td>
			  <td><input type="text" class="input-text" size="5" name="setup[size]" value="<?php echo ($size); ?>" /></td>
			</tr>
			<tr>
			  <td>默认值</td>
			  <td> <input type="text" class="input-text" size="50"  name="setup[default]" value="<?php echo ($default); ?>" /></td>
			</tr>
			<tr>
			  <td>是否为密码框</td>
			  <td><input type="radio" name="setup[ispassword]" value="1" <?php if($ispassword==1): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[ispassword]" value="0" <?php if($ispassword==0): ?>checked<?php endif; ?>> 否</td>
			</tr><input type="hidden" id="varchar" name="setup[fieldtype]" value="varchar"/>
		</table><?php break;?>
	<?php case "textarea": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
				  <td>字段类型</td>
				  <td>
				  <select name="setup[fieldtype]">
				  <option value="mediumtext" <?php if($fieldtype=='mediumtext') echo 'selected';?>>MEDIUMTEXT</option>
				  <option value="text" <?php if($fieldtype=='text') echo 'selected';?>>TEXT</option>
				  </select>
                  </td>
			</tr>
			<tr>
			  <td width="120">文本域行数</td>
			  <td><input type="text"  class="input-text" size="5" name="setup[rows]" value="<?php echo ($rows); ?>" /></td>
			</tr>
			<tr>
			  <td>文本域列数</td>
			  <td><input type="text"  class="input-text" size="5" name="setup[cols]" value="<?php echo ($cols); ?>" /></td>
			</tr>
			<tr>
			  <td>默认值</td>
			  <td><textarea  name="setup[default]" rows="3" cols="40"><?php echo ($default); ?></textarea></td>
			</tr>
		</table><?php break;?>
	<?php case "select": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">选项列表:<br>例: <font color="red">选项名称|值</font></td>
			  <td><textarea  name="setup[options]" rows="5" cols="40"><?php if(strlen($options)!=5) : echo ($options); endif;?></textarea></td>
			</tr>
			<tr>
			  <td>选项类型</td>
			  <td><input type="radio" name="setup[multiple]" value="0" <?php if($multiple==0): ?>checked<?php endif; ?>> 下拉框 <input type="radio" name="setup[multiple]" value="0" <?php if($multiple==1): ?>checked<?php endif; ?>> 多选列表框</td>
			</tr>
			<tr>
				  <td>字段类型</td>
				  <td>
				  <select name="setup[fieldtype]" onchange="javascript:numbertype(this.value);">
				  <option value="varchar" <?php if($fieldtype=='varchar') echo 'selected';?>>字符 VARCHAR</option>
				  <option value="tinyint" <?php if($fieldtype=='tinyint') echo 'selected';?>>整数 TINYINT(3)</option>
				  <option value="smallint" <?php if($fieldtype=='smallint') echo 'selected';?>>整数 SMALLINT(5)</option>
				  <option value="mediumint" <?php if($fieldtype=='mediumint') echo 'selected';?>>整数 MEDIUMINT(8)</option>
				  <option value="int" <?php if($fieldtype=='int') echo 'selected';?>>整数 INT(10)</option>
				  </select> <span id="numbertype" style="display:none;"  > <input type="checkbox" id="" name = "setup[numbertype]" value="1" checked />不包括负数</span>
                  </td>
			</tr>
			<tr>
			  <td>可见选项的数目</td>
			  <td><input type="text" class="input-text" size="5" name="setup[size]" value="<?php echo ($size); ?>" /></td>
			</tr>
			<tr>
			  <td>默认值</td>
			  <td> <input type="text" class="input-text" size="5"  name="setup[default]" value="<?php echo ($default); ?>" /></td>
			</tr>
		</table><?php break;?>
	<?php case "editor": ?><table cellpadding="2" cellspacing="1" width="100%">
		<tr>
		  <td width="140">编辑器类型：</td>
		  <td><select name="setup[edittype]">
			<option value="kindeditor"<?php if($edittype=='kindeditor'): ?>selected<?php endif; ?>>kindeditor</option>
			<option value="Xheditor"<?php if($edittype=='Xheditor'): ?>selected<?php endif; ?>>Xheditor</option>
			<option value="ckeditor"<?php if($edittype=='ckeditor'): ?>selected<?php endif; ?>>ckeditor</option>
		  </select></td>
		</tr>
		
		<tr>
		  <td width="140">编辑器样式：</td>
		  <td><input type="radio" name="setup[toolbar]" value="basic" <?php if($toolbar=='basic'): ?>checked<?php endif; ?>> 简洁型  <input type="radio" name="setup[toolbar]" value="full" <?php if($toolbar=='full'): ?>checked<?php endif; ?>> 标准型 </td>
		</tr>
		<tr>
		  <td>默认值：</td>
		  <td><textarea name="setup[default]" rows="3" cols="40" id="default"  ><?php echo ($default); ?></textarea></td>
		</tr>
		<tr>
		  <td>编辑器默认高度：</td>
		  <td><input type="text" name="setup[height]" value="<?php echo ($height); ?>" size="4" class="input-text" ></td>
		</tr>

		<tr>
		  <td>显示截取摘要选项：</td>
		  <td><input type="radio" name="setup[show_add_description]" value="1" <?php if($show_add_description==1): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[show_add_description]" value="0" <?php if($show_add_description==0): ?>checked<?php endif; ?>> 否</td>
		</tr>

		<tr>
		  <td>显示获取标题图片选项：</td>
		  <td><input type="radio" name="setup[show_auto_thumb]" value="1" <?php if($show_auto_thumb==1): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[show_auto_thumb]" value="0" <?php if($show_auto_thumb==0): ?>checked<?php endif; ?>> 否</td>
		</tr>
  

		<tr>
		  <td>内容是否分页：</td>
		  <td><input type="radio" name="setup[showpage]" value="1" <?php if($showpage==1 || $action_name=='add'): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[showpage]" value="0" <?php if($showpage==0): ?>checked<?php endif; ?>> 否</td>
		</tr>
		<tr>
		  <td>是否启用关联链接：</td>
		  <td><input type="radio" name="setup[enablekeylink]" value="1" <?php if($enablekeylink==1): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[enablekeylink]" value="0" <?php if($enablekeylink==0): ?>checked<?php endif; ?>> 否  <input type="text" name="setup[replacenum]" value="<?php echo ($replacenum); ?>" size="4" class="input-text"> 替换次数 （留空则为替换全部）</td>
		</tr>
		<tr>
		  <td>是否保存远程图片：</td>
		  <td><input type="radio" name="setup[enablesaveimage]" value="1" <?php if($enablesaveimage==1): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[enablesaveimage]" value="0" <?php if($enablesaveimage==0): ?>checked<?php endif; ?>> 否</td>
		</tr>
		<tr>
		  <td>是否允许图片上传：</td>
		  <td><input type="radio" name="setup[flashupload]" value="1" <?php if($flashupload==1 || $action_name=='add'): ?>checked<?php endif; ?>> 是 <input type="radio" name="setup[flashupload]" value="0" <?php if($flashupload==0): ?>checked<?php endif; ?>> 否</td>
		</tr>
		<tr>
		  <td>允许上传的图片类型：<br>例: <font color="red">jpg,jpeg,gif</font></td>
		  <td><input type="text" name="setup[alowuploadexts]" value="<?php echo ($alowuploadexts); ?>" size="55" class="input-text"></td>
		</tr>
		<tr>
		  <td>最多上传图片个数：<br></td>
		  <td><input type="text" name="setup[alowuploadlimit]" value="<?php echo ($alowuploadlimit); ?>" size="35" class="input-text" style="width:40px;">默认为20个</td>
		</tr>
		</table><?php break;?>
	<?php case "datetime": break;?>
    <?php case "groupid": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">选项类型</td>
			  <td> <input type="radio" name="setup[inputtype]" value="checkbox" <?php if($inputtype=='checkbox'): ?>checked<?php endif; ?>> 复选框 <input type="radio" name="setup[inputtype]" value="select" <?php if($inputtype=='select'): ?>checked<?php endif; ?>> 下拉列表框
              <input type="radio" name="setup[inputtype]" value="radio" <?php if($inputtype=='radio'): ?>checked<?php endif; ?>> 单选框
              </td>
			</tr>
			<tr>
				  <td>字段类型</td>
				  <td>
				  <select name="setup[fieldtype]"  onchange="javascript:numbertype(this.value);">
				  <option value="varchar" <?php if($fieldtype=='varchar') echo 'selected';?>>字符 VARCHAR</option>
				  <option value="tinyint" <?php if($fieldtype=='tinyint') echo 'selected';?>>整数 TINYINT(3)</option>
				  </select><span id="numbertype" style="display:none;"  > <input type="checkbox" id="" name = "setup[numbertype]" value="1" checked />不包括负数</span>
                  </td>
			</tr>
            <tr>
			  <td>复选框或单选框时宽度</td>
			  <td> <input type="text" class="input-text" size="10"  name="setup[labelwidth]" value="<?php echo ($labelwidth); ?>" /></td>
			</tr>
			<tr>
			  <td>默认值</td>
			  <td> <input type="text" class="input-text" size="5"  name="setup[default]" value="<?php echo ($default); ?>" /></td>
			</tr>
		</table><?php break;?>
	<?php case "typeid": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">选项类型</td>
			  <td> <input type="radio" name="setup[inputtype]" value="checkbox" <?php if($inputtype=='checkbox'): ?>checked<?php endif; ?>> 复选框 <input type="radio" name="setup[inputtype]" value="select" <?php if($inputtype=='select'): ?>checked<?php endif; ?>> 下拉列表框
              <input type="radio" name="setup[inputtype]" value="radio" <?php if($inputtype=='radio'): ?>checked<?php endif; ?>> 单选框
              </td>
			</tr>
			<tr>
				  <td>字段类型</td>
				  <td>
				  <select name="setup[fieldtype]"  onchange="javascript:numbertype(this.value);">
				  <option value="varchar" <?php if($fieldtype=='varchar') echo 'selected';?>>字符 VARCHAR</option>
				  <option value="tinyint" <?php if($fieldtype=='tinyint') echo 'selected';?>>整数 TINYINT(3)</option>
				  <option value="smallint" <?php if($fieldtype=='smallint') echo 'selected';?>>整数 SMALLINT(5)</option>
				  </select><span id="numbertype" style="display:none;"  > <input type="checkbox" id="" name = "setup[numbertype]" value="1" checked />不包括负数</span>
                  </td>
			</tr>
            <tr>
			  <td>复选框或单选框时宽度</td>
			  <td> <input type="text" class="input-text" size="10"  name="setup[labelwidth]" value="<?php echo ($labelwidth); ?>" /></td>
			</tr>
			<tr>
			  <td>顶级类别ID</td>
			  <td> <input type="text" class="input-text" size="5"  name="setup[default]" value="<?php echo ($default); ?>" /></td>
			</tr>
		</table><?php break;?>
    <?php case "posid": break;?>
    <?php case "radio": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">选项列表:<br>例: <font color="red">选项名称|值</font></td>
			  <td><textarea  name="setup[options]" rows="5" cols="40"><?php if(strlen($options)!=5) : echo ($options); endif;?></textarea></td>
			</tr>
			<tr>
				  <td>字段类型</td>
				  <td>
				  <select name="setup[fieldtype]" onchange="javascript:numbertype(this.value);">
				  <option value="varchar" <?php if($fieldtype=='varchar') echo 'selected';?>>字符 VARCHAR</option>
				  <option value="tinyint" <?php if($fieldtype=='tinyint') echo 'selected';?>>整数 TINYINT(3)</option>
				  <option value="smallint" <?php if($fieldtype=='smallint') echo 'selected';?>>整数 SMALLINT(5)</option>
				  <option value="mediumint" <?php if($fieldtype=='mediumint') echo 'selected';?>>整数 MEDIUMINT(8)</option>
				  <option value="int" <?php if($fieldtype=='int') echo 'selected';?>>整数 INT(10)</option>
				  </select> <span id="numbertype" style="display:none;"  > <input type="checkbox" id="" name = "setup[numbertype]" value="1" checked />不包括负数</span>
                  </td>
			</tr>
            <tr>
			  <td>宽度</td>
			  <td> <input type="text" class="input-text" size="10"  name="setup[labelwidth]" value="<?php echo ($labelwidth); ?>" /></td>
			</tr>
			<tr>
			  <td>默认值</td>
			  <td> <input type="text" class="input-text" size="5"  name="setup[default]" value="<?php echo ($default); ?>" /></td>
			</tr>
		</table><?php break;?>
    <?php case "checkbox": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">选项列表:<br>例: <font color="red">选项名称|值</font></td>
			  <td><textarea  name="setup[options]" rows="5" cols="40"><?php if(strlen($options)!=5) : echo ($options); endif;?></textarea></td>
			</tr>
			<tr>
				  <td>字段类型</td>
				  <td>
				  <select name="setup[fieldtype]" onchange="javascript:numbertype(this.value);">
				  <option value="varchar" <?php if($fieldtype=='varchar') echo 'selected';?>>字符 VARCHAR</option>
				  <option value="tinyint" <?php if($fieldtype=='tinyint') echo 'selected';?>>整数 TINYINT(3)</option>
				  <option value="smallint" <?php if($fieldtype=='smallint') echo 'selected';?>>整数 SMALLINT(5)</option>
				  <option value="mediumint" <?php if($fieldtype=='mediumint') echo 'selected';?>>整数 MEDIUMINT(8)</option>
				  <option value="int" <?php if($fieldtype=='int') echo 'selected';?>>整数 INT(10)</option>
				  </select> <span id="numbertype" style="display:none;"  > <input type="checkbox" id="" name = "setup[numbertype]" value="1" checked />不包括负数</span>
                  </td>
			</tr>
            <tr>
			  <td>宽度</td>
			  <td> <input type="text" class="input-text" size="10"  name="setup[labelwidth]" value="<?php echo ($labelwidth); ?>" /></td>
			</tr>
			<tr>
			  <td>默认值</td>
			  <td> <input type="text" class="input-text" size="5"  name="setup[default]" value="<?php echo ($default); ?>" /></td>
			</tr>
		</table><?php break;?>
	<?php case "number": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">文本框长度</td>
			  <td><input type="text" class="input-text" size="5" name="setup[size]" value="<?php echo ($size); ?>" /></td>
			</tr>
			<tr> 
			  <td>是否包括负数：</td>
			  <td><input type="checkbox" id="" name = "setup[numbertype]" value="1" checked />不包括负数</td>
			</tr>
			<tr> 
			  <td>小数位数：</td>
			  <td>
			  <select name="setup[decimaldigits]">
			  <option value="0"<?php if($decimaldigits==0) echo ' selected';?>>0</option>
			  <option value="1"<?php if($decimaldigits==1) echo ' selected';?>>1</option>
			  <option value="2"<?php if($decimaldigits==2) echo ' selected';?>>2</option>
			  <option value="3"<?php if($decimaldigits==3) echo ' selected';?>>3</option>
			  <option value="4"<?php if($decimaldigits==4) echo ' selected';?>>4</option>
			  <option value="5"<?php if($decimaldigits==5) echo ' selected';?>>5</option>
			  </select>
			</td>
			</tr>
			<tr> 
			  <td>默认值</td>
			  <td><input type="text" name="setup[default]" value="<?php echo ($default); ?>" size="40" class="input-text"></td>
			</tr>
		</table><?php break;?>
	<?php case "image": ?><table cellpadding="2" cellspacing="1" width="100%">
				<tr> 
				  <td width="120">文本框长度</td>
				  <td><input type="text" name="setup[size]" value="<?php echo ($size); ?>" size="10" class="input-text"></td>
				</tr>
				<tr> 
				  <td>默认值</td>
				  <td><input type="text" name="setup[default]" value="<?php echo ($default); ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>允许上传的文件大小</td>
				  <td><input type="text" name="setup[upload_maxsize]" value="<?php echo ($upload_maxsize); ?>" size="5" class="input-text">MB </td>
				</tr>
				<tr> 
				  <td>允许上传的文件类型</td>
				  <td><input type="text" name="setup[upload_allowext]" value="<?php if($upload_allowext){echo $upload_allowext;}else{ echo 'jpg,jpeg,gif,png';} ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>是否添加水印</td>
				  <td><input type="radio" name="setup[watermark]" value="1" <?php if($watermark==1) echo 'checked';?>> 是 <input type="radio" name="setup[watermark]" value="0" <?php if($watermark==0) echo 'checked';?>> 否</td>
				</tr>
				<tr> 
				  <td>是否从已上传中选择</td>
				  <td><input type="radio" name="setup[more]" value="1" <?php if($more==1) echo 'checked';?>> 是 <input type="radio" name="setup[more]" value="0" <?php if($more==0) echo 'checked';?>> 否</td>
				</tr>				
			</table><?php break;?>
	<?php case "images": ?><table cellpadding="2" cellspacing="1" width="100%">
				<tr> 
				  <td>默认值</td>
				  <td><input type="text" name="setup[default]" value="<?php echo ($default); ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>最多允许上传几张图片</td>
				  <td><input type="text" name="setup[upload_maxnum]" value="<?php echo ($upload_maxnum); ?>" size="5" class="input-text"></td>
				</tr>
				<tr> 
				  <td>允许上传的图片大小</td>
				  <td><input type="text" name="setup[upload_maxsize]" value="<?php echo ($upload_maxsize); ?>" size="5" class="input-text">MB </td>
				</tr>
				<tr> 
				  <td>允许上传的图片类型</td>
				  <td><input type="text" name="setup[upload_allowext]" value="<?php if($upload_allowext){echo $upload_allowext;}else{ echo 'jpg,jpeg,gif,png';} ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>是否在图片上添加水印</td>
				  <td><input type="radio" name="setup[watermark]" value="1" <?php if($watermark==1) echo 'checked';?>> 是 <input type="radio" name="setup[watermark]" value="0" <?php if($watermark==0) echo 'checked';?>> 否</td>
				</tr>
				<tr> 
				  <td>是否从已上传中选择</td>
				  <td><input type="radio" name="setup[more]" value="1" <?php if($more==1) echo 'checked';?>> 是 <input type="radio" name="setup[more]" value="0" <?php if($more==0) echo 'checked';?>> 否</td>
				</tr>
			</table><?php break;?>
	<?php case "file": ?><table cellpadding="2" cellspacing="1" width="100%">
				<tr> 
				  <td width="120">文本框长度</td>
				  <td><input type="text" name="setup[size]" value="<?php echo ($size); ?>" size="10" class="input-text"></td>
				</tr>
				<tr> 
				  <td>默认值</td>
				  <td><input type="text" name="setup[default]" value="<?php echo ($default); ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>允许上传的文件大小</td>
				  <td><input type="text" name="setup[upload_maxsize]" value="<?php echo ($upload_maxsize); ?>" size="5" class="input-text">MB </td>
				</tr>
				<tr> 
				  <td>允许上传的文件类型</td>
				  <td><input type="text" name="setup[upload_allowext]" value="<?php if($upload_allowext){echo $upload_allowext;}else{ echo 'zip,rar,doc,ppt';} ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>是否从已上传中选择</td>
				  <td><input type="radio" name="setup[more]" value="1" <?php if($more==1) echo 'checked';?>> 是 <input type="radio" name="setup[more]" value="0" <?php if($more==0) echo 'checked';?>> 否</td>
				</tr>				
			</table><?php break;?>
	<?php case "files": ?><table cellpadding="2" cellspacing="1" width="100%">
				<tr> 
				  <td>默认值</td>
				  <td><input type="text" name="setup[default]" value="<?php echo ($default); ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>最多允许上传的文件个数</td>
				  <td><input type="text" name="setup[upload_maxnum]" value="<?php echo ($upload_maxnum); ?>" size="5" class="input-text"></td>
				</tr>
				<tr> 
				  <td>允许上传的文件大小</td>
				  <td><input type="text" name="setup[upload_maxsize]" value="<?php echo ($upload_maxsize); ?>" size="5" class="input-text">MB </td>
				</tr>
				<tr> 
				  <td>允许上传的文件类型</td>
				  <td><input type="text" name="setup[upload_allowext]" value="<?php if($upload_allowext){echo $upload_allowext;}else{ echo 'zip,rar,doc,ppt';} ?>" size="40" class="input-text"></td>
				</tr>
				<tr> 
				  <td>是否从已上传中选择</td>
				  <td><input type="radio" name="setup[more]" value="1" <?php if($more==1) echo 'checked';?>> 是 <input type="radio" name="setup[more]" value="0" <?php if($more==0) echo 'checked';?>> 否</td>
				</tr>				
			</table><?php break;?>
	<?php case "verify": ?><table cellpadding="2" cellspacing="1" width="100%">
			<tr>
			  <td width="120">文本框长度</td>
			  <td><input type="text" class="input-text" size="5" name="setup[size]" value="<?php echo ($size); ?>" /></td>
			</tr>
		</table><?php break;?>
	
	<?php default: endswitch;?>

<script>
		function numbertype(fieldtype){
		 
			if(fieldtype=='varchar'){ 
				$('#numbertype').hide();
			}else{
				$('#numbertype').show();
			}
		}
		<?php if(!empty($fieldtype) && $fieldtype!='varchar'): ?>$('#numbertype').show();<?php endif; ?>
		</script>