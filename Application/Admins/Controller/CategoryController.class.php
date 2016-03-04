<?php
namespace Adnims\Controller;
use Think\Controller;
class CategoryController extends CommonController{	
    public function index(){
		$tree = D('Category')->getTree(0,'id,name,title,sort,pid,allow_publish,status,rootid,childid,model');
        $this->assign('tree', $tree);
		C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取分类树模板
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('meta_title','栏目设置');
		$this->assign('pid',I('get.pid',0));
		$this->display('index');
    }
	//栏目树
	public function tree_s($trees){
		$root_c = '<span class="root_'.$trees['rootid'].'">'.$trees['rootid'].'级</span>';		
		$html = $root_c.'<input type="text" id="" name="" value="'.$trees['title'].'" readonly>';
		if ($trees['rootid'] < C('CATEGORY_MAX_ROOTID')){
			$html .= '<a href="'.U('Category/add?pid='.$trees['id']).'" title="添加 '.$trees['title'].' 子栏目">添加子栏目</a>';
		}
		return $html;
	}
	
	public function tree_f($trees){
		$arr_child = explode(',',$trees['childid']);
		if (count($arr_child) > 1 ){
			return '<i class="tree_open">折叠</i>';
		}else{
			return '<i class="tree_close">&nbsp;</i>';
		}		
	}
	
	/**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     */
    public function tree($tree = null){
        C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }
	
	/*整页批量排序*/
	public function resort(){
		$action   = trim(I('post.action'));
		$pid      = intval(I('post.pid'));
		if ($action =='resort'){
			//批量修改当前页面排序
			foreach ($_POST as $k=>$v){
				if (substr($k,0,5)=='sort_'){
					$select_id = substr($k,5);
					$order_id = $v;
					$menuData = M('Category');
					if ($select_id && $order_id){
						$data['sort'] = $order_id;
						$rs = $menuData->where('id='.$select_id)->save($data);
					}
				}
			}	
			$this->success("本页排序批量更新成功!",back_url,3);
		}else{
			$this->error("您没有用选择任何操作选项，请返回重新选择后再提交!",back_url,3);
		}		
	}
	/*修改*/
	public function edit($id=0){		
		$page_tip = array('page_name'=>'修改菜单','btn_name'=>'保存修改');
		if(IS_POST){
			$pid = I('post.pid',0);
			$id = I('post.id',0);
			if ($pid == $id){
				$this->success('栏目的上级父栏目不能是自身!',back_url);
			}
			// if ($this->checkRepeat($_POST['title'],$pid,$id,0)){
				// $this->error('栏目名称已经存在,请更换!',back_url,3);
			// }
			// if ($this->checkRepeat($_POST['name'],$pid,$id,1)){
				// $this->error('栏目英文标识已经存在,请更换!',back_url,3);
			// }
			$oldInfo = M('Category')->where('id='.$id)->field('pid,rootid,childid')->find();
			if ($oldInfo['pid'] == $pid){
				$isChange = false;
			}else{
				$isChange = true;
			}
			if ($isChange){  //判断当前移动之后roorid是否会超过限制
				$new_p_root = M('Category')->where('id='.$pid)->getfield('rootid');
				$map['id'] = array('in',$oldInfo['childid']);
				$max_root = M('Category')->where($map)->max('rootid');
				if ( ($max_root - $oldInfo['rootid'] + $new_p_root + 1) > C('CATEGORY_MAX_ROOTID')){
					$this->error('当前移动方案会导致栏目/子栏目的深度超过系统上限(['.C('CATEGORY_MAX_ROOTID').'])层,操作失败”!',back_url,3);
				}
			}
			$classData = D('Category');
            $data = $classData->create();
            if($data){
                if($classData->save()!== false){
					if ($isChange){
						D('Category')->format_tree();
					}
                    $this->success('栏目信息更新成功!',back_url);
                } else {
                    $this->error('栏目信息更新失败，请返回重新选择!');
                }
            } else {
                $this->error($classData->getError());
            }
        }else {
            /* 获取数据 */
			$infoid   = I('get.id',0);
			$classData = M('Category');
            $info = $classData->field(true)->find($infoid);
            if($info == false){
                $this->error('菜单信息获取失败,请返回重新选择!');
            }
			$this->assign('info', $info);
			//加载栏目树
			$Trees = $classData->field(true)->select();
			$Trees = D('Tree')->toFormatTree($Trees);
			$Trees = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级栏目')), $Trees);
			$this->assign('Trees', $Trees);
			//加载内容模块设置
			$Model = M(Model)->field(true)->where('status=1')->select();
			$this->assign('Models', $Model);
			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','修改 ['.$info['title'].'] 菜单信息');
            $this->display('edit');
        }
    }
	/*添加*/
	public function add(){
		if(IS_POST){
			$classData = D('Category');
			//根据父栏目Rootid获取新栏目Rootid 并判断是否允许继续添加子栏目 CATEGORY_MAX_ROOTID
			$pid = I('pid',0);
			$old = $classData->where('id='.$pid)->field('rootid,parentpath')->find();
			if (!$old){
				$new_rootid = 1;
				$parentpath = '0';
			}else{
				$new_rootid = $old['rootid'] + 1;
				$parentpath = $old['parentpath'].','.$pid;
			}
			// if ($this->checkRepeat($_POST['title'],$pid,0,0)){
				// $this->error('栏目名称已经存在,请更换!',back_url,3);
			// }
			// if ($this->checkRepeat($_POST['name'],$pid,0,1)){
				// $this->error('栏目英文标识已经存在,请更换!',back_url,3);
			// }
			if ($new_rootid > C('CATEGORY_MAX_ROOTID')){
				$this->error('当前栏目深度超过系统上限,请在参数设置中重新设定“栏目深度上限”!',back_url,3);
			}
			$data = $classData->create();
			if($data){
                $id = $classData->add();
                if($id){
					//更新当前栏目深度 rootid
					$up_data['rootid'] = $new_rootid;
					$up_data['parentpath'] = $parentpath;
					$up_data['childid'] = $id;
					$c_save = $classData->where('id='.$id)->save($up_data);
					//重置所有栏目子栏目ID集
					$map['id'] = array('in',$parentpath);
					$allClass = $classData->where($map)->field('id,childid')->select();
					if ($allClass){
						foreach ($allClass as $key){
							$new_childid = $key['childid'].','.$id;
							$upm_data['childid'] = $new_childid;							
							$c_save = $classData->where('id='.$key['id'])->save($upm_data);
						}
					}					
					//重置成功
                    $this->success('栏目添加成功!',back_url);
                } else {
                    $this->error('添加栏目失败，请返回重新填写表单!');
                }
            } else {
                $this->error($classData->getError());
            }			
		}else{
			$pid  = I('pid',0);
			$classData = M('Category');
			if ($pid){
				$data = $classData->where("id={$pid}")->field('pid,title,model,template_index,template_lists,template_detail')->find();
            	$this->assign('data',$data);
			}
			$where['pid'] = $pid;
			$sort = $classData->where($where)->max(sort);
			if ($sort){
				$sort = $sort + 1;
			}else{
				$sort = 1;
			}
			$this->assign('info',array('pid'=>$pid,'sort'=>$sort));
			//加载栏目树
			$Trees = $classData->field(true)->select();
			$Trees = D('Tree')->toFormatTree($Trees);
			$Trees = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级栏目')), $Trees);
			$this->assign('Trees', $Trees);
			//加载内容模块设置
			$Model = M(Model)->field(true)->where('status=1')->select();
			$this->assign('Models', $Model);
			$page_tip = array('page_name'=>'添加栏目','btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','添加栏目');
			$this->display('edit');
		}
	}
	
