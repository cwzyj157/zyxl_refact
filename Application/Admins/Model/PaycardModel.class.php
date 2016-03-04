<?php
namespace Admins\Model;
use Think\Model;
class PaycardModel extends Model {
	public function getCardstatus($status=0){
		switch ($status){
			case 0 : return '等待上架'; break;
			case 1 : return '未充�?' ; break;
			case 2 : return '已充�?' ; break;
			case 3 : return '已过�?' ; break;
			default : return '未知状�??';break;	
		}
		return $tmp;
	}
	
	public function addPaycard($type=0,$totalnum=10,$limitday=30){
		$mModel = M('Paycard');
		for ($i==0;$i<$totalnum;$i++){
			$cardnum  = strtoupper($this->build_authKey(24));
			$carspwd  = $this->build_authKey(16);
			$P_TYPE   = C('PAYCARD_TYPE');
			$money    = $P_TYPE[$type];
			$overtime = strtotime("+{$limitday} days");
			if ($this->chkCardnum($cardnum)){
				$newData = array(
					'cardnum'     => $cardnum,
					'cardpwd'     => $carspwd,
					'money'       => $money,
					'type'        => $type,
					'userid'      => 0,
					'paytime'     => 0,
					'overtime'    => $overtime,
					'create_time' => time(),
					'status'      => 0,		
				);
				$mModel -> add($newData);
			}			
		}		
		return true;
	}
	
	/*
	* 判断充�?�卡是否存在
	* 确保充�?�卡卡号是唯�? 
	*/
	public function chkCardnum($cardnum){
		if ($cardnum){
			$map = array('cardnum'=>$cardnum);
			$chk = M('Paycard')->where($map)->count();
			if ($chk > 0){
				return false;
			}else{
				return true;
			}
		}else{
			return false;
		}	
	}
	/**
	 * 生成系统AUTH_KEY
	 */
	public function build_authKey($length=30){
		$chars  = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$chars  = str_shuffle($chars);
		return substr($chars,0,$length);
	}
}