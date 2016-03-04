<?php
namespace Admins\Model;
use Think\Model;

class ConfigModel extends Model {
    protected $_validate = array(
        array('name', 'require', '配置标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '配置标识已经存在,请更�?', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('name', 'strtoupper', self::MODEL_BOTH, 'function'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
    );

}