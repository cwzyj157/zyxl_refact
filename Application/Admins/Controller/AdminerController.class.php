<?php
namespace Adnims\Controller;
use Think\Controller;
class AdminerController extends CommonController{
    public function index(){
		$map = array('id != '.C('USER_ADMINISTRATOR'));
		if ($sokey){
			$map['username'] = array('like',"%{$sokey}%");
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Adminer',$map,'islocked,groupid,id','*',C('LIST_ROWS'));
		$list = $result['lists'];
		int_to_string($list,array('islocked'=>array(0=>'',1=>'<font color="#ff0000">  已锁定</font>')));
		if($list){
			foreach($list as &$key){
                if($key['groupid']){
					if ($key['id'] == C('USER_ADMINISTRATOR')){
						$key['user_group'] = '超级管理员';
					}else{
						$key['user_group'] = M('AdminGroup')->where('id='.$key['groupid'])->getfield('title');
					}
                }
            };
        }
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));		
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('meta_title','管理员列表');
		$this->display('index');
    }
    /**
     * 添加管理员
     */
	public function add(){
		$adminModel = D('Adminer');
		if(IS_POST){
			//用户权限
			$uRules = I('post.uRules');
			$authority_type = intval(I('post.authority_type',0));			
			if($authority_type){
				$authority = 'all';
			}else{
				$authority = ($uRules) ? implode(',',$uRules) : '';
			}
			
			$data = $adminModel->create();
			if($data){
				$adminModel -> password = md5($_POST['password']);
				$adminModel -> authority = $authority;
                $id = $adminModel->add();
                if($id){
                    $this->success('新的管理员帐号添加成功!',back_url);
                } else {
                    $this->error('管理员帐号添加失败，请返回重新填写表单!');
                }
            } else {
                $this->error($adminModel->getError());
            }			
		}else{
			// 栏目结构
			$bigCate = M('Category')->where('pid=0')->field('id,title')->select();		
			foreach($bigCate as $key=>$value){
				$minCate = M('Category')->where('pid='.$value['id'])->field('id,title')->select();
				foreach($minCate as $k=>$v){
					$mminCate = M('Category')->where('pid='.$v['id'])->field('id,title')->select();
					foreach($mminCate as $mk=>$mv){
						$mminCate[$mk]['checked'] = '';
					}
					$minCate[$k]['childmenu'] = $mminCate;
					$minCate[$k]['checked']   = '';
				}
				$bigCate[$key]['childmenu'] = $minCate;
				$bigCate[$key]['checked']   = '';
			}
			$this->assign('bigCate', $bigCate);
			$this->assign('authority_type',1);		
			$group = M('admin_group')->field(true)->select();
			$this->assign('group', $group);
			$page_tip = array('page_name'=>'添加管理员','btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('info',null);
			$this->assign('meta_title','添加管理员');
			$this->display('edit');
		}
	}
	
    /**
     * 编辑管理员
     */
    public function edit($id = 0){
        if(IS_POST){
            $adminModel = D('Adminer');
			$adminModel -> create();
			if ($_POST['password']){
				$adminModel -> password = md5($_POST['password']);
			}
			//用户权限
			$uRules = I('post.uRules');
			$authority_type = intval(I('post.authority_type',0));			
			if($authority_type){
				$authority = 'all';
			}else{
				$authority = ($uRules) ? implode(',',$uRules) : '';
			}
			$adminModel -> authority = $authority;
			
			if($adminModel){
				if($adminModel->save() !== false ){
					$this->success('管理员信息修改成功', back_url);
				}else{
					$this->error('管理员信息更新失败');
                }
            } else {
                $this->error($adminModel->getError());
            }
        }else{
            $info = array();
            /* 获取数据 */
            $info = M('Adminer')->field(true)->where('id='.$id)->find();
            if(false === $info){
                $this->error('获取管理员信息错误');
            }
            $this->assign('info', $info);
			$group = M('admin_group')->field(true)->select();
			$this->assign('group', $group);
			// 栏目结构			
			$bigCate = M('Category')->where('pid=0')->field('id,title')->select();		
			foreach($bigCate as $key=>$value){
				$minCate = M('Category')->where('pid='.$value['id'])->field('id,title')->select();
				foreach($minCate as $k=>$v){
					$mminCate = M('Category')->where('pid='.$v['id'])->field('id,title')->select();
					foreach($mminCate as $mk=>$mv){
						$resmk = D('Category')->checkcaterule($info['authority'],$mv['id']);
						$mminCate[$mk]['checked'] = ($resmk) ? 'checked' : '';
					}
					$minCate[$k]['childmenu'] = $mminCate;
					$resm = D('Category')->checkcaterule($info['authority'],$v['id']);
					$minCate[$k]['checked']   = ($resm) ? 'checked' : '';
				}
				$bigCate[$key]['childmenu'] = $minCate;
				$res = D('Category')->checkcaterule($info['authority'],$value['id']);
				$bigCate[$key]['checked'] = ($res) ? 'checked' : '';
			}
			$authority_type = ($info['authority'] == 'all') ? 1 : 0;
			$this->assign('authority_type',$authority_type);
			$this->assign('bigCate', $bigCate);
			$page_tip = array('page_name'=>'修改管理员','btn_name'=>'保存修改');
			$this->assign('page_tip', $page_tip);
            $this->meta_title = '修改管理员';
            $this->display();
        }
    }
	
	/*删除*/
	public function del(){
		$adminModel = M('Adminer');
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}
		//判断是否为超级管理员
		if ($id == C('USER_ADMINISTRATOR')){
			$this->error('您所选择的管理员为本站唯一的超级管理员,不能删除',back_url,3);	
		}
		//判断是不是最后一个管理员 如果是的 则不能删除
		$total = $adminModel->count();
		if ($total == 1){
			$this->error('您所选择的管理员是系统最后一个管理员,不能删除',back_url,3);
		}else{
			if($adminModel->where('id='.$id)->delete()){
				$this->success('所选数据删除成功!',back_url);
			} else {
				$this->error('数据删除失败，请重试!',back_url,3);
			}
		}
	}	
	
	public function logs(){
		$groupid = M('Adminer')->where('id='.UID)->getfield('groupid');
		$map = ($groupid == 1) ? array() : array('adminid'=>UID);
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Logs',$map,'id desc','*',C('LIST_ROWS'));
		$list = $result['lists'];
		if($list){
			foreach($list as $key=>$value){
				$admininfo = M('Adminer')->where('id='.$value['adminid'])->field('username,truename')->find();
				$list[$key]['adminid_text'] = $admininfo['truename'].' ('.$admininfo['username'].')';
            }
        }
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));		
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('meta_title','操作日志');
		$this->display('logs');
    }
}