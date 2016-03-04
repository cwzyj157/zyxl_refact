<?php
namespace Adnims\Controller;
use Think\Controller;
class DataController extends CommonController{
	public $cateid,$cModels;	
	protected function _initialize(){
		parent :: _initialize();
		//左侧模块分类导航
		$this->assign('__DATA_MENU__',true);
		//加载栏目树	
		$map = array();
		/* 权限限制栏目 */
		if(UID != C('USER_ADMINISTRATOR')){
			$authority = M('Adminer')->where('id='.UID)->getfield('authority');
			if($authority != 'all'){
				$map['id'] = array('in',$authority);
			}
		}
		/* 权限限制栏目 */
		$Trees = M('Category')->field(true)->where($map)->order('sort asc')->select();
		$Trees = D('Tree')->toFormatTree($Trees);
		$this->assign('classMap',$Trees);
		$this->assign('VodType',C('VodType'));
		//参数
		$model = intval(I('get.model',0));
		$cateid = intval(I('get.cateid',0));
		if ($cateid){
			$data = M('Category')->where('id='.$cateid)->field('id,title,model,pid,topshow')->find();
			if (!$data){
				$this->error('系统参数传递错误，请返回重新选择!',U('Data/index'),3);
			}else{
				$this->cateid = $data['id'];
				$model = $data['model'];		
				$this->assign('data',$data);
				$this->assign('topshow',$data['topshow']);
				$this->assign('pid',$data['pid']);
				$this->assign('cateid',$data['id']);	
			}
		}
		if ($model){
			$m_Model = M('Model')->field(true)->where('id='.$model)->find();
			if (!$m_Model){
				$this->error('栏目模型参数错误，请返回重新选择!',U('Data/index'),3);
			}else{
				$this->cModels = $m_Model;
				$this->assign('Models',$m_Model);			
			}
		}
	}
	
