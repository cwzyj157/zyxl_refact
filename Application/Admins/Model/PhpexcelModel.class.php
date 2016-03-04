<?php
namespace Admins\Model;
use Think\Model;

class PhpexcelModel extends Model {

	public function GetExceldata($filepath){
		if(!$filepath){
			return false;
		}
		$extension = substr(strrchr($filepath, '.'), 1);
		$PHPExcel=new \PHPExcel();
		if($extension == 'xls'){
			import("Org.Util.PHPExcel.Reader.Excel5");
			$PHPReader=new \PHPExcel_Reader_Excel5();
		}elseif ($extension == 'xlsx'){
			import("Org.Util.PHPExcel.Reader.Excel2007");
			$PHPReader=new \PHPExcel_Reader_Excel2007();
		}else{
			return false;
		}
		$PHPExcel=$PHPReader->load($filepath);
		$currentSheet=$PHPExcel->getSheet(0); //获取表中的第�?个工作表，如果要获取第二个，�?0改为1，依次类�?
		$allColumn=$currentSheet->getHighestColumn(); //获取总列�?
		$allRow=$currentSheet->getHighestRow(); //获取总列�?		
		
		//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0�?�?
		for($currentRow=1;$currentRow<=$allRow;$currentRow++){
			//从哪列开始，A表示第一�?
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				//数据坐标
				$address=$currentColumn.$currentRow;
				//读取到的数据，保存到数组$arr�?
				$arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
			}
		}
		$arr = array_values($arr);
		return $arr;
	}
	
	public function ExportExcel($filename,$data,$filetype='xls'){
		//对数据进行检�?  
        if(empty($data) || !is_array($data)){  
			return false;
        }
		$filetype = ($filetype == 'xls') ? 'xls' : 'xlsx';
		$clasname = ($filetype == 'xls') ? 'Excel5' : 'Excel2007';
		//导入PHPExcel类库
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.$clasname");
		import("Org.Util.PHPExcel.IOFactory.php");
		$date = date("Ymd",time());
		$filename = iconv("utf-8","gb2312",$filename);
        $fileName = $filename."_{$date}.".$filetype;		
        $objPHPExcel = new \PHPExcel();  //创建PHPExcel对象，注意，不能少了\
		/*以下是一些设�? ，什么作�? 标题啊之类的*/
		$objPHPExcel->getProperties()
			->setCreator("系统导出")
			->setLastModifiedBy("系统导出")
			->setTitle($filename)
			->setSubject($filename.'导出')
			->setDescription($filename)
			->setKeywords($filename)
			->setCategory($filename);
		//设置表头  
		$key    = ord("A");
		$column = 1;
		$objActSheet = $objPHPExcel->getActiveSheet();  
		foreach ($data as $ke => $row) {                  
			foreach($row as $key => $rows){ //行写�?  
				$span = ord("A");
				foreach($rows as $keyName=>$value){// 列写�?  
					$j = chr($span);  
					$objActSheet->setCellValue($j.$column, $value);  
					$span++;
				}
			}  
			$column++;  
		}
		//设置活动单指数到第一个表,�?以Excel打开这是第一个表  
		$objPHPExcel->setActiveSheetIndex(0);  
		$objPHPExcel->getActiveSheet()->setTitle('User');
		header('Content-Type: application/vnd.ms-excel');  
		header("Content-Disposition: attachment;filename=\"$fileName\"");  
		header('Cache-Control: max-age=0');  
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,$clasname);
		$objWriter->save('php://output'); //文件通过浏览器下�?  
        exit;
	}
	
	
}