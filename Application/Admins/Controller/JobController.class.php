<?php
namespace Adnims\Controller;
use Think\Controller;
class JobController extends CommonController{
    public function index(){
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Job','','sort asc,id asc','*',C('LIST_ROWS'));
		$list = $result['lists'];
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		$this->assign('meta_title','招聘职位管理');
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('index');
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
					$menuData = M('Job');
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
		if(IS_POST){
			$menuData = D('Job');
            $data = $menuData->create();			
            if($data){
				$menuData->update_time  = time();
                if($menuData->save()!== false){
                    $this->success('招聘信息更新成功!',back_url);
                } else {
                    $this->error('招聘信息更新失败，请返回重新选择!');
                }
            } else {
                $this->error($menuData->getError());
            }
        }else {
            /* 获取数据 */
			$infoid   = I('get.id',0);
			$menuData = M('Job');
            $info = $menuData->field(true)->find($infoid);
            if($info == false){
                $this->error('招聘信息获取失败,请返回重新选择!');
            }
			$this->assign('info', $info);
			$page_tip = array('page_name'=>'修改'.$t_name,'btn_name'=>'保存修改');
			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','修改招聘信息');
            $this->display('edit');
        }
    }
	/*添加*/
	public function add(){
		if(IS_POST){
			$LinksDate = D('Job');	
			$data = $LinksDate->create();
			if($data){
				$LinksDate->update_time  = time();
                $id = $LinksDate->add();
                if($id){
                    $this->success('招聘信息添加成功!',back_url);
                } else {
                    $this->error('招聘信息添加失败，请返回重新填写!');
                }
            } else {
                $this->error($LinksDate->getError());
            }			
		}else{
			$sort = M('Job')->max(sort);
			if ($sort){
				$sort = $sort + 1;
			}else{
				$sort = 1;
			}
			$this->assign('info',array('sort'=>$sort));	
			$page_tip = array('page_name'=>'添加招聘信息','btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','添加招聘信息');
			$this->display('edit');		
		}
	}
	
	/*删除*/
	public function del(){
		$menuData = M('Job');
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}
		if($menuData->where('id='.$id)->delete()){
			$this->success('所选数据删除成功!',back_url);
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
		}
	}	
	
}