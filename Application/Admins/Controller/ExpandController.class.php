<?php
namespace Adnims\Controller;
use Think\Controller;
use Think\Db;
class ExpandController extends CommonController{
	protected function _initialize(){
		parent :: _initialize();
		$Expan = array();
		if(is_dir('./Expand/User')){
			$Expan['1001'] = array('name'=>'会员系统(User)','checkaction'=>'checkExpand_user','installaction'=>'getExpand_user','uninstall'=>'uninstall_user');
		}
		if(is_dir('./Expand/Weixin')){
			//$Expan['1002'] = array('name'=>'微信开放平台(Weixin)','checkaction'=>'checkExpand_weixin','installaction'=>'getExpand_weixin','uninstall'=>'uninstall_weixin');
		}
		$this->Exp = $Expan;
	}
		
	public function index(){
		$lists = $this->Exp;
		foreach ($lists as $key=>$value){
			$exp_status = D('Expand') ->$value['checkaction']();
			if ($exp_status){
				$lists[$key]['status_text'] = '<font color="#008000">已经安装</font>';
				$lists[$key]['status_btn']  = '<a href="'.U('Expand/uninstall?openid='.$key).'" class="exp_uninstall">删除扩展</span>';
			}else{
				$lists[$key]['status_text'] = '<font color="#FF0000">尚未安装</font>';
				$lists[$key]['status_btn']  = '<a href="'.U('Expand/install?openid='.$key).'" class="exp_install">现在安装</span>';
			}	
		}
		$assign = array(
			'meta_title' => '扩展功能',
			'lists' => $lists,
		);
		$this->assign($assign);
		$this->display('index');	
	}
	
	public function install(){
		$ExpInfo = $this->Exp;
		$openid = I('get.openid',0);
		if (!$ExpInfo[$openid]){
			$this->error("该扩展Openid验证失败，请重新选择。",U('Expand/index'));
		}
		$assign = array(
			'meta_title' => '安装扩展功能',
			'expinfo'   => $ExpInfo[$openid],
		);
		$this->assign($assign);
		$this->display('install');
		$this->$ExpInfo[$openid]['installaction']();
	}
	
	public function uninstall(){
		$ExpInfo = $this->Exp;
		$openid = I('get.openid',0);
		$assign = array(
			'meta_title' => '删除扩展功能',
			'expinfo'   => $ExpInfo[$openid],
		);
		$this->assign($assign);
		$this->display('install');
		$this->$ExpInfo[$openid]['uninstall']();
	}
	
	/****操作扩展功能****/
	
