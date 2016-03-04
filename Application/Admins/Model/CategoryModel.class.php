<?php
namespace Admins\Model;
use Think\Model;

class CategoryModel extends Model {
    protected $_validate = array(
        array('title', 'require', '栏目名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
		// array('name', 'require', '栏目英文标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        // array('name', '', '栏目英文标识已经存在,请更�?', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
		// array('template_index', 'require', '栏目首页模版不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
		// array('template_lists', 'require', '栏目列表页模版不能为�?', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
		// array('template_detail', 'require', '栏目内容页模版不能为�?', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
		array('seotitle', '1,50', '网页标题不能超过50个字�?', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
    	array('keywords', '1,150', '网页关键字不能超�?150个字�?', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
    	array('description', '1,255', '网页描述不能超过255个字�?', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),		
    );

    protected $_auto = array(
        array('childid', '0', self::MODEL_INSERT),
		array('status', '1', self::MODEL_INSERT),
    );
	
	/*
	 * 重置栏目结构
	*/
	public function format_tree(){
		$cModel = D('Category');
		$result = M('Category')->field('id,pid')->select();
		if ($result){
			foreach($result as $key=>$value){
				$str_childid = $cModel->getChildid($result[$key]['id'],$result[$key]['id']);
				$str_parentpath = $cModel->getParentpath($result[$key]['pid']);
				$str_childid = $cModel->format_array($str_childid);
				$str_parentpath = $cModel->format_array($str_parentpath);
				$data['childid'] = $str_childid;
				$data['parentpath'] = $str_parentpath;
				$array_path = explode(',',$str_parentpath);
				$data['rootid'] = count($array_path);
				M('Category')->where('id='.$result[$key]['id'])->save($data);
			}
		}
		return true;
	} 
	public function getChildid($id,$strValue){
		$tempD = M('Category')->where('pid='.$id)->field('id')->select();
		if ($tempD){
			foreach($tempD as $key=>$value){
				$strValue .= ','.D('Category')->getChildid($tempD[$key]['id'],$tempD[$key]['id']);
			}
		}		
		return $strValue;
	}
	
	public function getParentpath($id,$value){
		$temp_P = '0';
		$p_result = M('Category')->where('id='.$id)->field('id,pid')->find();
		if ($p_result){
			$temp_P .=','.$p_result['id'];
			$temp_P .=','.D('Category')->getParentpath($p_result['pid']);
		}
		return $temp_P;
	}
	
	public function format_array($arr_str){
		$arr_p = explode(',',$arr_str);
		$arr_p = a_array_unique($arr_p);
		sort($arr_p);
		$temp_P = '';
		for($i = 0;$i < count($arr_p);$i++){
			$temp_P .= ','.$arr_p[$i];
		}
		$temp_P = substr($temp_P,1);
		return $temp_P;
		
	}
	
    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类�?
     */
    public function getTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取�?有分�? */
        $map  = array('status' => array('gt', -1));
        $list = $this->field($field)->where($map)->order('sort')->select();
		int_to_string($list,array('allow_publish'=>array(1=>'<font color="#008000">�?</font>',0=>'�?')));
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);
        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回�?有分�?
            $info = $list;
        }

        return $info;
    }
	
	public function checkcaterule($uRules,$cateid){
		if(!$uRules){
			return false;
		}
		if($uRules == 'all'){
			return true;
		}else{
			$arr = explode(',',$uRules);
			if (in_array($cateid,$arr)){
				return true;
			}else{
				return false;
			}
		}
	}
	
}