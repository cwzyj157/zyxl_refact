<?php
namespace Admins\Model;
use Think\Model;
class TemplateModel extends Model {
	protected $_validate = array(
		array('url','require','菜单Url必须填写',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
		array('title','require','菜单名称不能为空',self::MUST_VALIDATE,'regex',self::MODEL_BOTH)
	);
	//protected $_auto = array(
	//	array('tempContent', 'htmlspecialchars_decode', self::MODEL_BOTH, 'function'),
	//):
	/*
	 * 遍历模版结构
	*/
	public function format_tree(){
		
	}
}