<?php
namespace Adnims\Controller;
use Think\Controller;
class LinksController extends CommonController{
	public $Type_Config;
	public $Status_Con;
	protected function _initialize(){		
		parent :: _initialize();
		$this->Type_Config = array(0 => '切换焦点图',1 => '栏目Banner');
		$this->Status_Con = array(
			0 => array('首页'),
			1 => array('关于我们','公司业务','新闻动态','人才需求','联系我们'),
		);
		$this->assign('Type_Config',$this->Type_Config);
	}
    public function index(){
		$type     	= I('type',0);
		$status   	= intval(I('status',0));
		$t_name   	= $this->Type_Config[$type];
		$flCon   	= $this->Status_Con;
		$Status_Con = $flCon[$type];
		$sokey = trim(I('get.sokey'));
		$map  = array();
		$map['type'] = $type;
		$map['status'] = $status;
		if ($sokey){
			$map['title'] = array('like',"%{$sokey}%");
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Links',$map,'sort asc,id asc','*',C('LIST_ROWS'));
		$list = $result['lists'];
		foreach($list as $key=>$value){
			$list[$key]['status_text'] = $Status_Con[$value['status']];
		}
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		$this->assign('meta_title',$t_name.'管理');
		$this->assign('type',$type);
		$this->assign('status',$status);
		$this->assign('Status_Con',$Status_Con);
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
					$menuData = M('Links');
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
		$type   	= I('type',0);
		$status 	= intval(I('status',0));
		$t_name 	= $this->Type_Config[$type];
		$flCon 		= $this->Status_Con;
		$Status_Con = $flCon[$type];
		if(IS_POST){
			$menuData = D('Links');
            $data = $menuData->create();
            if($data){
                if($menuData->save()!== false){
                    $this->success($t_name.'信息更新成功!',back_url);
                } else {
                    $this->error($t_name.'信息更新失败，请返回重新选择!');
                }
            } else {
                $this->error($menuData->getError());
            }
        }else {
            /* 获取数据 */
			$infoid   = I('get.id',0);
			$menuData = M('Links');
            $info = $menuData->field(true)->find($infoid);
            if($info == false){
                $this->error($t_name.'信息获取失败,请返回重新选择!');
            }
			$this->assign('info', $info);
			$page_tip = array('page_name'=>'修改'.$t_name,'btn_name'=>'保存修改');
			$this->assign('page_tip', $page_tip);
			$this->assign('type', $type);
			$this->assign('status',$status);
			$this->assign('Status_Con',$Status_Con);
			$this->assign('meta_title','修改 ['.$info['title'].'] 菜单信息');
			$this->assign('UploadPic_admin',getUploadHtml('links','上传图片[1920x450]','pic'));
            $this->display('edit');
        }
    }
	/*添加*/
	public function add(){
		$type   	= I('type',0);
		$status 	= intval(I('status',0));
		$t_name 	= $this->Type_Config[$type];
		$flCon 		= $this->Status_Con;
		$Status_Con = $flCon[$type];
		if(IS_POST){
			$LinksDate = D('Links');	
			$data = $LinksDate->create();
			if($data){
                $id = $LinksDate->add();
                if($id){
                    $this->success($t_name.'添加成功!',back_url);
                } else {
                    $this->error($t_name.'添加失败，请返回重新填写!');
                }
            } else {
                $this->error($LinksDate->getError());
            }			
		}else{
			$where['type'] = $type;
			$sort = M('Links')->where($where)->max(sort);
			if ($sort){
				$sort = $sort + 1;
			}else{
				$sort = 1;
			}
			$this->assign('info',array('type'=>I('type',0),'sort'=>$sort));	
			$page_tip = array('page_name'=>'添加'.$t_name,'btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('meta_title','添加'.$t_name);
			$this->assign('Status_Con',$Status_Con);
			$this->assign('status',$status);
			$this->assign('UploadPic_admin',getUploadHtml('links','上传图片[1920x450]','pic'));
			$this->assign('type',$type);
			$this->display('edit');		
		}
	}
	
	/*删除*/
	public function del(){
		$menuData = M('Links');
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
		if($menuData->where('id='.$id)->delete()){
			$this->success('所选数据删除成功!',back_url);
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
		}
	}	
	
}