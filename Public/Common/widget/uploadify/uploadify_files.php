<?php
$fileTypes = array('jpg','jpeg','gif','png'); // 文件类型
$verifyToken = md5('koo_uPlOadPicZ554' . $_POST['timestamp']); //验证数据

if (!empty($_FILES) && $_POST['token'] == $verifyToken){
	$targetFolder = '../../../../Upload/'.$_POST['picdir'].'/';	
    if(!is_dir($targetFolder)){
		create_dir($targetFolder);
	}
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$uuid = (isset($_POST['uuid'])) ? $_POST['uuid'] : 0;
	$fileName = mkFileName($_FILES['Filedata']['name'],$uuid);	
	$targetPath = $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . $fileName;
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	$data = array();
	//if (in_array(strtolower($fileParts['extension']),$fileTypes)){
		if (!move_uploaded_file($tempFile,$targetFile)){
			$data['status'] = 0;
			$data['info']   = '移动文件失败！';
		}else{
			//分析图片宽高
			$arr = getimagesize($targetFile);
			$strarr = explode("\"",$arr[3]);
			$data['status'] = 1;
			$data['url']    = $targetFile;
			$data['info']   = '文件上传成功';
			$data['name']   = $_FILES['Filedata']['name'];
			$data['data'] = array(
				'path'   => $targetFile,
				'width'  => $strarr[1],
				'height' => $strarr[3]
			);
		}
	//}else {
	//	$data['status'] = 0;
	//	$data['info']   = '文件格式错误(只支持 jpg | gif | png 格式图片)';
	//}
	echo json_encode($data);
}

/*
 * 基于url地址逐级创建目录，
*/

function create_dir($dir){
    $files = array_filter(explode("/",$dir));
	$tempFile="";
    foreach ($files as $key => $value) {
	     $tempFile .=$value."/"; 
	     if(!is_dir($tempFile)){     
            mkdir($tempFile);
        }
    }
	return  $tempFile;
}

/*
 *  生成随机图片名字
 */
	function mkFileName($tempName,$uuid=0){
		$extend = getExtend($tempName);
		if ($uuid > 0){
			$str = 'uuid_'.$uuid.'_tmp';
		}else{
			$str = date("YmdHi");
			$str.=rand(1000,9999);		
		}
		return $str.".".$extend;
   }
   
/*获取后缀名
**/
	function getExtend($file_name){
		$extend = pathinfo($file_name);
		$extend = strtolower($extend["extension"]);
		return $extend;
	}
?>