<?php
namespace Adnims\Controller;
use Think\Controller;
class AdmingroupController extends CommonController{
    public function index(){
		$map = array();
		if ($sokey){
			$map['title'] = array('like',"%{$sokey}%");
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('AdminGroup',$map,'islocked,sort,id','*',C('LIST_ROWS'));
		$list = $result['lists'];
		int_to_string($list,array('islocked'=>array(0=>'<font color="#008000">正常</font>',1=>'<font color="#ff0000">已锁定</font>')));
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));	
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('meta_title','管理权限组');
		$this->display('Adminer/group_index');
    }
    /**
     * 添加权限组
     */
	public function add(){
		$adminModel = D('AdminGroup');
		if(IS_POST){
			$data = $adminModel->create();
			if($data){
                $id = $adminModel->add();
                if($id){
                    $this->success('新的管理员权限组添加成功!',back_url);
                } else {
                    $this->error('管理员权限组添加失败，请返回重新填写表单!');
                }
            } else {
                $this->error($Config->getError());
            }			
		}else{
			$sort = M('AdminGroup')->max(sort);
			if ($sort){
				$sort = $sort + 1;
			}else{
				$sort = 1;
			}
			$this->assign('info',array('sort'=>$sort));			
			$page_tip = array('page_name'=>'添加权限组','btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','添加权限组');
			$this->display('Adminer/group_edit');
		}
	}
	
    /**
     * 编辑权限组
     */
    public function edit($id = 0){
        if(IS_POST){
            $adminModel = D('AdminGroup');
            $data = $adminModel->create();
			if($data){
                if($adminModel->save() !== false ){
                    $this->success('权限组信息修改成功', back_url);
                }else{
                    $this->error('权限组信息更新失败');
                }
            } else {
                $this->error($adminModel->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('AdminGroup')->field(true)->find($id);
            if(false === $info){
                $this->error('获取权限组信息出错,请返回重新选择');
            }
            $this->assign('info', $info);
			$page_tip = array('page_name'=>'修改权限组','btn_name'=>'保存修改');
			$this->assign('page_tip', $page_tip);
            $this->meta_title = '修改权限组';
            $this->display('Adminer/group_edit');
        }
    }
	
	/*整页批量排序*/
	public function resort(){
		$action   = trim(I('post.action'));
		if ($action =='resort'){
			//批量修改当前页面排序
			foreach ($_POST as $k=>$v){
				if (substr($k,0,5)=='sort_'){
					$select_id = substr($k,5);
					$order_id = $v;
					$menuData = M('AdminGroup');
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
	
	/*管理员权限设置*/
	public function group(){
		$id = intval(I('id',0));		
		if (IS_POST){
			$title = I('post.title');
			$rules = I('post.uRules');
			$tempRules = Bubble_sort($rules);
			$FmenuIds = implode(',',$tempRules);
			$data['rules'] = $FmenuIds;
			//$data['title'] = $title;
			$res = M('AdminGroup')->where('id='.$id)->save($data);
			if ($res !== false){
				$this->error("管理组权限设置成功!",back_url,3);
			}else{
				$this->error("权限设置失败，请返回重试!",back_url,3);
			}			
		}else{
			$groupp = M('AdminGroup')->where('id='.$id)->field('rules,title,id')->find();
			$this->assign('groupp', $groupp);
			//加载权限树
			$Trees = M('Menu')->field('id,title,pid')->where('pid=0')->select();
			if ($Trees){
				foreach ($Trees as &$key){
					$key['childmenu'] = D('AdminGroup')->getSecondtree($key['id'],$groupp['rules']);				
					$key['checked'] = D('AdminGroup')->check_checked($key['id'],$groupp['rules']);
				}
				$this->assign('rules', $Trees);
			}
			$page_tip = array('page_name'=>'权限设置','btn_name'=>'保存修改');
			$this->assign('page_tip', $page_tip);
			$this->meta_title = '权限设置';
			$this->display('Adminer/group_setting');
		}
	}
	
	/*删除*/
	public function del(){
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}
		//判断是否存在管理账户 若存在则禁止删除
		$childMenu = M('Adminer')->where('groupid='.$id)->count();
		if ($childMenu > 0){
			$this->error('该权限组下有 '.$childMenu.' 个管理帐号，请先删除或移动到其他组之后再删除!',back_url,3);
		}else{
			if(M('AdminGroup')->where('id='.$id)->delete()){
				$this->success('所选数据删除成功!',back_url);
			} else {
				$this->error('数据删除失败，请重试!',back_url,3);
			}
		}
	}	

}