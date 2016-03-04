<?php
namespace Adnims\Controller;
use Think\Db;
use Think\Controller;
use OT\Database;
class DatabaseController extends CommonController{	
	/*数据表列表*/
	public function backup(){
		$Db    = Db::getInstance();
		$list  = $Db->query('SHOW TABLE STATUS');
		$list  = array_map('array_change_key_case',$list);
		
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('list', $list);
		$this->assign('meta_title','数据备份');
		$this->display('backup');
	}
	
	/*备份文件列表*/
	public function reduction(){
		$path = realpath(C('DATA_BACKUP_PATH'));
		$flag = \FilesystemIterator::KEY_AS_FILENAME;
		$glob = new \FilesystemIterator($path,$flag);	
		$list = array();
		foreach ($glob as $name => $file) {
			if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
				$name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
				$date = "{$name[0]}-{$name[1]}-{$name[2]}";
				$time = "{$name[3]}:{$name[4]}:{$name[5]}";
				$part = $name[6];
				if(isset($list["{$date} {$time}"])){
					$info = $list["{$date} {$time}"];
					$info['part'] = max($info['part'], $part);
					$info['size'] = $info['size'] + $file->getSize();
				} else {
					$info['part'] = $part;
					$info['size'] = $file->getSize();
				}
				$extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
				$info['compress'] = ($extension === 'SQL') ? '-' : $extension;
				$info['time']     = strtotime("{$date} {$time}");
	
				$list["{$date} {$time}"] = $info;
			}
		}

		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('list', $list);
		$this->assign('meta_title','数据备份');
		$this->display('reduction');
	}	
	
	/* 数据还原 */
    public function import($time = 0, $part = null, $start = null){
        if(is_numeric($time) && is_null($part) && is_null($start)){ //初始化
            //获取备份文件信息
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR . $name;
            $files = glob($path);
            $list  = array();
            foreach($files as $name){
                $basename = basename($name);
                $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
                $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
                $list[$match[6]] = array($match[6], $name, $gz);
            }
            ksort($list);

            //检测文件正确性
            $last = end($list);
            if(count($list) === $last[0]){
                session('backup_list', $list); //缓存备份列表
                $this->success('初始化完成！', '', array('part' => 1, 'start' => 0));
            } else {
                $this->error('备份文件可能已经损坏，请检查！');
            }
        } elseif(is_numeric($part) && is_numeric($start)) {
            $list  = session('backup_list');
            $db = new Database($list[$part], array(
                'path'     => realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR,
                'compress' => $list[$part][2]));

            $start = $db->import($start);

            if(false === $start){
                $this->error('还原数据出错！');
            } elseif(0 === $start) { //下一卷
                if(isset($list[++$part])){
                    $data = array('part' => $part, 'start' => 0);
                    $this->success("正在还原...#{$part}", '', $data);
                } else {
                    session('backup_list', null);
                    $this->success('<font color="#008000">还原完成！</font>');
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if($start[1]){
                    $rate = floor(100 * ($start[0] / $start[1]));
                    $this->success("正在还原...#{$part} ({$rate}%)", '', $data);
                } else {
                    $data['gz'] = 1;
                    $this->success("正在还原...#{$part}", '', $data);
                }
            }

        } else {
            $this->error('参数错误！');
        }
    }
	
	/**
     * 优化表
     * 修复表
     */	
	public function resort(){
		$action   = trim(I('request.action'));
		$tables   = I('tables');
		if ($action =='optimize'){
			//优化表
			if($tables){
				$Db   = Db::getInstance();
				if(is_array($tables)){
					$tables = implode('`,`', $tables);
					$list = $Db->query("OPTIMIZE TABLE `{$tables}`");
					if($list){
						$this->success("所选数据表优化完成！");
					} else {
						$this->error("数据表优化出错请重试！");
					}
				} else {
					$list = $Db->query("OPTIMIZE TABLE `{$tables}`");
					if($list){
						$this->success("数据表'{$tables}'优化完成！");
					} else {
						$this->error("数据表'{$tables}'优化出错请重试！");
					}
				}		
			}else{
				$this->error("请选择需要优化的数据表");
			}
		}elseif($action =='repair'){
			//修复表
			if($tables){
				$Db   = Db::getInstance();
				if(is_array($tables)){
					$tables = implode('`,`', $tables);
					$list = $Db->query("REPAIR TABLE `{$tables}`");
					if($list){
						$this->success("所选数据表修复完成！");
					} else {
						$this->error("数据表修复出错请重试！");
					}
				} else {
					$list = $Db->query("REPAIR TABLE `{$tables}`");
					if($list){
						$this->success("数据表'{$tables}'修复完成！");
					} else {
						$this->error("数据表'{$tables}'修复出错请重试！");
					}
				}		
			}else{
				$this->error("请选择需要修复的数据表");
			}			
		}elseif($action == 'backup'){
			// 开始备份数据库
			$id = I('id');
			$start = I('start');
			if(IS_POST && !empty($tables) && is_array($tables)){ //初始化
				//读取备份配置
				$config = array(
					'path'     => realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR,
					'part'     => C('DATA_BACKUP_PART_SIZE'),
					'compress' => C('DATA_BACKUP_COMPRESS'),
					'level'    => C('DATA_BACKUP_COMPRESS_LEVEL'),
				);
	
				//检查是否有正在执行的任务
				$lock = $config['path']."backup.lock";
				if(is_file($lock)){
					$this->error('检测到有一个备份任务正在执行，请稍后再试！');
				} else {
					//创建锁文件
					file_put_contents($lock,NOW_TIME);
				}
				//检查备份目录是否可写
				is_writeable($config['path']) || $this->error('备份目录['.$config['path'].']不存在或不可写，请检查后重试！');
				session('backup_config',$config);
	
				//生成备份文件信息
				$file = array(
					'name' => date('Ymd-His', NOW_TIME),
					'part' => 1,
				);
				session('backup_file', $file);
	
				//缓存要备份的表
				session('backup_tables', $tables);
	
				//创建备份文件
				$Database = new Database($file, $config);
				if(false !== $Database->create()){
					$tab = array('id' => 0, 'start' => 0);
					$this->success('初始化成功！','',array('tables' => $tables, 'tab' => $tab));
				} else {
					$this->error('初始化失败，备份文件创建失败！');
				}
			}elseif (IS_GET && is_numeric($id) && is_numeric($start)) { //备份数据
				$tables = session('backup_tables');
				//备份指定表
				$Database = new Database(session('backup_file'), session('backup_config'));
				$start  = $Database->backup($tables[$id], $start);
				if(false === $start){ //出错
					$this->error('备份出错！');
				} elseif (0 === $start) { //下一表
					if(isset($tables[++$id])){
						$tab = array('id' => $id, 'start' => 0);
						$this->success('<font color="#008000">备份完成！</font>', '', array('tab' => $tab));
					} else {//备份完成，清空缓存
						unlink(session('backup_config.path') . 'backup.lock');
						session('backup_tables', null);
						session('backup_file', null);
						session('backup_config', null);
						$this->success('<font color="#008000">备份完成！</font>');
					}
				} else {
					$tab  = array('id' => $id, 'start' => $start[0]);
					$rate = floor(100 * ($start[0] / $start[1]));
					$this->success("正在备份...({$rate}%)", '', array('tab' => $tab));
				}
			}else {//出错
				$this->error('备份参数错误！');
			}
			// 开始备份数据库			
		}else{
			$this->error("您没有用选择任何操作选项，请返回重新选择后再提交!",back_url,3);
		}		
	}
	
	public function del(){
		$time = I('time');
		if($time){
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR . $name;
            array_map("unlink",glob($path));
            if(count(glob($path))){
                $this->error('备份文件删除失败，请检查权限！');
            } else {
                $this->success('数据库备份文件删除成功！',back_url);
            }
        } else {
            $this->error('参数获取错误，请返回重新选择！',back_url);
        }
	}
}