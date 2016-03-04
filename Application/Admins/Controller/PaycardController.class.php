<?php
namespace Adnims\Controller;
use Think\Controller;
class PaycardController extends CommonController{
    public function index(){
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		//判断是否指定模型或栏目
		$type = intval(I('get.type',999));
		$sokey = trim(I('get.sokey',0));
		$map  = array();
		if ($type < 20){
			$map['type'] = $type;
		}
		if ($sokey){
			$map['cardnum'] = $sokey;
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Paycard',$map,' status asc,id desc','*',C('LIST_ROWS'));
		$list = $result['lists'];
		if($list){
            foreach($list as &$key){				
				if ($key['overtime'] < time() && $key['status'] < 2){				
					$saveData = array('id'=>$key['id'],'status'=>3);
					$up = M('Paycard')->save($saveData);
					if ($up !== false){
						$key['status'] = 3;
					}
				}
				$key['status_text'] = D('Paycard') -> getCardstatus($key['status'],0);
            }
        }
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'cardType'=>C('PAYCARD_TYPE'),'type'=>$type,'_total'=>$result['total']));
		$this->assign('meta_title','充值卡管理');
		$this->display('index');
    }
	
	public function add(){
		if(IS_POST){
			$type = intval(I('post.type',0));
			$totalnum = intval(I('post.totalnum',0));
			$limitday = intval(I('post.limitday',0));
			if (totalnum > 98){
				$this->error('每次最多生成99张充值卡!');
			}
			if ($limitday < 30){
				$this->error('有效期请至少设置30天!');
			}
			$result = D('Paycard')->addPaycard($type,$totalnum,$limitday);
			if ($result){
				$this->success('充值卡生成成功!',back_url,3);
			}else{
				$this->error('充值卡生成失败，请重试!');
			}			
		}else{
			$assign = array(
				'PAYCARD_TYPE' => C('PAYCARD_TYPE'),
				'meta_title' => '生成充值卡',
			);
			$this->assign($assign);
			$this->display('add');
		}
	}
	
	public function resort(){
		$action   = trim(I('post.action'));
		if ($action =='resort'){
			$check_id = I('Post.check_id');
			if (!$check_id){
				$this->error("请选择需要上架的充值卡!",back_url,3);
			}else{
				$txtCode = '';
				foreach ($check_id as $k=>$v){
					$menuData = M('Paycard');
					$info = $menuData->field('status,cardnum,cardpwd,money')->find($v);
					
					if ($info['status'] == 0){
						$data['status'] = 1;
						$rs = $menuData->where('id='.$v)->save($data);
						if ($rs){
							$txtCode .= "卡号：".$info['cardnum']." ---- 卡密：".$info['cardpwd']." ---- 面值：".$info['money']."\r\n";
						}					
					}					
				}
				$txtPath = './Upload/card/'.Date('YmdHis').'.txt';
				D('Xhttp')->make_file($txtPath,$txtCode);				
				$this->success("已经成功设为‘已上架’，<a href='".$txtPath."' target='_bank'>请下载本次生成的充值卡</a>",back_url,60);
			}
			
		}else{
			$this->error("您没有用选择任何操作选项，请返回重新选择后再提交!",back_url,3);
		}		
	}
	
	public function del(){
		$mData = M('Paycard');
		$ids = intval(I('get.id',0));
		if (!$ids){
			$this->error('请选择需要删除的数据!',back_url);
		}
		$info = $mData->field('status,id')->find($ids);
		if ($info['status'] !=0){
			$this->error('该充值卡已经上架，不能删除!',back_url);
		}
		if($mData->where('id='.$ids)->delete()){
			$this->success('所选充值卡删除成功!',back_url);
		} else {
			$this->error('充值卡删除失败，请重试!',back_url,3);
		}
	}
	
}