	/*安装用户中心扩展*/	
	public function getExpand_user(){
		$ExpModel = D('Expand');
		$chk_exp = $ExpModel->checkDBtable('koo_member');
		if ($chk_exp){
			$ExpModel->show_msg('该扩展【会员中心】已经存在，不用再次安装','error');
			exit;
		}
		//复制文件
		$copydir = array(
			//'uc_client' => array('from'=>'uc_client','to'=>'./uc_client'),
			'public' => array('from'=>'Public/User','to'=>'./Public/User'),
			'user' => array('from'=>'Application/User','to'=>'../Application/User'),
			'adnim' => array('from'=>'Application/Adnims','to'=>'../Application/Adnims'),
		);	
		$ExpModel->CopyFiles($copydir,'User');
		//创建数据库
		$dbconfig = $ExpModel->db_config();
		$db = Db::getInstance($dbconfig);
		$ExpModel->create_tables($db,'../Expand/User/Data/user_install.sql');
		//添加配置
		$addConfig = array();
		//$addConfig[] = array('name'=>'OPEN_UCENTERAPI','title'=>'是否与Ucenter通讯','sort'=>0,'type'=>4,'group'=>0,'extra'=>'0:否 1:是','remark'=>'是否与Ucenter通讯','create_time'=>time(),'update_time'=>time(),'value'=>'0','status'=>1);
		foreach($addConfig as $key=>$data){
			$ids = M('Config')->add($data);
			if ($ids > 0){
				$ExpModel->show_msg('系统配置['.$data['name'].']添加成功!');
			}else{
				session('exp_error',true);
				$ExpModel->show_msg('系统配置['.$data['name'].']添加失败!','error');
			}				
		}	
		//添加菜单
		$id_1 = $ExpModel->addMenu(array('会员中心',0,2,'Member/index',0,''));
		if ($id_1 > 0){
			$id_1_1 = $ExpModel->addMenu(array('用户资料配置',$id_1,1,'Member/config',0,'参数配置'));
			$id_1_2 = $ExpModel->addMenu(array('会员列表',$id_1,2,'Member/index',0,'会员列表'));
			$id_1_3 = $ExpModel->addMenu(array('提现申请',$id_1,3,'Member/withdraw',0,'会员操作'));
			$id_1_4 = $ExpModel->addMenu(array('充值记录',$id_1,4,'Member/payrecord',0,'会员操作'));			
			if ($id_1_1){
				$ExpModel->addMenu(array('修改配置',$id_1_1,1,'Member/editconfig',1,''));
				$ExpModel->addMenu(array('删除配置',$id_1_1,2,'Member/delconfig',1,''));
				$ExpModel->addMenu(array('排序',$id_1_1,3,'Member/sortconfig',1,''));
			}
			if ($id_1_2){
				$ExpModel->addMenu(array('修改会员信息',$id_1_2,1,'Member/edit',1,''));
				$ExpModel->addMenu(array('删除会员',$id_1_2,2,'Member/del',1,''));
			}
			if ($id_1_3){
				$ExpModel->addMenu(array('处理提现申请',$id_1_3,1,'Member/dispose',1,''));
			}			
		}
		$ExpModel->show_msg('系统菜单添加成功!!');
		//生成配置
		$installstatus = session('exp_error');
		if ($installstatus){
			$ExpModel->show_msg('安装扩展失败，请检查文件夹权限','error');
		}else{
			D('Xhttp')->make_file('../Expand/User/Data/install.lock','lock');
			S('DB_CONFIG_DATA',null);
			session('exp_error',null);
			$ExpModel->show_msg(' 扩展功能安装成功!');
			//$ExpModel->page_goo(U('Expand/index'));
		}
	}
	
