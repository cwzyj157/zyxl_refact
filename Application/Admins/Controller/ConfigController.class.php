<?php
namespace Adnims\Controller;
use Think\Controller;
class ConfigController extends CommonController{
    public function index(){
		$sokey = trim(I('get.sokey'));
		$map = array();
		//$map['id']  = array('not in','21,22');
		if ($sokey){
			$map['title'] = array('like',"%{$sokey}%");
		}
		$InfosPage = new \OT\Pages;
		$result = $InfosPage->getLists('Config',$map,'`group` desc,sort,id','*',C('LIST_ROWS'));
		$list = $result['lists'];
		int_to_string($list,array('status'=>array(1=>'',0=>'<font color="#ff0000">  已禁用</font>')));
		$this->assign(array('list'=>$list,'_page'=>$result['page'],'_total'=>$result['total']));
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('meta_title','参数配置');
		$this->display('index');
    }
	
	public function group(){
		$id     =   I('get.id',1);
        $type   =   C('CONFIG_GROUP_LIST');
		$list   =   M("Config")->where(array('status'=>1,'group'=>$id))->field('id,name,title,extra,value,remark,type')->order('sort')->select();
        if($list) {
            $this->assign('list',$list);
        }
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->assign('id',$id);
		$this->assign('meta_title',$type[$id].'设置');
		$this->display('group');
    }
	
    /**
     * 添加配置
     */
	public function add(){
		$Config = D('Config');
		if(IS_POST){
			$data = $Config->create();
			if($data){
                $id = $Config->add();
                if($id){
					S('DB_CONFIG_DATA',null);
                    $this->success('新的参数配置添加成功!',back_url);
                } else {
                    $this->error('参数配置添加失败，请返回重新填写表单!');
                }
            } else {
                $this->error($Config->getError());
            }			
		}else{
			$page_tip = array('page_name'=>'添加配置','btn_name'=>'确定添加');
			$this->assign('page_tip', $page_tip);
			$this->assign('info',null);
			$this->assign('meta_title','参数配置');
			$this->display('edit');	
		}
	}
	
    /**
     * 编辑配置
     */
    public function edit($id = 0){
        if(IS_POST){
            $Config = D('Config');
            $data = $Config->create();
            if($data){
                if($Config->save()){
                    S('DB_CONFIG_DATA',null);
                    $this->success('参数配置更新成功', back_url);
                } else {
                    $this->error('参数配置更新失败');
                }
            } else {
                $this->error($Config->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Config')->field(true)->find($id);

            if(false === $info){
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
			$page_tip = array('page_name'=>'修改配置','btn_name'=>'保存修改');
			$this->assign('page_tip', $page_tip);
            $this->meta_title = '编辑配置';
            $this->display();
        }
    }
	
	/*保存设置*/
	public function save(){
		$id     =   I('get.id',1);
        $type   =   C('CONFIG_GROUP_LIST');
		$config = I('post.config');
		if (is_array($config)){
			foreach ($config as $name => $value){
				$map = array('name' => $name);
				$data['value'] = $value; 
				M('Config')->where($map)->save($data);
			}
		}
		S('DB_CONFIG_DATA',null);
        $this->success($type[$id].'配置修改成功!',back_url,3);
	}
	
	/*批量排序*/
	public function resort(){
		$action   = trim(I('post.action'));
		if ($action =='resort'){
			//批量修改当前页面排序
			foreach ($_POST as $k=>$v){
				if (substr($k,0,5)=='sort_'){
					$select_id = substr($k,5);
					$order_id = $v;
					$menuData = M('config');
					if ($select_id){
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
	/*清除系统缓存*/
	public function delcache(){
		//删除S缓存
		S('DB_CONFIG_DATA',null);
		
		//删除Runtime文件夹的内容
		$dirs = './Application/Runtime/';
		//@mkdir('Runtime',0777,true);
		$data = rmdirr($dirs);
		if ($data !== false){
			$result = '系统缓存文件已经成功清除。';
		}else{
			$result = '缓存文件夹删除失败，请检查目录 ['.$dirs.'] 权限。';
		}
		$this->assign('meta_title','清空系统缓存');
		$this->assign('result',$result);
		$this->display('del_cache');
		//清空缓存文件夹
		
		//清除配置文件缓存		
	}
	
	/*删除*/
	public function del(){
		$id = intval(I('id',0));
		if (!$id){
			$this->error('请选择需要删除的数据!',back_url);
		}
		/* 批量删除时候使用  in 查询操作
		$id = array_unique((array)I('id',0));  //排除相项
		if (empty($id)) {
            $this->error('请选择需要删除的数据!',$back_url);
        }
		$map = array('id' => array('in', $id) );
		*/
		if(M('config')->where('id='.$id)->delete()){
			S('DB_CONFIG_DATA',null);
			$this->success('所选数据删除成功!',back_url);
		} else {
			$this->error('数据删除失败，请重试!',back_url,3);
		}
	}

	/*生成网站地图*/
	public function sitemap(){
		$siteurl = C('WEB_URL');
		$Xhttp = D('Xhttp');	
		//总地图
		$sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
		$sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$sitemap .= '<sitemap><loc>'.$siteurl.'/news.xml</loc><lastmod>'.date('Y-m-d').'</lastmod></sitemap>';
		$sitemap .= '<sitemap><loc>'.$siteurl.'/tags.xml</loc><lastmod>'.date('Y-m-d').'</lastmod></sitemap>';
		$sitemap .= '</sitemapindex>';
		$Xhttp->make_file('./sitemap.xml',$sitemap);
		
		//最新更新文章
		$newmap = '<?xml version="1.0" encoding="UTF-8"?>';
		$newmap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$data = M('Data')->field('title,id,create_time,md5code')->order('id desc')->limit(200)->select();
		foreach($data as $key=>$value){
			$newmap .= '<sitemap><loc>'.$siteurl.'/u/'.$value['md5code'].'.html</loc><lastmod>'.date('Y-m-d',$value['create_time']).'</lastmod></sitemap>';
		}
		$newmap .= '</sitemapindex>';
		$Xhttp->make_file('./news.xml',$newmap);		
		
		//标签地图
		$tagmap = '<?xml version="1.0" encoding="UTF-8"?>';
		$tagmap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$data = M('Tags')->field('tagname,id')->order('id desc')->limit(20000)->select();
		foreach($data as $key=>$value){
			$tagmap .= '<sitemap><loc>'.$siteurl.'/tags/'.$value['id'].'/</loc><lastmod>'.date('Y-m-d').'</lastmod></sitemap>';
		}
		$tagmap .= '</sitemapindex>';
		$Xhttp->make_file('./tags.xml',$tagmap);		
		
		$this->success('网站地图生成成功！!',U('Config/group'));		
	}
	
	
}