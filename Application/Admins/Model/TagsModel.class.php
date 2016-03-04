<?php
namespace Admins\Model;
use Think\Model;

class TagsModel extends Model{
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
				// $map['modelid'] = $modelid;
				$tagid = M('Tags')->where($map)->getfield('id');
				if ($tagid){
					$addtagsid = $tagid;
				}else{
					$sort = M('Tags')->max(sort);
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
	
	public function resetTags($ids){
		if(!$ids){
			return false;
		}
		$dataids = explode(',',$ids);
		foreach($dataids as $key=>$value){
			$did = intval($value);
			$tagmaps = M('Tagmap')->where('dataid='.$did)->select();
			$tag_v = '';
			foreach($tagmaps as $k=>$v){
				if($tag_v){
					$tag_v .= ','.$v['tagid'];
				}else{
					$tag_v = $v['tagid'];
				}
			}
			$savedata['tags'] = $tag_v;
			$result = M('Data')->where("id=$did")->save($savedata);
		}
	}
	
	
}