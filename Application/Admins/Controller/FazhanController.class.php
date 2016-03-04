<?php
namespace Adnims\Controller;
use Think\Controller;
class FazhanController extends CommonController{
    public function index(){
		$year = intval(I('get.year',0));
		if($year){
			$where['year'] = $year;
		}
		$Cate = M('Fazhan')->group('year')->order('year desc')->field('year,id')->select();
		//数据列表
		$InfosPage = new \OT\Pages;		
		$result = $InfosPage->getLists('Fazhan',$where,'year desc,month desc,id desc','*',C('LIST_ROWS'));
		$list   = $result['lists'];
		$assign = array(
			'meta_title' => '发展历程管理',
			'list' => $list,
			'year' => $year,
			'_page' => $result['page'],
			'Cate'  => $Cate,
		);
		$this->assign($assign);		
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('index');
    }
	
	public function detail(){		
		if(IS_POST){
			$id = I('post.id',0);
			$mData = D('Fazhan');
            $data = $mData->create();			
            if($data){
				if($id){
					$res = $mData->save();
					if($res !== false){
						$this->success('发展历程内容修改成功!',back_url);
					}else{
						$this->error('发展历程内容修改失败，请重试!');
					}
				}else{
					$ids = $mData->add();
					if($ids > 0){
						$this->success('发展历程内容添加成功!',back_url);
					}else{
						$this->error('发展历程内容添加失败，请重试!');
					}
				}
            } else {
                $this->error('数据获取失败，请重试!');
            }
        }else{			
			$id     = intval(I('get.id',0));
			$status = intval(I('get.year',0));
			$info   = M('Fazhan')->where('id='.$id)->find();
			if($info){
				$status = $info['year'];
			}
			$assign = array(
				'meta_title'   => '编辑发展历程内容',
				'year'         => $year,
				'info'         => $info,
			);
			$this->assign($assign);	
            $this->display('detail');
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
					$menuData = M('Fazhan');
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
	
	/*删除*/
	public function delete(){
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}
		$del_data = M('Fazhan') ->where('id='.$id)->delete();
		if($del_data){		
			$this->success('所选数据删除成功!',back_url);
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
		}
	}
	
}