	public function uninstall_user(){
		//echo '近期修改的数据尚未备份，暂不要卸载';exit;
		$ExpModel = D('Expand');
		$chk_exp = $ExpModel->checkDBtable('koo_member');
		if (!$chk_exp){
			$ExpModel->show_msg('该扩展【会员中心】不存在，无法正常卸载','error');
			exit;
		}
		//删除文件夹
		$deldir = array(
			//'../Web/uc_client',
			'../Web/Public/User',
			'../Application/User',
			'../Application/Adnims/Controller/MemberController.class.php',
			'../Application/Adnims/Model/MemberModel.class.php',
			'../Application/Adnims/View/Member',
		);	
		$ExpModel->DelsFiles($deldir);
		//删除数据表
		$dbconfig = $ExpModel->db_config();
		$db = Db::getInstance($dbconfig);
		$ExpModel->create_tables($db,'../Expand/User/Data/user_uninstall.sql');
		//删除配置
		//$delConfig = array('OPEN_UCENTERAPI');
		foreach($delConfig as $key=>$data){
			$map = array('name'=>$data);
			$result = M('Config')->where($map)->delete();
			if($result){
				$ExpModel->show_msg(' 扩展功能删除成功!');
			} else {
				$ExpModel->show_msg(' 扩展功能删除失败!','error');
			}
		}
		//删除菜单
		$delMenu = array('Member/index','Member/edit','Member/del','Member/dispose','Member/withdraw','Member/config','Member/editconfig','Member/delconfig','Member/sortconfig');
		foreach($delMenu as $key=>$data){
			$map = array('url'=>$data);
			$result = M('Menu')->where($map)->delete();
			if($result){
				$ExpModel->show_msg(' 菜单['.$data.']删除成功!');
			} else {
				$ExpModel->show_msg(' 菜单['.$data.']删除失败!','error');
			}
		}		
		$ExpModel->show_msg('系统菜单添加成功!!');
		//删除lock配置
		$installstatus = session('exp_error');
		if ($installstatus){
			$ExpModel->show_msg('卸载扩展失败，请检查文件夹权限','error');
		}else{
			$dellock = array('../Expand/User/Data/install.lock');	
			$ExpModel->DelsFiles($dellock);
			S('DB_CONFIG_DATA',null);
			session('exp_error',null);
			$ExpModel->show_msg(' 扩展功能安卸载成功!');
			$ExpModel->page_goo(U('Expand/index'));
		}		
	}
	
	
	//安装微信 
	public function getExpand_weixin(){
		$ExpModel = D('Expand');
		$chk_exp = $ExpModel->checkDBtable('koo_weixinconfig');
		if ($chk_exp){
			$ExpModel->show_msg('该扩展【微信公众平台】已经存在，不用再次安装','error');
			exit;
		}
		//复制文件
		$copydir = array(
			'user' => array('from'=>'Application/','to'=>'../Application/'),
		);
		$ExpModel->CopyFiles($copydir,'Weixin');
		//创建数据库
		$dbconfig = $ExpModel->db_config();
		$db = Db::getInstance($dbconfig);
		$ExpModel->create_tables($db,'../Expand/Weixin/Data/user_install.sql');
		//添加配置
		//添加菜单
		$id_1 = $ExpModel->addMenu(array('微信中心',0,5,'Weixin/index',0,''));
		if ($id_1 > 0){
			$id_1_1 = $ExpModel->addMenu(array('微信设置',$id_1,1,'Weixin/index',0,'微信中心'));
			$id_1_2 = $ExpModel->addMenu(array('微信素材管理',$id_1,2,'Weixin/data',0,'微信中心'));
			$id_1_3 = $ExpModel->addMenu(array('自定义菜单',$id_1,3,'Weixin/menu',0,'微信中心'));
			$id_1_4 = $ExpModel->addMenu(array('微网站设置',$id_1,4,'Wap/index',0,'微官网管理'));
			$id_1_5 = $ExpModel->addMenu(array('微网站内容管理',$id_1,5,'Wap/data',0,'微官网管理'));
			$id_1_6 = $ExpModel->addMenu(array('微网站栏目管理',$id_1,6,'Wap/menu',0,'微官网管理'));			
			if ($id_1_2){
				$ExpModel->addMenu(array('添加素材',$id_1_2,1,'Weixin/adddata',1,''));
				$ExpModel->addMenu(array('修改素材',$id_1_2,2,'Weixin/editdata',1,''));
				$ExpModel->addMenu(array('删除素材',$id_1_2,3,'Weixin/deldata',1,''));
				$ExpModel->addMenu(array('素材排序',$id_1_2,4,'Weixin/sortdata',1,''));
			}
			if ($id_1_3){
				$ExpModel->addMenu(array('添加自定义菜单',$id_1_3,1,'Weixin/addmenu',1,''));
				$ExpModel->addMenu(array('修改自定义菜单',$id_1_3,2,'Weixin/editmenu',1,''));
				$ExpModel->addMenu(array('删除自定义菜单',$id_1_3,3,'Weixin/delmenu',1,''));
				$ExpModel->addMenu(array('自定义菜单排序',$id_1_3,4,'Weixin/sortmenu',1,''));
				$ExpModel->addMenu(array('生成自定义菜单',$id_1_3,5,'Weixin/createmenu',1,''));
				$ExpModel->addMenu(array('撤销自定义菜单',$id_1_3,6,'Weixin/removemenu',1,''));
			}
			if ($id_1_5){
				$ExpModel->addMenu(array('添加内容',$id_1_5,1,'Wap/adddata',1,''));
				$ExpModel->addMenu(array('修改内容',$id_1_5,2,'Wap/editdata',1,''));
				$ExpModel->addMenu(array('删除内容',$id_1_5,3,'Wap/deldata',1,''));
				$ExpModel->addMenu(array('内容排序',$id_1_5,4,'Wap/sortdata',1,''));
				$ExpModel->addMenu(array('关联内容列表',$id_1_5,5,'Wap/loaddata',1,''));				
			}
			if ($id_1_6){
				$ExpModel->addMenu(array('添加Wap栏目',$id_1_6,1,'Wap/addmenu',1,''));
				$ExpModel->addMenu(array('修改Wap栏目',$id_1_6,2,'Wap/editmenu',1,''));
				$ExpModel->addMenu(array('删除Wap栏目',$id_1_6,3,'Wap/delmenu',1,''));
				$ExpModel->addMenu(array('Wap栏目排序',$id_1_6,4,'Wap/sortmenu',1,''));
			}			
		}		
		$ExpModel->show_msg('系统菜单添加成功!!');
		//生成配置
		$installstatus = session('exp_error');
		if ($installstatus){
			$ExpModel->show_msg('安装扩展失败，请检查文件夹权限','error');
		}else{
			D('Xhttp')->make_file('../Expand/Weixin/Data/install.lock','lock');
			S('DB_CONFIG_DATA',null);
			session('exp_error',null);
			$ExpModel->show_msg(' 扩展功能安装成功!');
			//$ExpModel->page_goo(U('Expand/index'));
		}
	}
	
