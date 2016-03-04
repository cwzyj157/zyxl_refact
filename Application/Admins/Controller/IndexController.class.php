<?php
namespace Admins\Controller;
use Think\Controller;
use Think\Storage;
class IndexController extends CommonController{
    public function index(){
		//环境检测
		$env = D('Index')->check_env();
		//目录文件读写检测
		if(IS_WRITE){
			$dirfile = D('Index')->check_dirfile();
			$this->assign('dirfile', $dirfile);
		}	
		$this->assign('env', $env);
		$this->assign("meta_title",'管理首页');
		$this->display('Index:index');
    }


	
	/**
     * 编辑管理员
     */
    public function edit(){
        if(IS_POST){
			$id = I('post.id',0);
			$editpwd = I('post.editpwd',0);		
			$oldpassword = I('post.oldpassword','');
			$password = I('post.password','');
			$repassword = I('post.repassword','');			
			if ($id != UID){
				$sessionName = C('SESSION_PREFIX');
				session($sessionName,null);
				$this->error('您的登录信息有误，请重新登录',U('Index'));
			}
			//判断是否修改密码
			$data['truename'] = I('post.truename');
			$data['email'] = I('post.email');
			if ($editpwd){
				if (!$oldpassword){
					$this->error('请输入您的原始登录密码;如果不修改，请选择勾选“不修改密码');
				}				
				$oldpassword = md5($oldpassword);
				$map['password'] = $oldpassword;
				$map['id'] = $id;
				$user = M('Adminer')->where($map)->find();
				if (!$user){
					$this->error('您的原始登录密码输入错误，请返回重新输入。');
				}
				if (!$password){
					$this->error('您勾选了“修改密码”，请返回输入新密码;如果不修改，请选择勾选“不修改密码”');
				}
				if ($password != $repassword){
					$this->error('两次输入的密码不一致,请重新输入!');
				}
				$data['password'] = md5($password);
			}
			$editPass = M('Adminer')->where('id='.$id)->save($data);
			//echo M('Adminer')->_sql();exit;
			if($editPass !== false ){
				if ($editpwd){
					$sessionName = C('SESSION_PREFIX');
					session($sessionName,null);
					$edtmsg = '登录密码修改成功,请使用新密码重新登录';
				}else{
					$edtmsg = '登录帐号资料修改成功！';
				}				
                $this->success($edtmsg,U('Index/index'));
            }else{
                $this->error('登录密码信息更新失败');
            }
        } else {
            $info = M('adminer')->field(true)->find(UID);
			$this->assign("info",$info);
			$this->assign("meta_title",'修改帐号资料');
			$this->display('Index:edt');
        }
    }
}