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

    'COOKIE_PREFIX'             => 'KooThink_cookie',      // Cookie前缀 避免冲突
    'SESSION_PREFIX'            => 'demo_session',      // session 前缀

);