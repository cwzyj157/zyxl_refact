<?php
namespace Adnims\Controller;
use Think\Controller;
class TagsController extends CommonController{
	protected function _initialize(){
		parent :: _initialize();
		//左侧模块分类导航
		$this->assign('__DATA_MENU__',true);
	}
    public function index(){
		$sokey = trim(I('get.sokey'));
		$map   = array();
		if ($sokey){
			$map['tagname'] = array('like',"%{$sokey}%");
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Tags',$map,'is_show desc,sort asc,countnum desc','*',C('LIST_ROWS'));
		$list = $result['lists'];		
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));		
		$this->assign('meta_title','文章标签');
		$this->assign('type','article');
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('index');
    }
	
	public function pic(){
		$sokey = trim(I('get.sokey'));
		$map   = array();
		$map['modelid'] = 3;
		if ($sokey){
			$map['tagname'] = array('like',"%{$sokey}%");
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Tags',$map,'countnum desc','*',C('LIST_ROWS'));
		$list = $result['lists'];		
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		$this->assign('meta_title','图集标签');
		$this->assign('type','pic');
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('index');
    }
	
	public function lists(){
		$tagid = intval(I('get.tagid',0));
		$tagname = M('Tags')->where('id='.$tagid)->getfield('tagname');
		$map   = array();
		$map['tagid'] = $tagid;
		$prefix = C('DB_PREFIX');
		$InfosPage = new \OT\Pages;
		$join = $prefix.'data ON '.$prefix.'tagmap.dataid = '.$prefix.'data.id';
		$result = $InfosPage->getLists('Tagmap',$map,$prefix.'tagmap.dataid desc',$prefix.'tagmap.id,tagid,dataid,title,create_time',C('LIST_ROWS'),true,$join);
		$list = $result['lists'];
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		$this->assign('meta_title','图集标签');
		$this->assign('type','pic');
		$this->assign('tagid',$tagid);
		$this->assign('tagname',$tagname);
		$this->display('datalist');
    }
	
	/*批量移除标签*/
	public function resort(){
		$action   = trim(I('post.action'));
		if ($action =='resort'){
			//批量修改当前页面排序
			foreach ($_POST as $k=>$v){
				if (substr($k,0,5)=='sort_'){
					$select_id = substr($k,5);
					$order_id = $v;
					$menuData = M('Tags');
					if ($select_id && $order_id){
						$data['sort'] = $order_id;
						$rs = $menuData->where('id='.$select_id)->save($data);
					}
				}
			}	
			$this->success("本页排序批量更新成功!",back_url,3);
		}elseif ($action =='removetag'){
			$check_id = I('post.check_id');
			$tagid    = intval(I('post.tagid'));
			$map['id'] = array('in',$check_id);
			$dataids = M('Tagmap')->where($map)->getfield('dataid',true);
			$res = M('Tagmap')->where($map)->delete();
			if($res){
				//Tags重新计数
				$count = M('Tagmap')->where('tagid='.$tagid)->count('id');
				if ($count > 0){
					$save['countnum'] = $count;
					M('Tags')->where("id = $tagid")->save($save);
				}else{
					M('Tags')->where("id = $tagid")->delete();
				}				
				D('Tags')->resetTags(implode(',',$dataids));
			}
			$this->success("标签内文档移除成功!",back_url,3);
		}else{
			$this->error("您没有用选择任何操作选项，请返回重新选择后再提交!",back_url,3);
		}		
	}
	
	/*修改标签*/
	public function edit($id=0){
		if(IS_POST){
			$menuData = D('Tags');
            $data = $menuData->create();
            if($data){
                if($menuData->save()!== false){
                    $this->success('标签信息更新成功!',back_url);
                } else {
                    $this->error('标签信息更新失败!');
                }
            } else {
                $this->error('标签信息更新失败!');
            }
        }else {
            /* 获取数据 */
			$id   = intval(I('get.id',0));
            $info = M('Tags')->field(true)->where('id='.$id)->find();
            if($info == false){
                $this->error('标签信息获取失败,请返回重新选择!');
            }
			$type = ($info['modelid'] == 2) ? 'article' : 'pic';
			$type_text = ($info['modelid'] == 2) ? '文章' : '图集';
			$this->assign('info', $info);
			$this->assign('type', $type);
			$this->assign('type_text', $type_text);
			$this->assign('meta_title','修改标签信息');
            $this->display('edit');
        }
    }
	
	/*删除标签*/
	public function delete(){
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}		
		if(M('Tags')->where('id='.$id)->delete()){	
			$dataids = M('Tagmap')->where('tagid='.$id)->getfield('dataid',true);
			M('Tagmap')->where('tagid='.$id)->delete();			
			D('Tags')->resetTags(implode(',',$dataids));
			$this->success('所选数据删除成功!',back_url);
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
		}
	}

}