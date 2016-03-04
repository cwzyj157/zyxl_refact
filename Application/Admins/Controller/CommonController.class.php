<?php
namespace Admins\Controller;

use Think\Controller;
use Admins\Model\AuthRuleModel;
use Org\CommonHelper;

class CommonController extends Controller
{
    protected $is_login = false;
    protected $is_administrator = false;
    /**
     * 后台控制器初始化
     */
    protected function _initialize()
    {
        $commonHelper = new CommonHelper();
        $this->is_login = $commonHelper->is_login();
        if ($this->is_login === false) {
            $this->redirect('Login/index');
        }
        $is_administrator = $commonHelper->is_administrator();

        $access = $this->accessControl();
        if ($access === false) {
            $this->error('403:禁止访问');
        } elseif ($access === null) {
            $dynamic = $this->checkDynamic();//检测分类栏目有关的各项动态权限
            if ($dynamic === null) {
                //检测非动态权限
                $rule = strtolower(MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME);
                if (!$this->checkRule($rule, array('in', '1,2'))) {
                    $this->error('很抱歉，您当前没有访问该页面(执行该操作)的权限,如有需要请联系管理员!');
                }
            } elseif ($dynamic === false) {
                $this->error('很抱歉，您当前没有访问该页面(执行该操作)的权限,如有需要请联系管理员!');
            }
        }
        //读取列表返回路径
        define('back_url', Cookie('__forward__') ? Cookie('__forward__') : U());

        $this->assign('WEB_URL', C('WEB_URL'));
        $this->assign('WEB_NAME', C('WEB_NAME'));
        $this->assign('VERSION', C('SYSTEM_VERSION'));
        $this->assign('UPDATETIME', C('SYSTEM_UPDATETIME'));
        $this->assign('__MENU__', $this->getMenus());
    }

    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     */
    final protected function accessControl()
    {
        if ($this->is_administrator) {
            return true;//管理员允许访问任何页面
        }
        $allow = C('ALLOW_VISIT');
        $deny = C('DENY_VISIT');
        $check = strtolower(CONTROLLER_NAME . '/' . ACTION_NAME);

        if (!empty($deny) && in_array_case($check, $deny)) {
            return false;//非超管禁止访问deny中的方法
        }
        if (!empty($allow) && in_array_case($check, $allow)) {
            return true;
        }
        return null;//需要检测节点权限
    }

    /**
     * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
     */
    final public function getMenus($controller = CONTROLLER_NAME)
    {
        //获取主菜单
        $menuData = M('Menu');
        $where['pid'] = 0;
        $where['hide'] = 0;
        $menus['main'] = $menuData->where($where)->order('sort asc')->select();
        //设置子节点
        $menus['child'] = array();
        //高亮主菜单
        $current = $menuData->where("url like '%{$controller}/" . ACTION_NAME . "%'")->field('id')->find();
        if ($current) {
            $nav = D('Menu')->getPath($current['id']);
            $nav_first_title = $nav[0]['title'];
            foreach ($menus['main'] as $key => $item) {
                if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
                    $this->error('控制器基类$menus属性元素配置有误');
                }
                if (stripos($item['url'], MODULE_NAME) !== 0) {
                    $item['url'] = MODULE_NAME . '/' . $item['url'];
                }
                // 判断主菜单权限
                if ($this->is_administrator == false && !$this->checkRule($item['url'], AuthRuleModel::RULE_MAIN, null)) {
                    unset($menus['main'][$key]);
                    continue;//继续循环
                }
                // 获取当前主菜单的子菜单项
                if ($item['title'] == $nav_first_title) {
                    $menus['main'][$key]['class'] = 'cur';
                    //生成child树
                    $groups = $menuData->where("pid = {$item['id']}")->distinct(true)->field("`group`")->order('sort asc')->select();
                    if ($groups) {
                        $groups = array_column($groups, 'group');
                    } else {
                        $groups = array();
                    }
                    $where = array();
                    $where['pid'] = $item['id'];
                    $where['hide'] = 0;
                    $second_urls = $menuData->where($where)->getField('id,url');
                    // 检测菜单权限
                    if ($this->is_administrator == false) {
                        $to_check_urls = array();
                        foreach ($second_urls as $key => $to_check_url) {
                            if (stripos($to_check_url, MODULE_NAME) !== 0) {
                                $rule = MODULE_NAME . '/' . $to_check_url;
                            } else {
                                $rule = $to_check_url;
                            }
                            if ($this->checkRule($rule, AuthRuleModel::RULE_URL, null))
                                $to_check_urls[] = $to_check_url;
                        }
                    }
                    foreach ($groups as $g) {
                        $map = array('group' => $g);
                        if (isset($to_check_urls)) {
                            if (empty($to_check_urls)) {
                                //没有任何权限
                                continue;
                            } else {
                                $map['url'] = array('in', $to_check_urls);
                            }
                        }
                        $map['pid'] = $item['id'];
                        $map['hide'] = 0;
                        $menuList = $menuData->where($map)->field('id,pid,title,url')->order('sort asc')->select();
                        $menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                    }
                    if ($menus['child'] === array()) {
                        //$this->error('主菜单下缺少子菜单，请去系统=》后台菜单管理里添加');
                    }
                }
            }
        }
        return $menus;
    }

    /**
     * 权限检测
     * @param string $rule 检测的规则
     * @param string $mode check模式
     * @return boolean
     */
    final protected function checkRule($rule, $type = AuthRuleModel::RULE_URL, $mode = 'url')
    {
        if ($this->is_administrator) {
            return true;//管理员允许访问任何页面
        }
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \Think\Auth();
        }
        if (!$Auth->check($rule, UID, $type, $mode)) {
            return false;
        }
        return true;
    }

    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则会进入checkRule根据节点授权判断权限
     *
     */
    protected function checkDynamic()
    {
        if ($this->is_administrator) {
            return true;//管理员允许访问任何页面
        }
        return null;//不明,需checkRule
    }

}