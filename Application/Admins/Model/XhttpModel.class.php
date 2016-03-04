<?php
namespace Admins\Model;
use Think\Model;
class XhttpModel extends Model{
	/**
	 * 函数名： GetHttpPage
	 * �? 用：获取网页源码
	 * �? 数： url  ------ 要获取源码的网页地址
	 *	   �? code ----- -编码�? 1 GB 2 UTF
	**/
	public function GetHttpPage($url,$code=2){
		$data = curlInit($url);
		if($code==1){
			$data = mb_convert_encoding($data,'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
		}
    	return $data;
	}
	
	/**
     * 函数名：GetBody
     * �?  用：截取字符�?
     * �?  数：conStr ------ 将要截取的字符串
     * �?  数：rule   ------ 匹配规则
     * �?  数：flag   ------ 是否采用正则 0 -  使用phpQuery ; 1-采用正则匹配
	**/
	public function GetBody($conStr,$regcode,$flag=0){
		if ($flag == 0){
			import('Admins.ORG.phpQuery.phpQuery');
			$pqObj = \phpQuery::newDocumentHTML($conStr);
			$jsonArr = array();
			while (list($key,$reg_value) = each($regcode)){
				$iobj = pq($pqObj)->find($reg_value[0]);
				switch ($reg_value[1]){
					case 'text':
						$jsonArr[$key] = trim(pq($iobj)->text());
						break;
					case 'html':
						$jsonArr[$key] = trim(pq($iobj)->html());
						break;
					default:
						$jsonArr[$key] = pq($iobj)->attr($reg_value[1]);
						break;
				}
			}
		}else{
			$jsonArr = array();
			while (list($v,$reg_value) = each($regcode)){
				$arr = array();
				$regex = $this->FormatRegex($reg_value[0]);
				if (preg_match('/'.$regex.'/',$conStr,$arr)){
					switch ($reg_value[1]){
						case 'text':
							$jsonArr[$v] = RemoveAllhtml($arr[1]);
							break;
						default :
							$jsonArr[$v] = $arr[1];
							break;				
					}
				}
			}
		}
		return $jsonArr;
	}
	
	/**
     * 函数名：GetArray
     * �?  用：截取字符�?
     * �?  数：conStr     ------  将要截取的字符串
     * �?  数：regcode    ------  列表元素规则
	 * �?  数：looprule   ------  [phpQuery]循环列表   [正则] 范围外围
     * �?  数：flag       ------  是否颠�?�数组顺�? 0-倒序�?1-顺序
	 * �?  �?: regex      ------  是否用正则来匹配
	**/
	public function GetArray($conStr,$regcode,$looprule,$flag=0,$regex=0){
		$jsonArr = array();
		if ($regex == 0){
			import('Admins.ORG.phpQuery.phpQuery');
			$pqObj    = \phpQuery::newDocumentHTML($conStr);
			$artlist  = pq($looprule)->html();
			$listCode = \phpQuery::newDocumentHTML($artlist);
			while (list($key,$reg_value) = each($regcode)){
				$iobj = pq($listCode)->find($reg_value[0]);
				foreach($iobj as $item){
					switch ($reg_value[1]){
						case 'text':
							$jsonArr[$key][] = RemoveAllhtml(pq($item)->text());
							break;
						case 'html':
							$jsonArr[$key][] = pq($item)->html();
							break;
						default:
							$jsonArr[$key][] = pq($item)->attr($reg_value[1]);
							break;
					}	
				}
			}
		}else{
			//按正则匹�?
			$listCode = $conStr;
			if ($looprule){
				$regex = array();
				$regex['main'] = array($looprule,'html');
				$listCode = $this->GetBody($listCode,$regex,1);
				while (list($key,$reg_value) = each($regcode)){
					$regex_b = $this->FormatRegex($reg_value[0]);
					$arr = array();
					if (preg_match_all('/'.$regex_b.'/',$listCode['main'],$arr)){
						foreach($arr[1] as $value){
							switch ($reg_value[1]){
								case 'text':
									$jsonArr[$key][] = RemoveAllhtml($value);
									break;
								default:
									$jsonArr[$key][] = $value;
									break;
							}						
						}
					}
				}
			}
		}
		//数组是否倒序排列
		if ($flag == 0){
			$jsonArr = array_reverse($jsonArr);
		}
		return $jsonArr;
	}
	
	/**
     * 函数名：FormatUrl
     * �?  用：截取字符�?
     * �?  数：conStr ------  将要截取的字符串
     * �?  数：rule   ------  列表元素规则
     * �?  数：flag   ------  是否颠�?�数组顺�? 0-倒序�?1-顺序
	**/
	public function FormatUrl($url,$baseurl){
		if (is_array($url)){
        	$return = array();
        	foreach ($url as $href) {
            	$return[] = FormatUrl($href,$baseurl);
        	}
        	return $return;
    	}else{
        	if (stripos($url,'http://')===0 || stripos($url,'ftp://')===0){
            	return $url;
        	}
        	$str = str_replace('\\\\', '/', $url);
        	$parseUrl = parse_url(dirname($baseurl).'/');
        	$scheme = isset($parseUrl['scheme']) ? $parseUrl['scheme'] : 'http';
        	$host = $parseUrl['host'];
        	$path = isset($parseUrl['path']) ? $parseUrl['path'] : '';
       	 	$port = isset($parseUrl['port']) ? $parseUrl['port'] : '';
        	if (strpos($str,'/')===0) {
            	return $scheme.'://'.$host.$str;
        	}else{
				$part = explode('/', $path);
				array_shift($part);
				$count = substr_count($str, '../');
				if ($count>0) {
					for ($i=0; $i<=$count; $i++) {
						array_pop($part);
					}
				}
				$path = implode('/', $part);
				$str = str_replace(array('../','./'), '', $str);
				$path = $path=='' ? '/' : '/'.trim($path,'/').'/';
				return $scheme.'://'.$host.$path.$str;
        	}
   		}
	}
	
	/**
     * 函数名：FilterScript
     * �?  用：脚本过滤
     * �?  数：iContent ------  �?要过来的字符
     * �?  数：iScript   ------ 过滤规则  0|0|0|0|0|0|0|0|0|0|0|0|0|0|0 => Iframe|Object|Script|Style|StyleAttr|Div|Table|Tr|Td|Span|P|Img|Font|A|html
	**/
	public function FilterScript($iContent,$iScript='1|1|1|1|1|0|0|0|0|1|0|0|1|1|0'){
		$ConStr = $iContent;
		if (!$ConStr){
			return '';
		}
		$Property = explode('|',$iScript);
		if (count($Property) != 15){
			return $iContent;
		}
		if ($Property[0]){
			$ConStr = $this->ScriptHtml($ConStr,"iframe",1);
		}
		if ($Property[1]){
			$ConStr = $this->ScriptHtml($ConStr,"object",1);
		}
		if ($Property[2]){
			$ConStr = $this->ScriptHtml($ConStr,"script",1);
		}
		if ($Property[3]){
			$ConStr = $this->ScriptHtml($ConStr,"style",1);
		}
		if ($Property[4]){
			$ConStr = $this->ScriptHtml($ConStr,"style",4);
		}
		if ($Property[5]){
			$ConStr = $this->ScriptHtml($ConStr,"div",2);
		}	
		if ($Property[6]){
			$ConStr = $this->ScriptHtml($ConStr,"table",1);
			$ConStr = $this->ScriptHtml($ConStr,"tbody",1);
		}
		if ($Property[7]){
			$ConStr = $this->ScriptHtml($ConStr,"tr",1);
		}
		if ($Property[8]){
			$ConStr = $this->ScriptHtml($ConStr,"td",1);
		}
		if ($Property[9]){
			$ConStr = $this->ScriptHtml($ConStr,"span",2);
		}
		if ($Property[10]){
			$ConStr = $this->ScriptHtml($ConStr,"P",2);
		}	
		if ($Property[11]){
			$ConStr = $this->ScriptHtml($ConStr,"img",3);
		}
		if ($Property[12]){
			$ConStr = $this->ScriptHtml($ConStr,"font",2);
		}
		if ($Property[13]){
			$ConStr = $this->ScriptHtml($ConStr,"a",2);
		}
		if ($Property[14]){
			$ConStr = RemoveAllhtml($ConStr);
		}
		$replace_tip = array('DIV','Div','div','P','p','EM','em','Em','Span','span','SPAN');
		foreach ($replace_tip as $key){
			$ConStr = str_replace('<'.$key.'></'.$key.'>','',$ConStr);
		}
		return $ConStr;
	}
	
	/**
	 * 函数名：ScriptHtml
	 * �?  用：过滤html标记
	 * �?  数：iConStr  ------ 要过滤的字符�?
	 * �?  数：TagName ------ 字符串种�?
	 * �?  数：FType   ------ 过滤的类�?
	**/
	public function ScriptHtml($iConStr,$TagName,$FType){
		$ConStr = $iConStr;
		$regEx = '/\s[on].+?=([\""|\'])(.*?)\1/'; //过滤onload onchick
		$ConStr = preg_replace($regEx,'',$ConStr);
		switch ($FType){
			case 1:
				$regEx = '/<'.$TagName.'([^>]*)>([\s\S]*?)<\/'.$TagName.'>/i';
				$ConStr = preg_replace($regEx,'',$ConStr);
				break;
			case 2:
				$regEx = '/<'.$TagName.'([^>]*)>([\s\S]*?)<\/'.$TagName.'>/i';
				$ConStr = preg_replace($regEx,'\2',$ConStr);
				break;
			case 3:
				$regEx = '/<'.$TagName.'([^>]*)>/i';
				$ConStr = preg_replace($regEx,'',$ConStr);
				break;
			case 4:
				$regEx = '/\s*'.$TagName.'=([\"\'])([^>]*)\1/i';
				$ConStr = preg_replace($regEx,'',$ConStr);
				break;
		}
		return $ConStr;
	}
	
	/**
	 * 函数名： FormatRegex
	 * 作用: 对正则规则进行必要的转义
	 * 参数: regexcode �?要转义的字符�?
	**/
	public function FormatRegex($regexcode){
		$strA = array("/","'");
		$strB = array("\/","\'");
		$tmpRegex = str_replace($strA,$strB,$regexcode);
		$tmpRegex = str_replace('[*]','[\s\S]*?',$tmpRegex);
		$tmpRegex = str_replace('[#]','([\s\S]*?)',$tmpRegex);
		return $tmpRegex;	
	}
	
	
	/*
	 * 生成静�?�文�?
	 * filepath 文件路径
	 * 文件内容
	 * 是否转为GB2312  0 - utf-8  1- gb2312
	 * 返回�? true or false
	*/
	public function make_file($filepath,$content,$code=0){
		if (!$filepath){
			return false;
		}		
		//根据文件路径 �?次创建文件夹
		$fileinfo = explode('/',$filepath);
		$filedir = '';
		for ($i=0;$i < (count($fileinfo)-1);$i++){
			$filedir .= $fileinfo[$i].'/';
		}
		if (!is_dir($filedir)){
			$result = mkdir($filedir,'0755',true);
			if (!$result){
				return false;
			}
		}
		//写入文件		
		$file = fopen($filepath, "w");
		if (!$file){
			return false;
		}
		if ($code){
			$content = iconv( "UTF-8", "gb2312//IGNORE",$content);
		}
		$result = fwrite($file,$content);
		fclose($file);
		if ($result === false){
			return false;
		}else{
			return true;
		}
	}	
}