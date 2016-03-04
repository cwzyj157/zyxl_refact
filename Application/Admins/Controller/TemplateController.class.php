<?php
namespace Adnims\Controller;
use Think\Controller;
class TemplateController extends CommonController{
	public $Type_Config,$Temp_dir,$edtFile;
	protected function _initialize(){		
		parent :: _initialize();
		$this->Type_Config = array('0' => '系统模版');
		/*模版路径
		 * 如果更改了前台和后台的入口文件模块名称，必须在这里做相应的 修改
		*/
		$this->edtFile = array('.html','.htm','.txt','.shtml');
		$this->Temp_dir = array('0' => './Template/');
		$this->assign('Type_Config',$this->Type_Config);
	}
    public function index(){
		$type   = I('type',0);
		$path   = I('path','');
		$t_name = $this->Type_Config[$type];
		$t_dir = $this->Temp_dir[$type];
		
		$dir_path = getTmpPath($path,0);
		$fu_path = getTmpPath($path,1);
		$true_path = $t_dir.$dir_path;
		$true_path = str_replace('//','/',$true_path);
		if ($dir_path == ''){
			$show_fu = 0;
		}else{
			$show_fu = 1;
		}
		$this->assign('true_path',$true_path);
		$this->assign('fu_path',$fu_path);
		$this->assign('path',$path);
		$this->assign('show_fu',$show_fu);
		$this->assign('tree',dirtree($true_path));
		
		$this->assign('meta_title',$t_name.'管理');
		$this->assign('type',$type);
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->display('index');
    }
	
	public function tree($tree = null){
		$filelist = $tree['file'];
		$dirlist  = $tree['dir'];
        $this->assign('temp_tree',$tree);
		$this->assign('filelist',$filelist);
		$this->assign('dirlist',$dirlist);
        $this->display('tree');
    }
	
	/*修改*/
	public function edit($id=0){
		if(IS_POST){
			$w_con  = I('post.tempContent');
			$f_path = I('post.true_path');
			$f_name = I('post.file_name');
			$w_con     = stripslashes(I('post.tempContent'));
			$w_con     = htmlspecialchars_decode($w_con);
			$f_dir = $f_path.$f_name;
			$f_dir = str_replace('//','/',$f_dir);
			$f_Type = '.'.getFileType($f_dir);
			if (!in_array($f_Type,$this->edtFile)){
				$this->error('当前版本不支持修改['.$f_Type.']类型的模版!');
			}
			$w_con = str_replace('textKOOarea>','textarea>',$w_con);
			if (D('Xhttp')->make_file($f_dir,$w_con)){
				$this->success('模版 ['.$f_name.'] 修改成功!',back_url);
			}else{
				$this->error('模版 ['.$f_name.'] 修改失败，请检查文件夹读写权限!');
			}
        }else{
			$type   = I('type',0);
			$dir    = I('dir',0);
			$file_name   = I('name',0);
			$t_name = $this->Type_Config[$type];
			$t_dir  = $this->Temp_dir[$type];
			$dir_path = getTmpPath($dir,0);
			$true_path = $t_dir.$dir_path;
			$true_path = str_replace('//','/',$true_path);
			$full_path = $true_path.$file_name;
			if (!file_exists($full_path)){
				$this->error('模版文件['.$file_name.'] 不存在，或者为非法路径，请返回重新选择!');
			}
			$fileType = '.'.getFileType($full_path);
			if (!in_array($fileType,$this->edtFile)){
				$this->error('当前版本不支持修改['.$fileType.']类型的模版!');
			}
			$temp_content = get_file($full_path);
			$temp_content = str_replace('textarea>','textKOOarea>',$temp_content);
			$this->assign('TemplateEdit', TemplateEdit($temp_content,'tempContent'));

			$page_tip = array('page_name'=>'修改'.$t_name,'btn_name'=>'保存修改');
			$this->assign('page_tip', $page_tip);
			$this->assign('type', $type);
			$this->assign('true_path', $true_path);
			$this->assign('file_name', $file_name);
			$this->assign('meta_title','修改 ['.$t_name.'] 模版内容');
            $this->display('edit');
        }
    }
	/*添加*/
	public function add(){
		if(IS_POST){
			$true_path = I('post.true_path');
			$file_name = I('post.file_name');
			$w_con     = stripslashes(I('post.tempContent'));
			$w_con     = htmlspecialchars_decode($w_con);
			$w_con     = str_replace('textKOOarea>','textarea>',$w_con);
			$true_path = str_replace('//','/',$true_path);
			$full_path = $true_path.$file_name;
			//获取文件类型 判断是否支持修改;
			$fileType = '.'.getFileType($full_path);
			if (!in_array($fileType,$this->edtFile)){
				$this->error('当前版本不支持修改['.$fileType.']类型的模版!');
			}
			if (D('Xhttp')->make_file($full_path,$w_con)){
				$this->success('模版 ['.$file_name.'] 添加成功!',back_url);
			}else{
				$this->error('模版 ['.$file_name.'] 添加失败，请检查文件夹读写权限!');
			}		
		}else{
			$type   = I('type',0);
			$t_name = $this->Type_Config[$type];
			$dir    = I('dir',0);
			$t_dir  = $this->Temp_dir[$type];
			$dir_path = getTmpPath($dir,0);
			$true_path = $t_dir.$dir_path;
			$true_path = str_replace('//','/',$true_path);
					
			$this->assign('TemplateEdit', TemplateEdit('','tempContent'));
			$page_tip = array('page_name'=>'添加'.$t_name,'btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('type', $type);
			$this->assign('true_path', $true_path);
			$this->assign('meta_title','添加'.$t_name);
			$this->display('edit');		
		}
	}
	
	/*删除*/
	public function del(){
		$type   = I('type',0);
		$dir    = I('dir',0);
		$file_name   = I('name',0);
		$t_name = $this->Type_Config[$type];
		$t_dir  = $this->Temp_dir[$type];
		$dir_path = getTmpPath($dir,0);
		$true_path = $t_dir.$dir_path;
		$true_path = str_replace('//','/',$true_path);
		$full_path = $true_path.$file_name;
		if (!file_exists($full_path)){
			$this->error('模版文件['.$file_name.'] 不存在，或者为非法路径，请返回重新选择!');
		}
		if (unlink($full_path)){
			$this->success('模版 ['.$file_name.'] 删除成功!',back_url);
		}else{
			$this->error('模版 ['.$file_name.'] 删除失败，请检查文件夹读写权限!');
		}
	}	
	
}