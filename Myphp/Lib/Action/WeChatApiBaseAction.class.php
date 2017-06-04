<?php
/**
 * 
 * Base (前台公共模块)
 * 
 * 常用模板变量
 *
 * 全局模板变量
 * $T,$Categorys
 * list列表页模板变量
 * $list,$page
 * show内容页模板变量
 * $cat,$catid,$data,$page
 * page单页面模板变量
 * $data
 */
if(!defined("Myphp")) exit("Access Denied");
class WeChatApiBaseAction extends Action
{

	protected $wechat;

    public function _initialize() {

            /**
             * 设置微信访问对象
             */
            vendor('wechat',VENDOR_PATH.'wechat','.class.php');
	}

    public function token()
    {
        $opt = array(
            'appsecret'=>'68c857db0a2315f58d9ff6af87ae9fa8',//填写高级调用功能的密钥
            'appid'=>'wx55a7a7bd5fb0ce56'	//填写高级调用功能的appid
        );

        $we = new Wechat($opt);
        $auth = $we->checkAuth();
        $js_ticket = $we->getJsTicket();
        if (!$js_ticket) {
            $return['msg'] = "获取js_ticket失败！";
            $return['code'] = $we->errCode;
            $return['data'] = ErrCode::getErrText($we->errCode);
            $this->ajaxReturn ($return,'JSON');
        }
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $js_sign = $we->getJsSign($url);
        $this->ajaxReturn ($js_sign,'JSON');
    }
}
?>