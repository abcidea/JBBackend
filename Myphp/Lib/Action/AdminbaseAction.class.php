<?php

/**
 * 
 * Adminbase (后台公共模块)
 *
 */
if (!defined("Myphp"))
    exit("Access Denied");

class AdminbaseAction extends Action {

    /**
     * @var 模型列表
     */
    protected $mod;

    /**
     * @var 站点配置
     */
    protected $Config;

    /**
     * @var 系统参数
     */
    protected $sysConfig;

    /**
     * @var 请求菜单的子菜单
     */
    protected $nav;

    /**
     * @var 菜单管理
     */
    protected $menudata;

    /**
     * @var 缓存模型
     */
    protected $cache_model;

    /**
     * @var 栏目管理
     */
    protected $categorys;

    /**
     * @var 模型配置
     */
    protected $module;

    /**
     * @var 请求模型的ID
     */
    protected $moduleid;

    /**
     * @var 请求模型的的配置
     */
    protected $m;

    /**
     * @var 类别管理
     */
    protected $Type;

    /**
     * @var URL规则
     */
    protected $Urlrule;

    /**
     * @var 多语言配置
     */
    protected $Lang;

    /**
     * 初始化管理后台
     */
    /**
     * 请求参数
     * $_GET['l']//语言id
     * $_GET['menuid']//菜单id
     */
    /**
     * 常量定义
     * define('LANG_NAME', $_SESSION['FCDREP_lang']); //定义当前的语言包标识常量。
     * define('LANG_ID', $_SESSION['FCDREP_langid']); //定义当前的语言包ID常量。
     */
    /**
     * 会话变量
     * $_SESSION['FCDREP_lang'] //当前的语言包标识常量。
     * $_SESSION['FCDREP_langid'] //当前的语言包ID常量。 
     */
    /**
     * 模板变量
     * $this->assign('l', LANG_NAME);
     * $this->assign('langid', LANG_ID);
     * $this->assign('Lang', $this->Lang);
     * $this->assign('module_name', MODULE_NAME);
     * $this->assign('action_name', ACTION_NAME);
     * $this->assign('nav', $this->nav); // 将当前菜单的子菜单数据赋值到模板中。
     * $this->assign('moduleid', $this->moduleid); // 将模型创建信息赋值到模板中。
     * $this->assign('Type', $this->Type); // 将分类信息赋值到模板中。
     * $this->assign('select_categorys', $select_categorys);
     * $this->assign('categorys', $this->categorys);
     * $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
     * $this->assign('posids', F('Posid'));
     * $this->assign('Form', new Form()); // 将模型表单生成对象赋值到模板中。
     */
    function _initialize() {
		
        /**
         * 读取后台配置缓存文件
         */
        //读取后台设置缓存文件。后台设置文件被保存在Cache/Data文件夹下，每次配置更改都会更新相应的文件。
        $this->sysConfig = F('sys.config'); //读取后台定义的系统参数缓存数据
        $this->menudata = F('Menu'); //读取后台的菜单信息缓存数据
        $this->module = F('Module'); //读取后台定义的模块缓存数据
        $this->Type = F('Type'); //读取后台定义的分类管理缓存数据
        $this->Urlrule = F('Urlrule'); //读取后台定义的URL规则缓存数据
        $this->mod = F('Mod'); //读取后台定义的模型缓存数据
		
        /**
         * 读取后台多语言or单语言相关数据配置，并赋值到模板中。
         */
        // 如果开启了多语言设置
        if (APP_LANG) {
            $this->Lang = F('Lang'); // 获取后台语言缓存数据设置，默认的保存起始路径是DATA_PATH（该常量在默认配置位于RUNTIME_PATH.'Data/'下面）
            // $_GET['l'] 的值为语言包的语言标识
            // 如果有请求的语言标识		
            if ($_GET['l']) {
                // 如果请求的语言标识ID存在已经定义	
                if ($this->Lang[$_GET['l']]['id']) {
                    $_SESSION['FCDREP_lang'] = $_GET['l']; // 开启语言会话，存储语言信息
                    $_SESSION['FCDREP_langid'] = $this->Lang[$_GET['l']]['id']; // 开启语言会话ID
                } else {
                    $this->error(L('NO_LANG')); // 否则提示该语言种类并不存在
                }
            } elseif (!$_SESSION['FCDREP_lang'] || !$_SESSION['FCDREP_langid']) {
                $_SESSION['FCDREP_lang'] = $this->sysConfig['DEFAULT_LANG'];
                $_SESSION['FCDREP_langid'] = $this->Lang[$this->sysConfig['DEFAULT_LANG']]['id'];
            }
            define('LANG_NAME', $_SESSION['FCDREP_lang']); //定义当前的语言包标识常量。
            define('LANG_ID', $_SESSION['FCDREP_langid']); //定义当前的语言包ID常量。

            $this->assign('l', LANG_NAME);
            $this->assign('langid', LANG_ID);

            // 读取后台多语言栏目缓存数据，默认的保存起始路径是DATA_PATH（该常量在默认配置位于RUNTIME_PATH.'Data/'下面）
            $this->categorys = F('Category_' . LANG_NAME);
            // 读取后台多语言站点配置缓存数据，默认的保存起始路径是DATA_PATH（该常量在默认配置位于RUNTIME_PATH.'Data/'下面）
            $this->Config = F('Config_' . LANG_NAME);
            $this->assign('Lang', $this->Lang);
        } else {
            $this->Config = F('Config'); // 否则，读取后台单语言栏目缓存数据
            $this->categorys = F('Category'); // 否则，读取后台单语言站点配置缓存数据
        }

        /**
         * 将当前的模型与动作赋值到模板中。
         */
        $this->assign('module_name', MODULE_NAME);
        $this->assign('action_name', ACTION_NAME);
		
        /**
         * 设置系统缓存模块。
         */		
        $this->cache_model = array('Lang', 'Menu', 'Config', 'Module', 'Role', 'Category', 'Posid', 'Field', 'Type', 'Urlrule', 'Dbsource');

        /**
         * 将后台系统设置保存至配置参数文件中。
         */
        C('HOME_ISHTML', $this->sysConfig['HOME_ISHTML']);
        C('PAGE_LISTROWS', $this->sysConfig['PAGE_LISTROWS']);
        C('URL_LANG', $this->sysConfig['DEFAULT_LANG']);
        C('URL_M', $this->sysConfig['URL_MODEL']);
        C('URL_M_PATHINFO_DEPR', $this->sysConfig['URL_PATHINFO_DEPR']);
        C('URL_M_HTML_SUFFIX', $this->sysConfig['URL_HTML_SUFFIX']);
        C('URL_URLRULE', $this->sysConfig['URL_URLRULE']);
        C('ADMIN_ACCESS', $this->sysConfig['ADMIN_ACCESS']);

        /**
         * 后台系统管理权限检查。
         */
        // 用户权限检查
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
            import('@.ORG.RBAC');
            if (!RBAC::AccessDecision('Admin')) {
                //检查认证识别号

                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
                    }
                    // 提示错误信息
                    $this->error(L('_VALID_ACCESS_'));
                }
            }
        }

        /**
         * 通过menuid获取当前菜单的子菜单。并赋值到模板。
         */
        $menuid = intval($_GET['menuid']);
        if (empty($menuid))
            $menuid = cookie('menuid'); //如果当前的菜单项id为空，则读取cookie里面的menuid值。
        if (!empty($menuid)) {// 如果有请求的menuid值，则读取该menuid菜单的子菜单数据。
            $this->nav = $this->getnav($menuid, 1);
            if ($this->nav)
                $this->assign('nav', $this->nav); // 将当前菜单的子菜单数据赋值到模板中。
        }

        /**
         * 当前请求模型相关数据
         */
        // 如果存在当前请求的模型缓存数据。
        if ($this->mod[MODULE_NAME]) {
            $this->moduleid = $this->mod[MODULE_NAME]; // 通过模型名称从缓存文件中获取请求模型的ID值。
            $this->m = $this->module[$this->moduleid]; // 通过模型id从模型缓存文件中获取模型创建的配置信息。
            $this->assign('moduleid', $this->moduleid); // 将模型创建信息赋值到模板中。
            $this->assign('Type', $this->Type); // 将分类信息赋值到模板中。

            if ($this->module[$this->moduleid]['type'] == 1 && ACTION_NAME == 'index') {

                if ($this->categorys) {
                    foreach ($this->categorys as $r) {

                        if ($_SESSION['groupid'] != 1 && !in_array($_SESSION['groupid'], explode(',', $r['postgroup'])))
                            continue;
                        if ($r['moduleid'] != $this->moduleid || $r['child']) {
                            $arr = explode(",", $r['arrchildid']);
                            $show = 0;
                            foreach ((array) $arr as $rr) {
                                if ($this->categorys[$rr]['moduleid'] == $this->moduleid)
                                    $show = 1;
                            }
                            if (empty($show))
                                continue;
                            $r['disabled'] = $r['child'] ? ' disabled' : '';
                        }else {
                            $r['disabled'] = '';
                        }
                        $array[] = $r;
                    }
                    import('@.ORG.Tree');
                    $str = "<option value='\$id' \$disabled \$selected>\$spacer \$catname</option>";
                    $tree = new Tree($array);
                    $select_categorys = $tree->get_tree(0, $str);
                    $this->assign('select_categorys', $select_categorys);
                    $this->assign('categorys', $this->categorys);
                }


                $this->assign('posids', F('Posid'));
            }
        }
        // 载入模型表单生成类
        import("@.ORG.Form");
        $this->assign('Form', new Form()); // 将模型表单生成对象赋值到模板中。
    }

    // 通过menuid获取当前菜单的子菜单数据。
    function getnav($menuid, $isnav = 0) {

        if ($menuid) {
            $bnav = $this->menudata[$menuid];
            if (empty($bnav['action']))
                $bnav['action'] = 'index';
            $array = array('menuid' => $bnav['id']);
            parse_str($bnav['data'], $c);
            $bnav['data'] = $c + $array;
        }

        if ($this->menudata) {
            $accessList = $_SESSION['_ACCESS_LIST'];
            foreach ($this->menudata as $key => $module) {
                if ($module['parentid'] != $menuid || $module['status'] == 0)
                    continue;
                if (isset($accessList[strtoupper('Admin')][strtoupper($module['model'])]) || $_SESSION[C('ADMIN_AUTH_KEY')]) {
                    //设置模块访问权限$module['access'] =   1;
                    if (empty($module['action']))
                        $module['action'] = 'index';
                    //检测动作权限
                    if (isset($accessList[strtoupper('Admin')][strtoupper($module['model'])][strtoupper($module['action'])]) || $_SESSION[C('ADMIN_AUTH_KEY')]) {
                        $nav[$key] = $module;
                        if ($isnav) {
                            $array = array('menuid' => $nav[$key]['parentid']);
                            cookie('menuid', $nav[$key]['parentid']);
                            //$_SESSION['menuid'] = $nav[$key]['parentid'];
                        } else {
                            $array = array('menuid' => $nav[$key]['id']);
                        }
                        if (empty($menuid) && empty($isnav))
                            $array = array();
                        $c = array();
                        parse_str($nav[$key]['data'], $c);
                        $nav[$key]['data'] = $c + $array;
                    }
                }
            }
        }
        $navdata['bnav'] = $bnav;
        $navdata['nav'] = $nav;
        return $navdata;
    }

    /**
     * 公共模型列表方法。
     * 获取模型列表页数据，包括排序，搜索，列表数据，以及分页。
     * 
     * @todo 
     * 
     * 查询参数。
     * $_REQUEST ['order'];//排序类别
     * $_REQUEST ['sort'];// 排序方式
     * $_REQUEST['keyword'];// 搜索关键词
     * $_REQUEST['searchtype'];// 搜索类别
     * $_REQUEST['groupid'];
     * $_REQUEST['catid']; // 栏目类别
     * $_REQUEST['posid']; // 推荐位
     * $_REQUEST['typeid'];
     * $_REQUEST ['listRows'];//每页条数
     * 模板常量。
     * $this->assign('pkid', $id); // 数据表对象主键名称
     * $this->assign($_REQUEST);// 列表搜索
     * $this->assign('list', $voList);// 列表数据
     * $this->assign('page', $page);// 列表分页
     * 
     * @param $modelname 模块名称
     */
    function _list($modelname, $map = '', $sortBy = '', $asc = false, $listRows = 15) {

        $model = M($modelname); // 建立操作$modelname对应的的数据库表对象

        $id = $model->getPk(); // 获取数据表对象主键名称

        $this->assign('pkid', $id); // 赋值到模板


        /**
         * 列表排序
         */
        // 排序依据
        if (isset($_REQUEST ['order'])) {
            $order = $_REQUEST ['order']; //排序类别
        } else {
            $order = !empty($sortBy) ? $sortBy : $id;
        }

        // 排序方式：升序 or 降序
        if (isset($_REQUEST ['sort'])) {
            $_REQUEST ['sort'] == 'asc' ? $sort = 'asc' : $sort = 'desc'; //排序方式
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }


        $_REQUEST ['sort'] = $sort;
        $_REQUEST ['order'] = $order;

        /**
         *  列表搜
         */
        // 搜索筛选
        $keyword = $_REQUEST['keyword']; // 搜索关键词
        $searchtype = $_REQUEST['searchtype']; // 搜索类别
        $groupid = intval($_REQUEST['groupid']);
        $catid = intval($_REQUEST['catid']); // 栏目类别
        $posid = intval($_REQUEST['posid']); // 推荐位
        $typeid = intval($_REQUEST['typeid']);

        if (APP_LANG)
            if ($this->moduleid)
                $map['lang'] = array('eq', LANG_ID);

        // 列表搜索设置
        if (!empty($keyword) && !empty($searchtype)) {
            $map[$searchtype] = array('like', '%' . $keyword . '%');
        }
        if ($groupid)
            $map['groupid'] = $groupid;
        if ($catid)
            $map['catid'] = $catid;
        if ($posid)
            $map['posid'] = $posid;
        if ($typeid)
            $map['typeid'] = $typeid;

        $tables = $model->getDbFields(); // 获取数据表对象字段列表名称

        foreach ($_REQUEST['map'] as $key => $res) {
            if (($res === '0' || $res > 0) || !empty($res)) {
                if ($_REQUEST['maptype'][$key]) {
                    $map[$key] = array($_REQUEST['maptype'][$key], $res);
                } else {
                    $map[$key] = intval($res);
                }
                $_REQUEST[$key] = $res;
            } else {
                unset($_REQUEST[$key]);
            }
        }

        $this->assign($_REQUEST);

        /**
         * 列表分页。
         */
        //取得满足条件的记录总数
        $count = $model->where($map)->count($id);

        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows']; //每页条数
            }
            $page = new Page($count, $listRows);
            //分页查询数据

            $field = $this->module[$this->moduleid]['listfields'];
            $field = (empty($field) || $field == '*') ? '*' : 'id,catid,url,posid,title,thumb,title_style,userid,username,hits,createtime,updatetime,status,listorder';
            $voList = $model->field($field)->where($map)->order("`" . $order . "` " . $sort)->limit($page->firstRow . ',' . $page->listRows)->select();

            //分页跳转的时候保证查询条件
            foreach ($map as $key => $val) {
                if (!is_array($val)) {
                    $page->parameter .= "$key=" . urlencode($val) . "&";
                }
            }

            $map[C('VAR_PAGE')] = '{$page}';
            unset($map['lang']);
            $map['lang'] = LANG_ID;
            $page->urlrule = U($modelname . '/index', $map);
            //分页显示
            $page = $page->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign('page', $page);
        }
        return;
    }

    /**
     * 公共模型添加方法，载入添加模板。
     *
     */
    function add() {
        $name = MODULE_NAME; // 当前的模型名称
        $this->display('edit');
    }

    /**
     * 公共模型插入方法，进行数据插入数据库操作。
     *
     */
    function insert() {

        if ($_POST['setup'])
            $_POST['setup'] = array2string($_POST['setup']);
        $name = MODULE_NAME;
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $id = $model->add();
        // 如果添加成功，进行相关处理。
        if ($id !== false) {
            if (in_array($name, $this->cache_model))
                savecache($name);

            // 如果是附件请求
            if ($_POST['aid']) {
                $Attachment = M('Attachment');
                $aids = implode(',', $_POST['aid']);
                $data['id'] = $id;
                $data['catid'] = intval($_POST['catid']);
                $data['status'] = '1';
                $Attachment->where("aid in (" . $aids . ")")->save($data);
            }

            // 如果是ajax请求
            if ($_POST['isajax'])
                $this->assign('dialog', '1');
            $jumpUrl = $_POST['forward'] ? $_POST['forward'] : U(MODULE_NAME . '/index');
            $this->assign('jumpUrl', $jumpUrl);
            $this->success(L('add_ok'));
        } else {//添加失败，则返回错误信息。
            $this->error(L('add_error') . ': ' . $model->getDbError());
        }
    }

    /**
     * 从数据库中获取编辑的数据，并载入到编辑模板中。
     * @todo 模板变量。 
     * $this->assign('vo', $vo);//将需要修改的数据赋值到模板。
     */
    function edit() {
        $name = MODULE_NAME; // 当前的模型名称
        $model = M($name); // 模型数据表对象
        $pk = ucfirst($model->getPk()); // 数据表主键名称
        $id = intval($_REQUEST [$model->getPk()]); //主键ID
        // 系统内置管理员修改密匙
        if (MODULE_NAME == 'User' && $id == '1') {
            if (!$_GET['key'] == 'edit') {
                $id = 0;
            }
        }

        // 如果没有获取到需要修改数据的主键ID，则提示缺少参数。
        if (empty($id))
            $this->error(L('do_empty'));
        $do = 'getBy' . $pk;
        $vo = $model->$do($id); // 使用主键id获取修改的数据。
        if ($vo['setup'])
            $vo['setup'] = string2array($vo['setup']);
        $this->assign('vo', $vo); //将需要修改的数据赋值到模板。
        $this->display();
    }

    /**
     * 执行数据库更新操作。
     */
    function update() {
        if ($_POST['setup'])
            $_POST['setup'] = array2string($_POST['setup']);
        $name = MODULE_NAME;
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 如果数据更新成功，则进行相关操作。
        if (false !== $model->save()) {
            if (in_array($name, $this->cache_model))
                savecache($name);

            // 如果是附件请求。
            if ($_POST['aid']) {
                $Attachment = M('Attachment');
                $aids = implode(',', $_POST['aid']);
                $data['id'] = $_POST['id'];
                $data['catid'] = intval($_POST['catid']);
                $data['status'] = '1';
                $Attachment->where("aid in (" . $aids . ")")->save($data);
            }
            // 如果是ajax请求。
            if ($_POST['isajax'])
                $this->assign('dialog', '1');
            $jumpUrl = $_POST['forward'] ? $_POST['forward'] : U(MODULE_NAME . '/index');
            $this->assign('jumpUrl', $jumpUrl);
            $this->success(L('edit_ok'));
        } else {
            $this->success(L('edit_error') . ': ' . $model->getDbError());
        }
    }

    /**
     * 删除
     *
     */
    function delete() {
        $name = MODULE_NAME;
        $model = M($name);
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        if (isset($id)) {
            if (false !== $model->delete($id)) {
                if (in_array($name, $this->cache_model))
                    savecache($name);
                if ($this->moduleid) {
                    $fields = $model->getDbFields();
                    delattach(array('moduleid' => $this->moduleid, 'id' => $id));
                    if ($fields['keywords']) {
                        $olddata = $model->field('keywords')->find($id);
                        $where['name'] = array('in', $olddata['keywords']);
                        $where['moduleid'] = array('eq', $this->moduleid);
                        if (APP_LANG)
                            $where['lang'] = array('eq', LANG_ID);
                        M('Tags')->where($where)->setDec('num');
                        M('Tags_data')->where("id=" . $id)->delete();
                    }
                }
                if ($name == 'Order')
                    M('Order_data')->where('order_id=' . $id)->delete();
                $this->assign('jumpUrl', U(MODULE_NAME . '/index'));
                $this->success(L('delete_ok'));
            }else {
                $this->error(L('delete_error') . ': ' . $model->getDbError());
            }
        } else {
            $this->error(L('do_empty'));
        }
    }

    /**
     * 批量删除
     *
     */
    function deleteall() {

        $name = MODULE_NAME;
        $model = M($name);
        $ids = $_POST['ids'];
        if (!empty($ids) && is_array($ids)) {
            $id = implode(',', $ids);
            if (false !== $model->delete($id)) {
                if (in_array($name, $this->cache_model))
                    savecache($name);
                if ($this->moduleid) {
                    $fields = $model->getDbFields();
                    delattach("moduleid=$this->moduleid and id in($id)");
                    if ($fields['keywords']) {
                        $olddata = $model->field('keywords')->where("id in($id)")->select();
                        foreach ((array) $olddata as $r) {
                            $where['name'] = array('in', $r['keywords']);
                            $where['moduleid'] = array('eq', $this->moduleid);
                            if (APP_LANG)
                                $where['lang'] = array('eq', LANG_ID);
                            M('Tags')->where($where)->setDec('num');
                        }
                        M('Tags_data')->where("id in($id)")->delete();
                        M('Tags')->where('num<=0')->delete();
                    }
                }
                if ($name == 'Order')
                    M('Order_data')->where('order_id in(' . $id . ')')->delete();
                $this->success(L('delete_ok'));
            }else {
                $this->error(L('delete_error') . ': ' . $model->getDbError());
            }
        } else {
            $this->error(L('do_empty'));
        }
    }

    /**
     * 批量操作
     *
     */
    public function listorder() {
        $name = MODULE_NAME;
        $model = M($name);
        $pk = $model->getPk();
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            $model->where($pk . '=' . $key)->save($data);
        }
        if (in_array($name, $this->cache_model))
            savecache($name);
        $this->success(L('do_ok'));
    }

    /* 状态 */

    public function status() {
        $name = MODULE_NAME;
        $model = D($name);
        $_GET = get_safe_replace($_GET);
        if ($model->save($_GET)) {
            savecache(MODULE_NAME);
            $this->success(L('do_ok'));
        } else {
            $this->error(L('do_error'));
        }
    }

    /**
     * 默认操作
     *
     */
    public function index() {
        $name = MODULE_NAME;
        $model = M($name);
        $id = $model->getPk();
        $count = $model->where($_REQUEST['where'])->count();
        import("@.ORG.Page");
        $p = new Page($count, 15);
        unset($_GET[C('VAR_PAGE')]);
        $map = $_GET;
        $map[C('VAR_PAGE')] = '{$page}';
        $p->urlrule = U($name . '/index', $map);
        $page = $p->show();

        $list = $model->where($_REQUEST['where'])->order("$id desc")->limit($p->firstRow . ',' . $p->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }

    /**
     * 后台更新文档时生成内容页html操作。
	 * $id:更新的内容ID
	 * $module: 更新的内容所属的模型
     *
     */
    public function create_show($id, $module) {
        C('DEFAULT_THEME_NAME', $this->sysConfig['DEFAULT_THEME']);
        C('HTML_FILE_SUFFIX', $this->sysConfig['HTML_FILE_SUFFIX']);
        C('TMPL_FILE_NAME', str_replace('Admin/Default', 'Home/' . $this->sysConfig['DEFAULT_THEME'], C('TMPL_FILE_NAME')));


        if (APP_LANG) {
            C('TMPL_CACHFILE_SUFFIX', '_' . LANG_NAME . '.php');
            $lang = C('URL_LANG') != LANG_NAME ? $lang = LANG_NAME . '/' : '';
            L(include LANG_PATH . LANG_NAME . '/common.php');
            $T = F('config_' . LANG_NAME, '', './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
        } else {
            L(include LANG_PATH . $this->sysConfig['DEFAULT_LANG'] . '/common.php');
            $T = F('config_' . $this->sysConfig['DEFAULT_LANG'], '', './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
        }
        $this->assign('T', $T);
        foreach ((array) $this->module as $r) {
            if ($r['issearch'])
                $search_module[$r['name']] = L($r['name']);
        }
        $this->assign('search_module', $search_module);
        $this->assign('form', new Form());
        $p = 1;
        $id = intval($id);
        if (empty($id))
            $this->success(L('do_empty'));;
        $this->assign($this->Config);
        $this->assign('Categorys', $this->categorys);
        $this->assign('Module', $this->module);
        $this->assign('Type', $this->Type);
        $this->assign('module_name', $module);
        $dao = M($module);
        $data = $dao->find($id);

        $catid = $data['catid'];
        $this->assign('catid', $catid);
        $cat = $this->categorys[$data['catid']];
        $this->assign($cat);
        $bcid = explode(",", $cat['arrparentid']);
        $bcid = $bcid[1];
        if ($bcid == '')
            $bcid = intval($catid);
        $this->assign('bcid', $bcid);

        $seo_title = $data['title'] . '-' . $cat['catname'];
        $this->assign('seo_title', $seo_title);
        $this->assign('seo_keywords', $data['keywords']);
        $this->assign('seo_description', $data['description']);

        $fields = F($this->mod[$module] . '_Field');
        foreach ($data as $key => $c_d) {
            $setup = '';
            $fields[$key]['setup'] = $setup = string2array($fields[$key]['setup']);
            if ($setup['fieldtype'] == 'varchar' && $fields[$key]['type'] != 'text') {
                $data[$key . '_old_val'] = $data[$key];
                $data[$key] = fieldoption($fields[$key], $data[$key]);
            } elseif ($fields[$key]['type'] == 'images' || $fields[$key]['type'] == 'files') {
                $p_data = explode(':::', $data[$key]);
                $data[$key] = array();
                foreach ($p_data as $k => $res) {
                    $p_data_arr = explode('|', $res);
                    $data[$key][$k]['filepath'] = $p_data_arr[0];
                    $data[$key][$k]['filename'] = $p_data_arr[1];
                }
                unset($p_data);
                unset($p_data_arr);
            }
            unset($setup);
        }
        $this->assign('fields', $fields);
        $this->assign('form', new Form());

        $urlrule = geturl($cat, $data, $this->Urlrule);

        if (!empty($data['template'])) {
            $template = $cat['module'] . '_' . $data['template'];
        } elseif (!empty($cat['template_show'])) {
            $template = $cat['module'] . '_' . $cat['template_show'];
        } else {
            $template = $cat['module'] . '_show';
        }
        //手动分页
        $CONTENT_POS = strpos($data['content'], '[page]');
        if ($CONTENT_POS !== false) {

            $pageurls = array();
            $contents = array_filter(explode('[page]', $data['content']));
            $pagenumber = count($contents);
            for ($i = 1; $i <= $pagenumber; $i++) {
                $pageurls[$i] = str_replace('{$page}', $i, $urlrule);
            }
            //生成分页
            foreach ($pageurls as $p => $urls) {
                $pages = content_pages($pagenumber, $p, $pageurls);
                $this->assign('pages', $pages);
                $data['content'] = $contents[$p - 1];
                $this->assign($data);
                $url = ($p > 1 ) ? $urls[1] : $urls[0];
                if (strstr($url, C('HTML_FILE_SUFFIX'))) {
                    $filename = basename($url, C('HTML_FILE_SUFFIX'));
                    $dir = dirname($url) . '/';
                } else {
                    $filename = 'index';
                    $dir = $url;
                }
                $dir = substr($dir, strlen(__ROOT__ . '/'));
                $this->buildHtml($filename, $dir, './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/' . $template . C('TMPL_TEMPLATE_SUFFIX'));
            }
        } else {
            $url = str_replace('{$page}', $p, $urlrule[0]);
            if (strstr($url, C('HTML_FILE_SUFFIX'))) {
                $filename = basename($url, C('HTML_FILE_SUFFIX'));
                $dir = dirname($url) . '/';
            } else {
                $filename = 'index';
                $dir = $url;
            }
            $this->assign('pages', '');
            $this->assign($data);
            $dir = substr($dir, strlen(__ROOT__ . '/'));
            $this->buildHtml($filename, $dir, './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/' . $template . C('TMPL_TEMPLATE_SUFFIX'));
        }

        return true;
    }
	
	/**
	 * 后台更新栏目及其文档时生成列表页html文件
	*/
    public function create_list($catid, $p = 1, $count = 0) {
        C('DEFAULT_THEME_NAME', $this->sysConfig['DEFAULT_THEME']);
        C('HTML_FILE_SUFFIX', $this->sysConfig['HTML_FILE_SUFFIX']);
        C('TMPL_FILE_NAME', str_replace('Admin/Default', 'Home/' . $this->sysConfig['DEFAULT_THEME'], C('TMPL_FILE_NAME')));

        if (APP_LANG) {
            C('TMPL_CACHFILE_SUFFIX', '_' . LANG_NAME . '.php');
            $lang = C('URL_LANG') != LANG_NAME ? $lang = LANG_NAME . '/' : '';
            L(include LANG_PATH . LANG_NAME . '/common.php');
            $T = F('config_' . LANG_NAME, '', './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
        } else {
            L(include LANG_PATH . $this->sysConfig['DEFAULT_LANG'] . '/common.php');
            $T = F('config_' . $this->sysConfig['DEFAULT_LANG'], '', './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
        }
        $this->assign('T', $T);
        foreach ((array) $this->module as $r) {
            if ($r['issearch'])
                $search_module[$r['name']] = L($r['name']);
        }
        $this->assign('search_module', $search_module);
        $this->assign('form', new Form());

        $this->assign($this->Config);
        $this->assign('Categorys', $this->categorys);
        $this->assign('Module', $this->module);
        $this->assign('Type', $this->Type);
        $catid = intval($catid);
        if (empty($catid))
            $this->success(L('do_empty'));

        $cat = $this->categorys[$catid];
        $this->assign('catid', $catid);
        if ($cat['type'])
            return;
        if (empty($cat['ishtml']))
            return;
        unset($cat['id']);
        $this->assign($cat);
        $cat['id'] = $catid;
        $bcid = explode(",", $cat['arrparentid']);
        $bcid = $bcid[1];
        if ($bcid == '')
            $bcid = intval($catid);
        $this->assign('bcid', $bcid);

        $urlrule = geturl($cat, '', $this->Urlrule);
        $url = ($p > 1 ) ? $urlrule[1] : $urlrule[0];
        $url = str_replace('{$page}', $p, $url);
        if (strstr($url, C('HTML_FILE_SUFFIX'))) {
            $filename = basename($url, C('HTML_FILE_SUFFIX'));
            $dir = dirname($url) . '/';
        } else {
            $filename = 'index';
            $dir = $url;
        }
        $dir = substr($dir, strlen(__ROOT__ . '/'));
        if (empty($module))
            $module = $cat['module'];
        $this->assign('module_name', $module);


        $this->assign('fields', F($cat['moduleid'] . '_Field'));
        $this->assign('form', new Form());


        if ($cat['moduleid'] == 1) {
            $cat['listtype'] = 2;
            $module = $cat['module'];
            $dao = M($module);
            $data = $dao->find($catid);
            $seo_title = $cat['title'] ? $cat['title'] : $data['title'];
            $this->assign('seo_title', $seo_title);
            $this->assign('seo_keywords', $data['keywords']);
            $this->assign('seo_description', $data['description']);

            $template = $cat['template_list'] ? $cat['template_list'] : 'index';
            //手动分页
            $CONTENT_POS = strpos($data['content'], '[page]');

            if ($CONTENT_POS !== false) {

                $contents = array_filter(explode('[page]', $data['content']));
                $pagenumber = count($contents);
                for ($i = 1; $i <= $pagenumber; $i++) {
                    $pageurls[$i] = str_replace('{$page}', $i, $urlrule);
                }
                //生成分页
                foreach ($pageurls as $p => $urls) {
                    $pages = content_pages($pagenumber, $p, $pageurls);
                    $this->assign('pages', $pages);
                    $data['content'] = $contents[$p - 1];
                    $this->assign($data);
                    if ($p > 1)
                        $filename = basename($pageurls[$p]['1'], C('HTML_FILE_SUFFIX'));
                    //$this->buildHtml($filename,$dir,'Home/'.$template);
                    $r = $this->buildHtml($filename, $dir, './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/Page_' . $template . C('TMPL_TEMPLATE_SUFFIX'));
                }
            }else {
                $this->assign($data);
                //$r=$this->buildHtml($filename,$dir,'Home/'.$template);
                $r = $this->buildHtml($filename, $dir, './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/Page_' . $template . C('TMPL_TEMPLATE_SUFFIX'));
            }
        } else {

            $seo_title = $cat['title'] ? $cat['title'] : $cat['catname'];
            $this->assign('seo_title', $seo_title);
            $this->assign('seo_keywords', $cat['keywords']);
            $this->assign('seo_description', $cat['description']);

            if ($cat['listtype'] == 1) {
                $template_r = 'index';
            } else {
                $where = " status=1 ";
                if ($cat['child']) {
                    $where .= " and catid in(" . $cat['arrchildid'] . ")";
                } else {
                    $where .= " and catid=" . $catid;
                }

                $module = $cat['module'];
                $dao = M($module);
                if (empty($count))
                    $count = $dao->where($where)->count();
                if ($count) {
                    import("@.ORG.Page");
                    $listRows = !empty($cat['pagesize']) ? $cat['pagesize'] : C('PAGE_LISTROWS');
                    $page = new Page($count, $listRows, $p);
                    $page->urlrule = $urlrule;
                    $pages = $page->show();
                    $field = $this->module[$this->mod[$module]]['listfields'];
                    $field = $field ? $field : 'id,catid,userid,url,username,title,title_style,keywords,description,thumb,createtime,hits';

                    $list = $dao->field($field)->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
                    $this->assign('pages', $pages);
                    $this->assign('list', $list);
                }
                $template_r = 'list';
            }

            $template = $cat['template_list'] ? $cat['template_list'] : $template_r;
            $r = $this->buildHtml($filename, $dir, './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/' . $cat['module'] . '_' . $template . C('TMPL_TEMPLATE_SUFFIX'));
        }
        if ($r)
            return true;
    }

	/**
	 * 生成首页html文件
	*/
    public function create_index($sitemap) {
        C('HTML_FILE_SUFFIX', $this->sysConfig['HTML_FILE_SUFFIX']);
        C('DEFAULT_THEME_NAME', $this->sysConfig['DEFAULT_THEME']);
        C('TMPL_FILE_NAME', str_replace('Admin/Default', 'Home/' . $this->sysConfig['DEFAULT_THEME'], C('TMPL_FILE_NAME')));


        if (APP_LANG) {
            C('TMPL_CACHFILE_SUFFIX', '_' . LANG_NAME . '.php');
            $lang = C('URL_LANG') != LANG_NAME ? $lang = LANG_NAME . '/' : '';
            L(include LANG_PATH . LANG_NAME . '/common.php');
            $T = F('config_' . LANG_NAME, '', './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
        } else {
            L(include LANG_PATH . $this->sysConfig['DEFAULT_LANG'] . '/common.php');
            $T = F('config_' . $this->sysConfig['DEFAULT_LANG'], '', './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/');
        }
        $this->assign('T', $T);
        foreach ((array) $this->module as $r) {
            if ($r['issearch'])
                $search_module[$r['name']] = L($r['name']);
        }
        $this->assign('search_module', $search_module);


        $this->assign('form', new Form());
        $this->assign('Module', $this->module);
        $this->assign('Type', $this->Type);
        $this->assign($this->Config);
        $this->assign('Categorys', $this->categorys);
        //$r=$this->buildHtml('index','./','Home/Index_index');
		
		// 如果参数为空，则更新首页html
        if (empty($sitemap)) {
            $this->assign('ishome', 'home');
            if (!$this->sysConfig['HOME_ISHTML'])
                $this->error(L('NO_HOME_ISHTML'));
            $this->assign('bcid', 0);
            $r = $this->buildHtml('index', './' . $lang, './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/Index_index' . C('TMPL_TEMPLATE_SUFFIX'));
		// 如果参数为true，则更新网站地图html
        }else {
            $this->assign('sitemap', '1');
            $r = $this->buildHtml('sitemap', './' . $lang, './Myphp/Tpl/Home/' . $this->sysConfig['DEFAULT_THEME'] . '/Sitemap' . C('TMPL_TEMPLATE_SUFFIX'));
        }
        if ($r)
            return true;
    }

    function clisthtml($id) {
        $pagesize = 10;
        $p = max(intval($p), 1);
        $j = 1;
        do {
            $this->create_list($id, $p);
            $j++;
            $p++;
            $pages = isset($pages) ? $pages : PAGESTOTAL;
        } while ($j <= $pages && $j < $pagesize);
    }
	
	// 判断请求的动作是否存在，不存在则跳转错误。
	public function __call($name, $arguments) {

		throw_exception('404');
	}


}

?>