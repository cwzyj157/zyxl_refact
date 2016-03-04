<?php
namespace Adnims\Controller;
use Think\Controller;
class MenuController extends CommonController{	
    public function index(){
		$pid  = I('get.pid',0);
		if($pid){
            $data = M('Menu')->where("id={$pid}")->field(true)->find();
            $this->assign('data',$data);		
        }	
		$sokey = trim(I('get.sokey'));
		$map  = array();
		$map['pid'] = $pid ;
		if ($sokey){
			$map['title'] = array('like',"%{$sokey}%");
		}
		$all_menu  = M('Menu')->getField('id,pid,title');
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Menu',$map,' hide asc,sort,id','*',C('LIST_ROWS'));
		$list = $result['lists'];
		int_to_string($list,array('hide'=>array(1=>'<font color="#ff0000">是</font>',0=>'否')));
		if($list){
            foreach($list as &$key){
                if($key['pid']){
                    $key['up_title'] = $all_menu[$key['pid']]['title'];
					$key['up_pid'] = $all_menu[$key['pid']]['pid'];
                }
            }
        }
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('list',$list);
		$this->assign('meta_title','菜单管理');
		$this->assign('pid',$pid);
		
		$this->display('index');
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
					$menuData = M('Menu');
					if ($select_id){
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
			$menuData = D('Menu');
            $data = $menuData->create();
            if($data){
                if($menuData->save()!== false){
                    $this->success('菜单信息更新成功!',back_url);
                } else {
                    $this->error('菜单信息更新失败，请返回重新选择!');
                }
            } else {
                $this->error($menuData->getError());
            }
        }else {
            /* 获取数据 */
			$infoid   = I('get.id',0);
			$menuData = M('Menu');
            $info = $menuData->field(true)->find($infoid);
            if($info == false){
                $this->error('菜单信息获取失败,请返回重新选择!');
            }
			$this->assign('info', $info);
			//栏目树
			$menus = $menuData->field(true)->select();
            $menus = D('Tree')->toFormatTree($menus);			
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')),$menus);
            $this->assign('Menus', $menus);

			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','修改 ['.$info['title'].'] 菜单信息');
            $this->display('edit');
        }
    }
	/*添加*/
	public function add(){
		if(IS_POST){
			$menuData = D('Menu');	
			$data = $menuData->create();
			if($data){
                $id = $menuData->add();
                if($id){
                    $this->success('菜单添加成功!',back_url);
                } else {
                    $this->error('添加菜单失败，请返回重新填写表单!');
                }
            } else {
                $this->error($Menu->getError());
            }			
		}else{
			$pid  = I('pid',0);
			$menuData = M('Menu');
			if ($pid){
				$data = $menuData->where("id={$pid}")->field('pid,title')->find();
            	$this->assign('data',$data);
			}
			$where['pid'] = $pid;
			$sort = $menuData->where($where)->max(sort);
			if ($sort){
				$sort = $sort + 1;
			}else{
				$sort = 1;
			}
			$this->assign('info',array('pid'=>I('pid',0),'sort'=>$sort));			
			//加载菜单树
			$menus = $menuData->field(true)->select();
			$menus = D('Tree')->toFormatTree($menus);
			$menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
			$this->assign('Menus', $menus);	
			
			$page_tip = array('page_name'=>'添加菜单','btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','新增菜单');
			$this->display('edit');		
		}
	}
	
	/*删除*/
	public function del(){
		$menuData = M('Menu');
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
		$childMenu = $menuData->where('pid='.$id)->count();
		if ($childMenu > 0){
			$this->error('该菜单下有子菜单，请先删除或移动子菜单后再进行删除!',back_url,3);
		}else{
			if($menuData->where('id='.$id)->delete()){
				$this->success('所选数据删除成功!',back_url);
			} else {
				$this->error('数据删除失败，请重试!',back_url,3);
			}
		}
	}	
	
}