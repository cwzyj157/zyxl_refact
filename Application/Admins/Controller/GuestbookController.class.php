<?php
namespace Adnims\Controller;
use Think\Controller;
class GuestbookController extends CommonController{
	public $Type_Config;
	protected function _initialize(){		
		parent :: _initialize();
		$this->Type_Config = array(1=>'留言本',2=>'申请表单');
		$this->assign('Type_Config',$this->Type_Config);
	}
    public function index(){
		$gtype = intval(I('get.type'));
		$ordertype = ($gtype==2) ? 2 : 1;
		$InfosPage = new \OT\Pages;
		$map['ordertype'] = $ordertype;
		$result = $InfosPage->getLists('Guestbook',$map,'status asc,id desc','*',C('LIST_ROWS'));
		$list = $result['lists'];
		foreach($list as $key=>$value){
			$list[$key]['info'] = json_decode($value['guestcontent'],true);
			$list[$key]['status_text'] = ($value['status'] ==1) ? '<font color="#008000">已读</font>' : '<font color="#ff0000">未读</font>';
		}
		$assign = array(
			'list'    => $list,
			'ordertype' => $ordertype,
			'_page'   => $result['page'],
			'_total'  => $result['total'],
			'meta_title' => '留言本',
		);
		$this->assign($assign);
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('index');
    }
	/*修改*/
	public function edit($id=0){
		if(IS_POST){
			$menuData = D('Guestbook');
            $data = $menuData->create();
			
            if($data){
				$dataInfo->update_time = time();
                if($menuData->save()!== false){
                    $this->success('预约回复成功!',back_url);
                } else {
                    $this->error($t_name.'预约回复失败，请返回重新选择!');
                }
            } else {
                $this->error($menuData->getError());
            }
        }else {
            /* 获取数据 */
			$infoid   = I('get.id',0);
			$menuData = M('Guestbook');
			$data['status'] = 1;
			$map['id'] = $infoid;
			$id = $menuData->where($map)->save($data);
            $info = $menuData->field(true)->find($infoid);
            if($info == false){
                $this->error('留言信息获取失败,请返回重新选择!');
            }
			$info['infos'] = json_decode($info['guestcontent'],true);
			$assign = array(
				'info'    => $info,
				'meta_title' => '留言本',
			);
			$this->assign($assign);
		
			$this->assign('info', $info);
			$this->assign('meta_title','留言本');
            $this->display('edit');
        }
    }
	/*删除*/
	public function del(){
		$menuData = M('Guestbook');
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