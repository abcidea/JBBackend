<?php
/**
 * 
 * IndexAction.class.php (前台首页)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class IndexAction extends BaseAction
{
    public function index()
    {
		if(!is_mobile_request()){
			header('Location: /index.php?g=Home');
		}
		
		$this->assign('bcid',0);//顶级栏目 
		$this->assign('ishome','home');
        $this->display();
    }
 
}
?>