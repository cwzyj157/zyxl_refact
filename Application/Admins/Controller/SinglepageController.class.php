<?php
namespace Adnims\Controller;
use Think\Controller;
class SinglepageController extends CommonController{
	protected function _initialize(){
		parent :: _initialize();
		//左侧模块分类导航
		// $this->assign('__DATA_MENU__',true);
	}
    public function index(){
		$status = intval(I('get.status',0));
		$where['status'] = $status;
		//数据列表
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Singlepage',$where,'sort asc,id asc','*',C('LIST_ROWS'));
		$list   = $result['lists'];
		$assign = array(
			'meta_title' => '单页面管理',
			'list' => $list,
			'status' => $status,
			'_page' => $result['page'],
			'TYPE_CONFIG' => C('SINGLEPAGE_TYPE_CONFIG'),
		);
		$this->assign($assign);		
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('index');
    }
	
	public function detail(){		
		if(IS_POST){
			$id = I('post.id',0);
			$mData = D('Singlepage');
            $data = $mData->create();			
            if($data){
				$mData->upload_time = time();
				if($id){
					$res = $mData->save();
					if($res !== false){
						$this->success('单页面内容修改成功!',back_url);
					}else{
						$this->error('单页面内容修改失败，请重试!');
					}
				}else{
					$ids = $mData->add();
					if($ids > 0){
						$this->success('单页面内容添加成功!',back_url);
					}else{
						$this->error('单页面内容添加失败，请重试!');
					}
				}
            } else {
                $this->error('数据获取失败，请重试!');
            }
        }else{			
			$id     = intval(I('get.id',0));
			$status = intval(I('get.status',0));
			$info   = M('Singlepage')->where('id='.$id)->find();
			$content = ($info) ? $info['content'] : '';
			if($info){
				$status = $info['status'];
			}
			$assign = array(
				'meta_title'      => '编辑单页面内容',
				'content_editor'  => getContentEditor('content',$content),
				'TYPE_CONFIG'     => C('SINGLEPAGE_TYPE_CONFIG'),
				'status'          => $status,
				'UploadPic_admin' => getUploadHtml('thumpic/'.date('Ym'),'上传图片','pic'),
				'info'            => $info,
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
					$menuData = M('Singlepage');
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
		$del_data = M('Singlepage') ->where('id='.$id)->delete();
		if($del_data){		
			$this->success('所选数据删除成功!',back_url);
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
		}
	}
	
}