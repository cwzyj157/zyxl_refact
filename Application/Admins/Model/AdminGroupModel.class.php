<?php
namespace Admins\Model;
use Think\Model;
class AdminGroupModel extends Model {
    protected $_validate = array(
        array('title','require','权限组标题不能为�?,请输入标�?', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title','','该权限组标题已经存在,请更�?', self::MUST_VALIDATE, 'unique', self::MODEL_BOTH),
    );
	public function getSecondtree($pid=0,$u_rules){
		$Sec_html = '';
		$map = array();
		$map['pid'] = $pid;
		$map['id']  = array('not in','24,25');
		$result = M('Menu')->field('title,id')->where($map)->select();
		foreach ($result as $key){
			$Sec_html .='<div class="M_class"><span class="selectbll" id="S_sCheck_'.$key['id'].'" onclick="secondCheck('.$key['id'].')">全部选中</span><label for="Smenu_'.$key['id'].'"><input name="uRules[]" id="Smenu_'.$key['id'].'" type="checkbox" value="'.$key['id'].'" '.D('AdminGroup')->check_checked($key['id'],$u_rules).'/>'.$key['title'].'</label></div>';
			$Sec_html .= D('AdminGroup')->getThirdtree($key['id'],$u_rules);			
		}
		return $Sec_html;
	}
	
	public function getThirdtree($pid,$u_rules){
		$thi_html = '';
		$map = array();
		$map['pid'] = $pid;
		$map['id']  = array('not in','37,38,39,40,58');
		$thi_result = M('Menu')->field('title,id')->where($map)->select();
		if ($thi_result){
			$thi_html .= '<ul class="clearfix" id="secondLevel'.$pid.'">';
			foreach ($thi_result as $tkey){
				$thi_html .='<li><label><input name="uRules[]" type="checkbox" value="'.$tkey['id'].'" '.D('AdminGroup')->check_checked($tkey['id'],$u_rules).' />'.$tkey['title'].'</label></li>';
			}
		}
			$thi_html .= '</ul>';
		return $thi_html;
	}
	
	public function check_checked($checkid,$u_rules){
		$str_a = ','.$checkid.',';
		$str_b = 'r:,'.$u_rules.',';
		if (strpos($str_b,$str_a) > 0){
			return ' checked="checked"';
		}else{
			return '';
		}
	}
}