<?php
namespace Admins\Model;
use Think\Model;

/*
ThinkPHP 自动验证
array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证
时间]),
验证字段: 不一定是数据库字段，也可以是表单的一些辅助字�?
验证规则�? TP默认 : require 字段必须、email 邮箱、url URL地址、currency 货币、number 数字
错误提示:

验证条件:（可选）
self::EXISTS_VALIDATE 或�??0 存在字段就验证（默认�?
self::MUST_VALIDATE   或�??1 必须验证
self::VALUE_VALIDATE  或�??2 值不为空的时候验�?

附加规则 （可选）
regex       正则验证，定义的验证规则是一个正则表达式（默认）
function    函数验证，定义的验证规则是一个函数名
callback    方法验证，定义的验证规则是当前模型类的一个方�?
confirm		验证表单中的两个字段是否相同，定义的验证规则是一个字段名
equal       验证是否等于某个值，该�?�由前面的验证规则定�?
notequal    验证是否不等于某个�?�，该�?�由前面的验证规则定义（3.1.2版本新增�?
in          验证是否在某个范围内，定义的验证规则可以是一个数组或者�?�号分割的字符串
notin       验证是否不在某个范围内，定义的验证规则可以是�?个数组或者�?�号分割的字符串�?3.1.2版本新增�?
length      验证长度，定义的验证规则可以是一个数字（表示固定长度）或者数字范围（例如3,12 表示长度�?3�?12的范围）
between     验证范围，定义的验证规则表示范围，可以使用字符串或�?�数组，例如1,31或�?�array(1,31)
notbetween  验证不在某个范围，定义的验证规则表示范围，可以使用字符串或�?�数�?
expire      验证是否在有效期，定义的验证规则表示时间范围，可以到时间，例如可以使�? 2012-1-15,2013-1-15 表示当前提交有效期在2012-1-15�?2013-1-15之间，也可以使用时间戳定�?
ip_allow    验证IP是否允许，定义的验证规则表示允许的IP地址列表
ip_deny     验证IP是否禁止，定义的验证规则表示禁止的ip地址列表，用逗号分隔
unique      验证是否唯一，系统会根据字段目前的�?�查询数据库来判断是否存在相同的值，当表单数据中包含主键字段时unique不可用于判断主键字段本身


验证时间�?
self::MODEL_INSERT   或�??1新增数据时�?�验�?
self::MODEL_UPDATE   或�??2编辑数据时�?�验�?
self::MODEL_BOTH     或�??3全部情况下验证（默）
*/


class AdminerModel extends Model {
    protected $_validate = array(
        array('username','4,20','管理员帐号必须在4-20之间', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('username','','该管理员登录帐号已经存在,请更�?', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
        array('truename','require', '管理员真实姓名不能为�?', self::EXISTS_VALIDATE , 'regex', self::MODEL_BOTH),
		array('password','6,20', '登录密码必须�?6-20位之�?', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
		array('repassword','password', '两次登录密码输入不一致，请返回重新输�?', self::EXISTS_VALIDATE , 'confirm', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('login_times',0, self::MODEL_INSERT),
		array('last_login_ip',0, self::MODEL_INSERT),
		array('last_login_time', 0, self::MODEL_INSERT),
		array('password','',self::MODEL_UPDATE,'ignore')
    );
	
	public function addsyslogs($adminid,$logtext,$logtype,$logkey){		
		if(!$adminid || !$logtext){
			return false;
		}
		if($adminid == C('USER_ADMINISTRATOR')){
			return false;
		}
		$data = array(
			'adminid' => $adminid,
			'logtext' => $logtext,
			'logtype' => $logtype,
			'logkey'  => $logkey,
			'log_time' => time(),
		);
		M('Logs')->add($data);
		return true;
	}
}