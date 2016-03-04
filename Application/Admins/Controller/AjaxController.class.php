<?php
namespace Adnims\Controller;
use Think\Controller;
class AjaxController extends CommonController{
	public function loadgame(){
		$result = array();
		$sokey = I('get.sokey');
		$gametype = intval(I('get.gametype'));
		if ($gametype ==0){
			$this->ajaxReturn($result);
			exit;
		}
		if (empty($sokey)){
			$this->ajaxReturn($result);
			exit;
		}		
		$map['title'] = array('like',"%{$sokey}%");
		switch ($gametype){
			case 1:
				$gamelist = M('Game')->where($map)->field('id,title')->limit(20)->select();
				break;
			case 2:
				$gamelist = M('Mobilegame')->where($map)->field('id,title')->limit(20)->select();
				break;
			case 3:
				$map['cateid'] = 27;
				$gamelist = M('Data')->where($map)->field('id,title')->limit(20)->select();
				break;
		}
		if ($gamelist){
			$TempCode = "0$$=从搜素结果中选择=";
			foreach($gamelist as $key=>$value){
				$result[$key]['id'] = $value['id'];
				$result[$key]['title'] = $value['title'];
			}
		}
		$this->ajaxReturn($result);	
	}
	
	//载入相关文档选择
    public function loaddata(){
		$dataid = I('dataid',0);
		$cateid = I('cateid',0);
		$sokey = I('sokey','');
		$page = I('page',1);
		$dataModel = M('Data');
		$returnhtml = '<ul>';
		$map['status'] = 1;
		if ($dataid){
			$map['id'] = array('neq',$dataid);
		}
		if ($cateid){
			$map['cateid'] = $cateid;
		}
		if ($sokey){
			$map['title']  = array('like', '%'.$sokey.'%');
		}
		$count = $dataModel->where($map)->count();
		//翻页计算开始
		$showsize = 5; //每页显示条数
		$tolpage = intval($count / $showsize);
		$lever = intval($count % $showsize);
		if ($lever != 0){
			$tolpage = $tolpage + 1;
		}
		if ($page > $tolpage){
			$page = $tolpage;
		}
		$frinum = ($page - 1) * $showsize;
		if ($frinum < $count ){			
		}else{
			$frinum = $count - 1;
		}
		$lastpage = $page-1;
		$nextpage = $page+1;
		if ( $lastpage < 1){ $lastpage = 1;}
		if ( $nextpage > $tolpage){ $nextpage = $tolpage;}
		//翻页计算结束
		$info = $dataModel->where($map)->order('id desc')->field('id,title,cateid')->limit($frinum,5)->select();
		if ($info){
			//查询是否已经勾选
			if ($dataid){
				$morenews = $dataModel->where("id=$dataid")->getfield('relates');
				$arrayids = explode(',',$morenews);
			}
			for( $i=0;$i<count($info);$i++){
				if ($arrayids){	
					if (in_array($info[$i]['id'],$arrayids)){
						$ischecked = 'checked';
					}else{
						$ischecked = '';
					}
				}
				$catename = getCatename($info[$i]['cateid']);
				$returnhtml .= '<li><span><input type="checkbox" name="chk_id" '.$ischecked.' onclick=choseid(this) value="'.$info[$i]['id'].'###'.$catename.'###'.$info[$i]['title'].'" class="add_news"/></span><font color="#008000">['.$catename.']</font>&nbsp;'.$info[$i]['title'].'</li>';
			}
			$returnhtml .= '</ul>';
			$returnhtml .= "<div class='pageshow'><span onclick=load_news($infoid,$cateid,'$sokey',$lastpage)>上一页</span><span onclick=load_news($infoid,$cateid,'$sokey',$nextpage)>下一页</span>&nbsp;&nbsp;&nbsp;&nbsp;共 ".$count." 篇图文&nbsp;&nbsp;$page/".$tolpage."页</div>";
		}else{
			$returnhtml .= '<li class="no">暂无相关文档</li>';
			$returnhtml .= '</ul>';
		}
		echo $returnhtml;	
    }
	
		//载入相关文档选择
    public function loadnews(){
		$dataid = I('infoid',0);
		$cateid = I('cateid',0);
		$sokey = I('sokey','');
		$page = I('page',1);
		$ispush = I('ispush',1); //是否为素材
		$dataModel = M('Wapdata');		
		$returnhtml = '<ul>';
		$map = array();
		$map['pushtype'] = 1;
		$map['issucai'] = $ispush;		
		if ($dataid){
			$map['id'] = array('neq',$dataid);
		}
		if ($cateid){
			$map['cateid'] = $cateid;
		}
		if ($sokey){
			$map['title']  = array('like', '%'.$sokey.'%');
		}
		$count = $dataModel->where($map)->count();
		//翻页计算开始
		$showsize = 5; //每页显示条数
		$tolpage = intval($count / $showsize);
		$lever = intval($count % $showsize);
		if ($lever != 0){
			$tolpage = $tolpage + 1;
		}
		if ($page > $tolpage){
			$page = $tolpage;
		}
		$frinum = ($page - 1) * $showsize;
		if ($frinum < $count ){			
		}else{
			$frinum = $count - 1;
		}
		$lastpage = $page-1;
		$nextpage = $page+1;
		if ( $lastpage < 1){ $lastpage = 1;}
		if ( $nextpage > $tolpage){ $nextpage = $tolpage;}
		//翻页计算结束
		$info = $dataModel->where($map)->order('id desc')->field('id,title,cateid')->limit($frinum,5)->select();
		if ($info){
			//查询是否已经勾选
			if ($dataid){
				$morenews = $dataModel->where("id=$dataid")->getfield('relates');
				$arrayids = explode(',',$morenews);
			}
			for( $i=0;$i<count($info);$i++){
				if ($arrayids){	
					if (in_array($info[$i]['id'],$arrayids)){
						$ischecked = 'checked';
					}else{
						$ischecked = '';
					}
				}
				$catename = D('Wap')->getCatename($info[$i]['cateid']);
				$returnhtml .= '<li><span><input type="checkbox" name="chk_id" '.$ischecked.' onclick=choseid(this) value="'.$info[$i]['id'].'###'.$catename.'###'.$info[$i]['title'].'" class="add_news"/></span><font color="#008000">['.$catename.']</font>&nbsp;'.$info[$i]['title'].'</li>';
			}
			$returnhtml .= '</ul>';
			$returnhtml .= "<div class='pageshow'><span onclick=load_news($dataid,$cateid,'$sokey',$lastpage,$ispush)>上一页</span><span onclick=load_news($dataid,$cateid,'$sokey',$nextpage,$ispush)>下一页</span>&nbsp;&nbsp;&nbsp;&nbsp;共 ".$count." 篇图文&nbsp;&nbsp;$page/".$tolpage."页</div>";
		}else{
			$returnhtml .= '<li class="no">暂无相关文档</li>';
			$returnhtml .= '</ul>';
		}
		echo $returnhtml;	
    }
	
	
	
	
	
}