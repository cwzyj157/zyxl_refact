<?php
define('TMPL_PATH','./Template/');
return array(
	//'配置项'=>'配置值'
    /* 数据库配置 */
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'zyxl_db', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'koo_', // 数据库表前缀

    'USER_ADMINISTRATOR'		=>  1, // 超级管理员用户ID
    'URL_MODEL'                 =>  0, // 如果你的环境不支持PATHINFO 请设置为3
    'COOKIE_PREFIX'             => 'kooAdmins_cookie',      // Cookie前缀 避免冲突
    'SESSION_PREFIX'            => 'kooAdmins_session', // session 前缀
    'TMPL_ACTION_ERROR'         =>  'Common:success', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'       =>  'Common:success', // 默认成功跳转对应的模板件
    'URL_CASE_INSENSITIVE'		=> true,
    'SHOW_ERROR_MSG'       		=> true,
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__COMMON__' => __ROOT__ . '/Public/Common',
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/script',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/style',
    ),


    'MODULE_DENY_LIST'          =>  array('Common','Runtime'),


    'SESSION_AUTO_START'        =>  true,
    'USER_AUTH_KEY'				=>  'mt2ZKJqg6zSX0TjyGD5LNibE4fBI3xrcs1hV8Pnd',
    'MODULE_ALLOW_LIST'     	=> array('Home','Admins'),
    'DEFAULT_MODULE'        	=> 'Home',  // 榛樿妯″潡
);