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
        $this->WeChatObj->text("http://dev.dingjiayu.cn/index.php?g=WeChat&m=Indexjs&a=index");
        $this->WeChatObj->reply();
    }

}
?>