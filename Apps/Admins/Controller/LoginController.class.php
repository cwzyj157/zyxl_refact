<?php
namespace Admins\Controller;

use Org\ConfigHelper;
use Admins\Controller;

class LoginController extends CommonController
{
    protected  function _initialize()
    {
        echo '111';

        $config = S('DB_CONFIG_DATA');
        if ($config == false) {
            $configHelper = new ConfigHelper();
            $config = $configHelper->get_config_lists();
            S('DB_CONFIG_DATA', $config);
        }
        // 添加配置
        C($config);
        $this->assign('WEB_URL', C('WEB_URL'));
        $this->assign('WEB_NAME', C('WEB_NAME'));
    }
    public  function  index(){
        $this->assign('Index/login');
    }
}