	public function uninstall_weixin(){
		//echo '近期修改的数据尚未备份，暂不要卸载';exit;
		$ExpModel = D('Expand');
		$chk_exp = $ExpModel->checkDBtable('koo_wapconfig');
		if (!$chk_exp){
			$ExpModel->show_msg('该扩展【微信公众平台】不存在，无法正常卸载','error');
			exit;
		}
		//删除文件夹
		$deldir = array(
			'../Application/Adnims/Controller/WapController.class.php',
			'../Application/Adnims/Controller/WeixinController.class.php',			
			'../Application/Adnims/Model/WeixinModel.class.php',
			'../Application/Adnims/Model/WapModel.class.php',
			'../Application/Adnims/View/Wap',
			'../Application/Adnims/View/Weixin',
			'../Application/Wap',
		);	
		$ExpModel->DelsFiles($deldir);
		//删除数据表
		$dbconfig = $ExpModel->db_config();
		$db = Db::getInstance($dbconfig);
		$ExpModel->create_tables($db,'../Expand/Weixin/Data/user_uninstall.sql');
		//删除配置
		$delConfig = array();
		foreach($delConfig as $key=>$data){
			$map = array('name'=>$data);
			$result = M('Config')->where($map)->delete();
			if($result){
				$ExpModel->show_msg(' 扩展功能删除成功!');
			} else {
				$ExpModel->show_msg(' 扩展功能删除失败!','error');
			}
		}
		//删除菜单
		$delMenu = array('Weixin/index','Weixin/data','Weixin/menu','Wap/index','Wap/data','Wap/menu','Weixin/adddata','Weixin/editdata','Weixin/deldata','Weixin/sortdata','Weixin/addmenu','Weixin/editmenu','Weixin/delmenu','Weixin/sortmenu','Weixin/createmenu','Weixin/removemenu','Wap/adddata','Wap/editdata','Wap/deldata','Wap/sortdata','Wap/loaddata','Wap/addmenu','Wap/editmenu','Wap/delmenu','Wap/sortmenu');
		foreach($delMenu as $key=>$data){
			$map = array('url'=>$data);
			$result = M('Menu')->where($map)->delete();
			if($result){
				$ExpModel->show_msg(' 菜单['.$data.']删除成功!');
			} else {
				$ExpModel->show_msg(' 菜单['.$data.']删除失败!','error');
			}
		}		
		$ExpModel->show_msg('系统菜单添加成功!!');
		//删除lock配置
		$installstatus = session('exp_error');
		if ($installstatus){
			$ExpModel->show_msg('卸载扩展失败，请检查文件夹权限','error');
		}else{
			$dellock = array('../Expand/Weixin/Data/install.lock');	
			$ExpModel->DelsFiles($dellock);
			S('DB_CONFIG_DATA',null);
			session('exp_error',null);
			$ExpModel->show_msg(' 扩展功能安卸载成功!');
			//$ExpModel->page_goo(U('Expand/index'));
		}
		
	}
}