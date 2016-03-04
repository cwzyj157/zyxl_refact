<?php
namespace Admins\Model;
use Think\Model;
class ExpandModel extends Model{
	/*
	* �?测扩展是否已经安�?
	*/
	public function checkExpand_user(){
		$check_lock = '../Expand/User/Data/install.lock';
		if (is_file($check_lock)){
			return true;
		}else{
			return false;
		}
	}
	
	public function checkExpand_weixin(){
		$check_lock = '../Expand/Weixin/Data/install.lock';
		if (is_file($check_lock)){
			return true;
		}else{
			return false;
		}
	}
	
	/*添加菜单*/
	public function addMenu($Datas){
		$tmpid = 0;
		if (is_array($Datas)){
			if (count($Datas) == 6){
				$map = array('title'=>$Datas[0],'url'=>$Datas[3]);
				$oid = M('Menu')->where($map)->getfield('id');
				if ($oid){
					$tmpid = $oid;
				}else{
					$value = array(
						'title' => $Datas[0],
						'pid' => $Datas[1],
						'sort' => $Datas[2],
						'url' => $Datas[3],
						'hide' => $Datas[4],
						'group' => $Datas[5]
					);
					$tmpid = M('Menu')->add($value);					
				}
			}		
		}
		return $tmpid;
	}
	
	/*复制文件�?*/
	public function CopyFiles($dirs,$expcode){
		$fromdir = '../Expand/'.$expcode.'/Files/';
		$FileUtil = new \OT\FileUtil();
		foreach($dirs as $key=>$value){
			$result = $FileUtil->copyDir($fromdir.$value['from'],$value['to'],true);
			if ($result !== false){
				$this->show_msg($value['to'].'复制成功');
			}else{
				session('exp_error',true);
				$this->show_msg($value['to'].'复制失败','error');				
			}			
		}	
	}
	/*删除文件(�?)*/
	public function DelsFiles($files){
		$FileUtil = new \OT\FileUtil();
		foreach ($files as $key=>$value){
			if (is_dir($value)){
				$result = $FileUtil->unlinkDir($value);
				if ($result){
					$this->show_msg($value.'删除成功!');
				}else{
					$this->show_msg($value.'删除失败，请手动删除!','error');
				}				
			}else{
				$result = $FileUtil->unlinkFile($value);
				if ($result){
					$this->show_msg($value.'删除成功!');
				}else{
					$this->show_msg($value.'删除失败，请手动删除!','error');
				}			
			}
		}
	}
	
	public function show_msg($msg, $class = 'success'){
		echo "<script type=\"text/javascript\">showmsg(\"{$msg}\",\"{$class}\")</script>";
		cut_push();
	}	
	
	public function page_goo($url){
		cut_push(1);
		echo "<script type=\"text/javascript\">window.location.href=\"".$url."\"</script>";		
	}
	
	/*数据库信�?*/
	public function db_config(){
		$DB = array(
			'DB_TYPE' => C('DB_TYPE'),
			'DB_HOST' => C('DB_HOST'),
			'DB_NAME' => C('DB_NAME'),
			'DB_USER' => C('DB_USER'),
			'DB_PWD' => C('DB_PWD'),
			'DB_PORT' => C('DB_PORT'),
			'DB_PREFIX' => C('DB_PREFIX')
		);
		return $DB;
	}
	
	/**
	 * 创建数据�?
	 * @param  resource $db 数据库连接资�?
	 */
	public function create_tables($db,$sqlfile){
		//读取SQL文件
		$sql = file_get_contents($sqlfile);
		$sql = str_replace("\r", "\n", $sql);
		$sql = explode(";\n", $sql);
		//替换表前�?
		$orginal = C('DB_PREFIX');
		$sql = str_replace(" `koo_", " `{$orginal}", $sql);
		//�?始安�?
		foreach ($sql as $value) {
			$value = trim($value);
			if(empty($value)) continue;
			if(substr($value,0,12) == 'CREATE TABLE'){
				$name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $value);
				$msg  = "操作数据�? [ {$name} ] ";
				if(false !== $db->execute($value)){
					$this->show_msg($msg . '成功!');
				} else {
					$this->show_msg($msg . '失败!', 'error');
					session('exp_error',true);
				}
			} else {
				$db->execute($value);
			}
		}
	}
	
	/*判断表是否存�?*/
	public function checkDBtable($tablename){
		$orginal = C('DB_PREFIX');
		$tablename = str_replace(" `koo_", " `{$orginal}", $tablename);
		$ExpTable = mysql_fetch_row(mysql_query("SHOW TABLES LIKE '{$tablename}' "));
		if($ExpTable){
			return true;
		}else{
			return false;
		}	
	}
	
}