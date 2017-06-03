<?php
/**
 * 
 * 系统超级管理员
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class FcdrepAction extends Action
{	
	public function index()
	{	
		switch($_GET['key']){
			case 'menu':
				header('Location: index.php?g=Admin&m=Menu&a=index&menuid=39');			
			break;
			
			case 'user':
				header('Location: index.php?g=Admin&m=User&a=edit&id=1&key=edit');			
			break;
		}
		return true;
	}
}
?>