	/*删除*/
	public function del(){
		$classData = M('Category');
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}
		/* 批量删除时候使用  in 查询操作
		$id = array_unique((array)I('id',0));  //排除相项
		if (empty($id)) {
            $this->error('请选择需要删除的数据!',back_url);
        }
		$map = array('id' => array('in', $id) );
		*/
		
		//判断是否存在子菜单  若存在则禁止删除
		$childMenu = $classData->where('pid='.$id)->count();
		if ($childMenu > 0){
			$this->error('该栏目下有子栏目，请先删除或移动子栏目后再进行删除!',back_url,3);
		}
		$datainfo = M('Data')->where('classid='.$id)->count();
		if ($datainfo > 0){
			$this->error('该栏目下有 '.$datainfo.' 篇文档，请先删除或移动文档后再删除栏目!',back_url,3);
		}
		
		if($classData->where('id='.$id)->delete()){
			D('Category')->format_tree();
			$this->success('所选栏目删除成功!',back_url);
		} else {
			$this->error('栏目删除失败，请重试!',back_url,3);
		}
	}
	
	/*检测栏目名称和目录*/
	public function checkRepeat($checkname,$pid=0,$id=0,$checkinfo=0){
		$map = array();
		if ($checkinfo == 0){
			$map['title'] = $checkname;
		}else{
			$map['name'] = $checkname;
		}
		$map['pid'] = $pid;
		if ($id > 0){
			$map['id']  = array('neq',$id);
		}
		$result = M('Category')->where($map)->getfield('id');
		if ($result){
			return true;
		}else{
			return false;
		}		
	}
}