    public function index(){
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		//判断是否指定模型或栏目
		$sokey  = trim(I('get.sokey'));
		$model  = $this->Models['id'];
		$cateid = $this->cateid;
		$map    = array();
		if ($model){
			$map['modelid'] = $model;
		}
		if ($cateid){
			$childid = M('Category')->where('id='.$cateid)->field('childid,pid')->find();
			$map['cateid'] = array("in",($childid['childid']));
			$pid = $childid['pid'];
		}else{
			$map['cateid'] = array('neq',12);
		}
		if ($sokey){
			$map['title'] = array('like',"%{$sokey}%");
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Data',$map,' is_hot desc,create_time desc','*',C('LIST_ROWS'));
		$list = $result['lists'];		
		if($list){
            foreach($list as &$key){
				$key['cate_name'] = getCatename($key['cateid'],0);
            }
        }
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		$this->assign('meta_title','内容管理');
		$this->assign('pid',$pid);
		$this->display('index');
    }

	/*添加*/
	public function add(){
		if(IS_POST){		
			//栏目容错
			$cateid  = intval(I('post.cateid',0));
			$data = M('Category')->where('id='.$cateid)->field('id,title,model')->find();
			if (!$data){
				$this->error('栏目ID参数出错，请返回重新选择!',U('Data/index'),3);
			}
			//模型容错
			$map['status'] = 1;
			$map['id'] = $data['model'];
			$mData = M('Model')->where($map)->field('id,smp_name,database,data_edit_temp')->find();
			if (!$mData){
				$this->error('栏目模型参数错误，请返回重新选择!',U('Data/index'),3);
			}
			//当前最新排序
			$sort = M('Data')->where('cateid='.$cateid)->max(sort);
			if ($sort){
				$sort = $sort + 1;
			}else{
				$sort = 1;
			}			
			//入库
			$menuData = D('Data');		
			$a_data = $menuData->create();
			//转为时间戳
			$create_time_text = I('post.create_time_text');
			$create_time=strtotime($create_time_text)?strtotime($create_time_text):false;
			if(!$create_time){
				$create_time = time();
			}
			if($a_data){
				$menuData->adminid = UID;
				$menuData->create_time = $create_time;
				$menuData->sort  = $sort;
				$menuData->label = getLabel($_POST['label']);
                $id = $menuData->add();
                if($id){
					//重置Tags标签
					D('Tags') -> addTags($id,$data['model'],$_POST['tagtext']);
					//添加附加表内容
					$extendData = D($mData['database']);
					$ext_data = $extendData->create();
					if ($ext_data){
						//更新主表关联id
						$extendData->dataid = $id;
						//课程频道
						if ($mData['id'] == 2){
							// $goodsconfig = array();
							// $postData = $_POST;
							// for($g=1;$g<=28;$g++){
								// $goodsconfig['info_a'.$g] = $postData['info_a'.$g];
							// }
							// $extendData->goodsconfig = json_encode($goodsconfig);
						}
						//课程频道			
						$ext_id = $extendData->add();
						if ($ext_id){
							$this->success($mData['smp_name'].'内容【'.I('post.title').'】添加成功!',back_url); 
						}else{
							$this->error('【'.I('post.title').'】扩展内容添加失败,请返回修改该文档内容!'); 
						}						
					}
                } else {
                    $this->error($mData['smp_name'].'内容添加失败，请返回重新填写表单!');
                }
            }else {
                $this->error($menuData->getError());
            }	
		}else{
			$mData  = $this->Models;
			$Cateid  = $this->cateid;
			//初始化表单
			$this->assign('UploadPic_admin',getUploadHtml('thumpic/'.date('Ym'),'上传图片['.D('Data')->thumpicSize($Cateid).']','smallpic')); //制定缩微图保存路径
			if ($mData['id'] != 2){
				$this->assign('content_editor', getContentEditor($mData['database'].'Content',''));
			}
			//Tags
			// $tmap['modelid'] = $mData['id'];
			$tags = M("Tags")->where($tmap)->field('id,tagname')->order('countnum desc,sort desc')->limit(40)->select();
			$this->assign('tags',$tags);
			$this->assign('create_time_text',date('Y-m-d H:i:s'));
			$this->assign('page_tip',array('page_name'=>'添加新'.$mData['smp_name'],'btn_name'=>'确定添加'));
			$this->assign('meta_title','添加新'.$mData['smp_name']);
			$this->display($mData['data_edit_temp']);		
		}
	}	
	/*修改*/
	public function edit($id=0){		
		$page_tip = array('page_name'=>'修改菜单','btn_name'=>'保存修改');
		if(IS_POST){
			//栏目容错
			$dataid  = intval(I('post.id',0));
			$info = M('Data')->where('id='.$dataid)->field('id,cateid,modelid,tags,title')->find();
			if (!$info){
				$this->error('文档信息获取失败，请返回重新选择!',U('Data/index'),3);
			}
			//模型容错
			$map['status'] = 1;
			$map['id'] = $info['modelid'];
			$mData = M('Model')->where($map)->field('id,smp_name,database,data_edit_temp')->find();
			if (!$mData){
				$this->error('栏目模型参数错误，请返回重新选择!',U('Data/index'),3);
			}
			//入库
			//处理自定义属性
			$dataInfo = D('Data');		
			$a_data = $dataInfo->create();
			//转为时间戳
			$create_time_text = I('post.create_time_text');
			$create_time=strtotime($create_time_text)?strtotime($create_time_text):false;
			if(!$create_time){
				$create_time = time();
			}
			if($a_data){
				$dataInfo->adminid = UID;
				$dataInfo->create_time = $create_time;
				$dataInfo->label = getLabel($_POST['label']);
				if($dataInfo->save()!== false){
					//重置Tags标签
					D('Tags') -> addTags($dataid,$info['modelid'],$_POST['tagtext'],$info['tags']);
					//保存扩展表内容
					$extendData = D($mData['database']);
					$ext_data = $extendData->create();
					if ($ext_data){		
						//特殊模型
						if ($mData['id'] == 2){
							// $goodsconfig = array();
							// $postData = $_POST;
							// for($g=1;$g<=28;$g++){
								// $goodsconfig['info_a'.$g] = $postData['info_a'.$g];
							// }
							// $extendData->goodsconfig = json_encode($goodsconfig);
						}
						//特殊模型					
						if($extendData->save()!== false){
							$this->success($mData['smp_name'].'修改成功!',back_url);
						}else{
							$this->error('【'.$info['title'].'】扩展内容更新失败,请返回重试!'); 
						}						
					}else{
						$this->error($extendData->getError());
					}
                } else {
                    $this->error($mData['smp_name'].'修改失败，请返回重新选择!');
                }
			}else{
                $this->error($dataInfo->getError());
            }
        }else {
            /* 获取主表数据 */
			$infoid   = intval(I('get.id',0));
            $info = M('Data')->field(true)->find($infoid);
            if(!$info){
                $this->error('文档信息获取失败,请返回重新选择!');
            }
			$this->assign('info', $info);
			//相关文档
			$map['id'] = array('in',($info['relates']));
			$relates = M('Data')->where($map)->field('id,cateid,title')->limit(10)->select();
			$this->assign('relates',$relates);
			/*载入文档模型*/
			$mData = M('Model')->field(true)->find($info['modelid']);
			if (!$mData){
				$this->error('栏目模型参数错误，请返回重新选择!',U('Data/index'),3);
			}
			$this->assign('Models',$mData);
			/*加载扩展表内容*/
			$extend = M($mData['database'])->field(true)->where('dataid='.$info['id'])->find();
			$this->assign('extend', $extend);			
			$this->assign('UploadPic_admin',getUploadHtml('thumpic/'.date('Ym'),'上传图片['.D('Data')->thumpicSize($info['cateid']).']','smallpic')); //制定缩微图保存路径
			if ($mData['id'] != 4){
			$this->assign('content_editor', getContentEditor($mData['database'].'Content',$extend[$mData['database'].'Content']));
			}
			//特殊模型
			if ($mData['id'] == 4){
				// $gconfig = json_decode($extend['goodsconfig'],true);
				// $this->assign('gconfig',$gconfig);
			}
			//特殊模型
			//Tags
			$tmap['modelid'] = $mData['id'];
			$tags = M("Tags")->where($tmap)->field('id,tagname')->order('countnum desc,sort desc')->limit(40)->select();
			$this->assign('tags',$tags);
			//栏目信息
			$cateinfo = M('Category')->where('id='.$info['cateid'])->field('id,pid,topshow')->find();
			$this->assign('cateid',$cateinfo['id']);
			$this->assign('pid',$cateinfo['pid']);
			$this->assign('topshow',$cateinfo['topshow']);
			$this->assign('create_time_text',date('Y-m-d H:i:s',$info['create_time']));
			$this->assign('page_tip',array('page_name'=>'修改'.$mData['smp_name'],'btn_name'=>'保存修改'));
			$this->assign('meta_title','修改'.$mData['smp_name']);
            $this->display($mData['data_edit_temp']);
        }
    }	
	/*删除*/
	public function del(){
		$menuData = M('Data');
		$id = intval(I('id',0));
		$modelid = $menuData->where('id='.$id)->getfield('modelid');
		if (!$modelid){
			$this->error('请选择需要删除的数据!',back_url);
		}
		$modelType = showmodel($modelid,2);
		$delinfo = $menuData->where('id='.$id)->getfield('tags');	
		if($menuData->where('id='.$id)->delete()){			
			//删除扩展表数据
			M($modelType)->where('dataid='.$id)->delete();
			if($delinfo){
				D('Tags') -> dekTagmap($id,$delinfo['tags']);
			}
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
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
					$menuData = M('Data');
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
	
}