<?php
namespace Admins\Model;
use Think\Model;


class MenuModel extends Model {
	protected $_validate = array(
		array('url','require','菜单Url必须填写',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
		array('title','require','菜单名称不能为空',self::MUST_VALIDATE,'regex',self::MODEL_BOTH)
	);

	//获取树的根到子节点的路径
	public function getPath($id){
		$path = array();
		$nav = M('Menu')->where("id={$id}")->field('id,pid,title')->find();
		$path[] = $nav;
		if($nav['pid'] > 0){
			$path = array_merge($this->getPath($nav['pid']),$path);
		}
		return $path;
	}
}