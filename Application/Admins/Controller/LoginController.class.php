<?php
namespace Admins\Controller;
use Think\Controller;
use Org\ConfigHelper;
use Org\ValidateCodeHelper;

class LoginController extends CommonController{
	protected function _initialize()
    {
        $config = S('DB_CONFIG_DATA');
        if ($config == false) {
            $configHelper = new ConfigHelper();
            $config = $configHelper->get_config_lists();
            S('DB_CONFIG_DATA', $config);
        }
        // 添加配置
        C($config);
        $this->assign('WEB_URL', C('WEB_URL'));
        $this->assign('WEB_NAME', C('WEB_NAME'));
    }

	public function index()
	{
		$this->display('Index:login');
	}

	public function login(){
		if (IS_POST){
			$user_name = trim(I('post.uname'));
			$user_pass = trim(I('post.upass'));
			$user_code = strtolower(trim(I('post.ucode')));
			$true_Code  = $_SESSION['koOuEswCodeche'];
			if (strlen($user_name) < 4 || strlen($user_name) > 20){
				$this->error("管理员帐号必须在4位到20位之间，请返回重新输入!",U('Login/index'),3);
			}
			if (strlen($user_pass) < 6 || strlen($user_pass) > 20){
				$this->error("登录密码必须在6位到20位之间，请返回重新输入!",U('Login/index'),3);
			}
			if (strlen($user_code) !=4){
				$this->error("请输入4位的验证码!",U('Login/index'),3);
			}
			if ($user_code != $true_Code){
				$this->error("您输入的验证码不正确，请返回重新输入验证码!",U('Login/index'),3);
			}
			//验证数据库帐号信息
			$UserModel = M("adminer");
			$Where = ' username="'.$user_name.'"';
			$rs = $UserModel->where($Where)->find();
			if (!$rs){
				$this->error("用户名不存在，请返回重新输入!",U('Login/index'),3);
			}else{
				$data_pwd = $rs['password'];
				if ($data_pwd != md5($user_pass)){
					$this->error("管理员密码不正确，请返回重新输入!",U('Login/index'),3);
				}else{
					if ($rs['islocked']==1){
						$this->error("您的帐号已经被锁定登录，请联系管理员!",U('Login/index'),3);
					}
					//组成后台验证session值
					$u_session = array();
					$u_session['admin_id'] = $rs['id'];
					$u_session['admin_name'] = $rs['username'];
					$u_session['admin_truename'] = $rs['truename'];
					$u_session['login_last_time'] = $rs['last_login_time'];
					$u_session['login_last_ip'] = $rs['last_login_ip'];
					$u_session['login_times'] = $rs['login_times'];
					$u_session['check_KEY'] = md5($rs['id'].'|'.$rs['last_login_time'].'|'.$rs['last_login_ip']);
					//更新登录信息
					$data["last_login_time"] =time();
					if ($rs["last_login_ip"] != get_client_ip(1)){
						$data["last_login_ip"] = get_client_ip(1);
						$data['login_times'] = array('exp', 'login_times+1'); 
					}
       				$user_login = $UserModel ->where("id =".$rs['id'])->save($data);
					if (!$user_login){
						$this->error("系统繁忙，请稍后再试!",U('Index/login'),3);
					}else{
						$sessionName = C('SESSION_PREFIX');
						session($sessionName, $u_session);
						$this->success($rs['truename']."，欢迎您回来!",U('Index/index'),3);
						exit;
					}					
				}			
			}
		}else{
			$this->assign('WEB_URL',C('WEB_URL'));
			$this->display('Index/login');
		}
	}
	
	/*
	* 退出登录
	*/
	public function logout(){
		$sessionName = C('SESSION_PREFIX');
		session($sessionName,null);
		$this->success("您已经成功退出后台管理系统",U('Index/index'),3);
	}

	public function getValidateCode()
	{
		$validateCodeHelper = new ValidateCodeHelper();

		$checkcode = $validateCodeHelper->make_rand(4);

		session_start();//将随机数存入session中
		$_SESSION['koOuEswCodeche'] = strtolower($checkcode);

		$validateCodeHelper->getAuthImage($checkcode);
	}
}