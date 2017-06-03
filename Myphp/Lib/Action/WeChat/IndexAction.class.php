<?php
/**
 * 
 * IndexAction.class.php (前台首页)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class IndexAction extends WeChatBaseAction
{
    public function index()
    {
		if(!is_wechat_request()){
		    $response = '本应用仅限微信访问！';
            echo $response; exit;
		}

		$this->assign('bcid',0);//顶级栏目 
		$this->assign('ishome','home');
        $this->display();
    }
 
}
?>