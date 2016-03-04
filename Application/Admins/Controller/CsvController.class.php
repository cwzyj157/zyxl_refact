<?php
namespace Adnims\Controller;
use Think\Controller;
class CsvController extends CommonController{
	public function importmember(){
		if(IS_POST){
			import("Org.Util.PHPExcel");
			$filename = trim(I('post.csvfile',''));
			if(!$filename){
				$this->error("请上传Excel文件!",U('importmember'),3);
			}
			$filename = './Upload/'.$filename;
			$data = D('Phpexcel')->GetExceldata($filename);
			/*导入数据业务*/			
			dump($data);
		}else{		
			$assign = array(
				'meta_title' => '导入数据测试',
			);
			$this->assign($assign);
			$this->display('Csv/importmember');
		}	
	}
	
	public function exportitem(){  
		$filename = "导航标题";
		$result = M('Menu')->order('id desc')->select();
		$data[][0]=array($filename);
		$data[][1]=array("自增长ID","菜单名称","上级ID","链接地址");
		foreach ($result as $key => $value){
			$row_data = array();
			$row_data[] = $value['id'];
			$row_data[] = $value['title'];
			$row_data[] = $value['pid'];
			$row_data[] = $value['url'];
			$row_data[] = '娃哈哈';	
			$data[][$key+2] = $row_data;
		}		
		$res = D('Phpexcel')->ExportExcel($filename,$data,'xls');
		if(!$res){
			$this->error($filename.'导出失败!');
		}
	}
	
}