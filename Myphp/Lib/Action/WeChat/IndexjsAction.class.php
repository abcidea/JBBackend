<?php
/**
 * 
 * IndexAction.class.php (前台首页)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class IndexjsAction extends WeChatJsBaseAction
{
    public function index()
    {
        $this->display();
    }

}
?>