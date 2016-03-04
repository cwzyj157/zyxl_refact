<?php
namespace Adnims\Controller;
use Think\Controller;
class AdvertisingController extends CommonController{
    public function index(){		
		//数据列表
		$adType = array('0'=>'图片广告位','1'=>'Flash广告位','2'=>'Html代码','3'=>'文字广告位');
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Advertising','','id desc','*',C('LIST_ROWS'));
		$list = $result['lists'];
		foreach ($list as $key=>$value){
			$list[$key]['status_text'] = $adType[$value['status']];
		}
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		$this->assign('meta_title','广告位管理');
		$this->assign('type',$type);
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('Advertising/index');
    }
	
	public function detail(){
		if(IS_POST){
			$id = I('post.id',0);
			$mData = D('Advertising');
            $data = $mData->create();
            if($data){
				$mData->update_time = time();
				if($id){
					$res = $mData->save();
					if($res !== false){
						$this->success('广告位信息修改成功!',back_url);
					}else{
						$this->error('广告位信息修改失败，请重试!');
					}
				}else{
					$ids = $mData->add();
					if($ids > 0){
						$this->success('广告位信息添加成功!',back_url);
					}else{
						$this->error('广告位信息添加失败，请重试!');
					}
				}
            } else {
                $this->error('数据获取失败，请重试!');
            }
        }else{
			$id    = intval(I('get.id',0));			
			$info  = M('Advertising')->where('id='.$id)->find();
			$assign = array(
				'meta_title' => '编辑广告计划',
				'info'       => $info,
				'UploadPic_admin' =>getUploadHtml('advertising','上传广告素材','source'),
			);
			$this->assign($assign);
            $this->display('Advertising/detail');
        }
		
	}
	
	/*删除*/
	public function delete(){
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}
		$del_data = M('Advertising') ->where('id='.$id)->delete();
		if($del_data){		
			$this->success('所选数据删除成功!',back_url);
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
		}
	}
	
}