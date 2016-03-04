<?php
namespace Admins\Model;
use Think\Model;
class CollectModel extends Model {
	protected $_validate = array(
		array('url','require','链接Url必须填写',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
	);
	protected $_auto = array(
		array('update_time', NOW_TIME, self::MODEL_BOTH),
    );
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * 将格式数组转换为�?
	 *
	 * @param array $list
	 * @param integer $level 进行递归时传递用的参�?
	 */
	
	//但页面采集插件规�?
	//
	public function (){
	
	}
}