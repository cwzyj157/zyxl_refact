<?php
namespace Admins\Model;
use Think\Model;


class DataModel extends Model {
	protected $_validate = array(
		array('title','require','文档标题不能为空,请返回重新填�?',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
		array('title','1,150', '文档标题不能超过150个字�?', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
		array('Author', '1,50', '文档来源不能超过50个字�?', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
		array('cateid','checkcateid', '�?选栏目禁止发布内容，请重新�?�择', self::MUST_VALIDATE , 'callback', self::MODEL_BOTH),
		array('doc_url','require','单页面文档url不能为空,请返回重新填�?',self::VALUE_VALIDATE,'regex',self::MODEL_BOTH),
		array('doc_url','1,100','单页面文档url不能超过100字符',self::VALUE_VALIDATE,'length',self::MODEL_BOTH),
	);
	
	protected $_auto = array(
        array('status',1, self::MODEL_INSERT),
		array('clicktimes',0, self::MODEL_INSERT),
		//array('create_time',NOW_TIME, self::MODEL_INSERT),
		array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

	public function thumpicSize($cateid=0){
		switch ($cateid){
			case 6: return '180x240'; break;
			default: return '360x200'; break;
		}
	}
	
	public function checkcateid(){
		$cateid = intval(I('post.cateid',0));
		$is_public = M('Category')->where('id='.$cateid)->getfield('allow_publish');
		if (!$is_public){
			return false;
		}else{
			return true;
		}	
	}
	
	// 返回标签ID�?
	public function addTags($dataid,$modelid,$newtags,$oldtags=''){
		if (!$newtags){
			return false;
		}
		$newtags = array_unique_char($newtags,' ');
		$arr_newtag = explode(' ',$newtags);
		$tmptag = '';
		for ($i=0;$i<count($arr_newtag);$i++){
			$tagname = $arr_newtag[$i];
			if ($tagname){
				$map['tagname'] = $tagname;
				$map['modelid'] = $modelid;
				$tagid = M('Tags')->where($map)->getfield('id');
				if ($tagid){
					$addtagsid = $tagid;
				}else{
					$sort = M('Tags')->where('modelid='.$modelid)->max(sort);
					if ($sort){
						$sort = $sort + 1;
					}else{
						$sort = 1;
					}
					$data['tagname']  = $tagname;
					$data['modelid']  = $modelid;
					$data['is_show']  = 0;
					$data['countnum'] = 0;
					$data['sort']     = $sort;
					$addtagsid = M('Tags')->add($data);
				}
				if ($tmptag){
					$tmptag .= ','.$addtagsid;
				}else{
					$tmptag = $addtagsid;
				}
				$this->reserTagnum($addtagsid,$dataid);
			}
		}
		$savedata['tags'] = $tmptag;
		$result = M('Data')->where("id=$dataid")->save($savedata);
		//去掉历史tag
		$his_arr = explode(',',$oldtags);
		$new_arr = explode(',',$tmptag);
		$diff_tags = array_diff($his_arr,$new_arr);
		if ($diff_tags){
			$remove_tag = '';
			foreach($diff_tags as $value){
				if ($remove_tag){
					$remove_tag = ','.$value;
				}else{
					$remove_tag = $value;
				}
			}
		}
		$this -> dekTagmap($dataid,$remove_tag);
		return $result;
	}
	
	public function reserTagnum($tagid,$dataid){
		$tagmap = M('Tagmap')->where("tagid=$tagid and dataid=$dataid")->getfield('id');
		if (!$tagmap){
			$tagdata['tagid'] = $tagid;
			$tagdata['dataid'] = $dataid;
			$addtagmap = M('Tagmap')->add($tagdata);
			if ($addtagmap){
				M('Tags')->where("id = $tagid")->setInc('countnum');
			}
		}
	}
	
	public function dekTagmap($dataid,$deltags){
		if (!$deltags){
			return false;
		}
		$arr_del = explode(',',$deltags);
		foreach ($arr_del as $value){
			$delid = intval($value);
			if ($delid){
				M('Tagmap')->where('dataid='.$dataid.' and tagid='.$delid)->delete();
				//重新计数
				$count = M('Tagmap')->where('tagid='.$delid)->count('id');
				if ($count > 0){
					$save['countnum'] = $count;
					M('Tags')->where("id = $delid")->save($save);
				}else{
					M('Tags')->where("id = $delid")->delete();
				}				
			}
		}
	}
}