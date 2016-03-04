<?php
namespace Admins\Model;
use Think\Model;
define('INSTALL_APP_PATH', realpath('./') . '/');
class IndexModel extends Model{		
	/**
	 * 系统环境�?�?
	 * @return array 系统环境数据
	 */
	public function check_env(){
		$items = array(
			'os'      => array('操作系统', '不限�?', '类Unix', PHP_OS, 'success'),
			'php'     => array('PHP版本', '5.3', '5.3+', PHP_VERSION, 'success'),
			//'mysql'   => array('MYSQL版本', '5.0', '5.0+', '未知', 'success'), //PHP5.5不支持mysql版本�?�?
			'upload'  => array('附件上传', '不限�?', '2M+', '未知', 'success'),
			'gd'      => array('GD�?', '2.0', '2.0+', '未知', 'success'),
			'disk'    => array('磁盘空间', '5M', '不限�?', '未知', 'success'),
		);

		//PHP环境�?�?
		if($items['php'][3] < $items['php'][1]){
			$items['php'][4] = 'error';
			session('error', true);
		}

		//数据库检�?
		// if(function_exists('mysql_get_server_info')){
		// 	$items['mysql'][3] = mysql_get_server_info();
		// 	if($items['mysql'][3] < $items['mysql'][1]){
		// 		$items['mysql'][4] = 'error';
		// 		session('error', true);
		// 	}
		// }

		//附件上传�?�?
		if(@ini_get('file_uploads'))
			$items['upload'][3] = ini_get('upload_max_filesize');

		//GD库检�?
		$tmp = function_exists('gd_info') ? gd_info() : array();
		if(empty($tmp['GD Version'])){
			$items['gd'][3] = '未安�?';
			$items['gd'][4] = 'error';
			session('error', true);
		} else {
			$items['gd'][3] = $tmp['GD Version'];
		}
		unset($tmp);

		//磁盘空间�?�?
		if(function_exists('disk_free_space')) {
			$items['disk'][3] = floor(disk_free_space(INSTALL_APP_PATH) / (1024*1024)).'M';
		}

		return $items;
	}

	/**
	 * 目录，文件读写检�?
	 * @return array �?测数�?
	 */
	public function check_dirfile(){
		$items = array(
			array('dir',  '可写', 'success', './Upload'),
			array('dir',  '可写', 'success', './DataBack'),
			array('dir',  '可写', 'success', './Public/Include'),
			array('dir',  '可写', 'success', './Application/Runtime'),
			array('dir',  '可写', 'success', './Application/Common/Conf'),
		);
		foreach ($items as &$val) {
			if('dir' == $val[0]){
				if(!is_writable(INSTALL_APP_PATH . $val[3])) {
					if(is_dir($items[1])) {
						$val[1] = '可读&nbsp;�?';
						$val[2] = 'error';
						session('error', true);
					} else {
						$val[1] = '不存�?&nbsp;×';
						$val[2] = 'error';
						session('error', true);
					}
				}
			} else {
				if(file_exists(INSTALL_APP_PATH . $val[3])) {
					if(!is_writable(INSTALL_APP_PATH . $val[3])) {
						$val[1] = '不可�?&nbsp;×';
						$val[2] = 'error';
						session('error', true);
					}
				} else {
					if(!is_writable(dirname(INSTALL_APP_PATH . $val[3]))) {
						$val[1] = '不存�?&nbsp;×';
						$val[2] = 'error';
						session('error', true);
					}
				}
			}
		}

		return $items;
	}
}