<?php
/**
 * 
 * IndexAction.class.php (前台首页)
 *
 */
if(!defined("Myphp")) exit("Access Denied");
class WxapiAction extends WeChatApiBaseAction
{
    public function getToken()
    {
        parent::token();
    }
}
?>