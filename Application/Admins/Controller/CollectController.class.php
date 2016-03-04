<?php
namespace Adnims\Controller;
use Think\Controller;
class CollectController extends CommonController{
	public $colldemo = array(
		'doyo_pic' => array(
			'title'  => 'doyo图片',
			'type'   => 'pic',
			'url'	 => 'http://www.doyo.cn/tu',		
		),
		'7k7k_flash' => array(
			'title'  => '7k7k小游戏',
			'type'   => 'flash',
			'url'	 => 'http://www.7k7k.com/tag/71/new.htm',			
		),
	);
	protected function _initialize(){
		parent :: _initialize();
			$this->assign('colldemo',$this->colldemo);
		}
		public function index(){
			$Collect = D('Xhttp');
			$url = 'http://www.2114.com/news/news_1.shtml';
			$pagecode = $Collect->GetHttpPage($url);
			$looprule = '.gmain-list li';
			$regcode = array("url"=>array('h4 a',"href"));
			$ArrUrls = $Collect->GetArray($pagecode,$regcode,$looprule);
			dump($ArrUrls);
			exit;
			$tempUrl = $Collect->FormatUrl($ArrUrls[1]['url'],$url);
			$pageCode = $Collect->GetHttpPage($tempUrl);
			$regcode = array();
			$regcode['title'] = array('.gbox30 h3','text');
			$regcode['time'] = array('.gbox30 .wrt .date','text');
			$regcode['content'] = array('.con-main','html');
			$result = $Collect->GetBody($pageCode,$regcode);
			$content = $Collect->FilterScript($result['content']);
			$regEx = '<h2 class="relevant-t">[#]</h2>';
			$jieguo = $Collect->GetBody($pageCode,$regEx,1);
			dump($jieguo);
	}
	
    public function onepage(){
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$assign['meta_title'] = '单页面采集';
		$assign['colldemo'] = $this->colldemo;
		$this->assign($assign);
		$this->display('onepage');
    }
	
	 public function collsingle(){
		$collurl = I('post.collurl','');
		$sitetype = I('post.sitetype','');
		$colldemo = $this->colldemo;
		$funname = 'rule_'.$sitetype;
		$coll_result = $this->$funname($pagecode,$sitetype,$collurl);
		$assign['meta_title'] = '内容采集结果';
		$assign['collect_type'] = $colldemo[$sitetype];
		$assign['coll_result'] = $coll_result;
		$assign['collurl'] = $collurl;
		$template = 'demo_'.$colldemo[$sitetype]['type'];
		/*输入模板内容*/
		$this->assign($assign);		
		$this->display($template);
    }
	/**
	 * 目标网站采集规则
	**/
	
	public function rule_doyo_pic($pagecode,$sitetype,$collurl){
		$Collect  = D('Xhttp');
		$colldemo = $this->colldemo;
		$pagecode = $Collect->GetHttpPage($collurl);
		//图片
		$regcode  = array();
		$regcode['picurl']  = array("'[#]'",'text');
		$result  = $Collect->GetArray($pagecode,$regcode,'var picture_list[#]var small_list',1,1);
		//标题等信息
		$regex  = array();
		$regex['title'] = array('#picture_title_box h1','text');
		$regex['tags'] = array('#picture_bottom_bar .tag span','text');
		$Title_arr = $Collect->GetBody($pagecode,$regex);
		
		$result = array_merge($result,$Title_arr);
		return $result;
	}
	
	public function rule_7k7k_flash($pagecode,$sitetype,$collurl){
		$Collect  = D('Xhttp');
		$colldemo = $this->colldemo;
		$pagecode = $Collect->GetHttpPage($collurl);
		$regcode = array();
		$regcode['title'] = array('#game-info h1 a','text');
		$regcode['pic'] = array('#game-info .img-mask img','src');
		$regcode['payurl'] = array('.play-operate .ui-play-btn','href');
		$regcode['jieshao'] = array('.game-describe','text');
		$regcode['caozuo'] = array('.play-step .howto','html');
		$regcode['kaishi'] = array('.play-step .item:eq(1) p','text');
		$regcode['mubiao'] = array('.play-step .item:eq(2) p','text');
		$regcode['label'] = array('.game-tag','text');
		$regcode['classname'] = array('.game-meta .item:eq(1)','text');
		$regcode['gamesize'] = array('.game-meta .item:eq(2)','text');
		$result = $Collect->GetBody($pagecode,$regcode);
		$playurl = $result['payurl'];
		if ($playurl){
			$payurl = $Collect->FormatUrl($playurl,$collurl);
			$playpage = $Collect->GetHttpPage($payurl);
			$regnei = array();
			$regnei['width'] = array('_gamewidth =[#],','text');
			$regnei['height'] = array('_gameheight =[#],','text');
			$regnei['swfurl'] = array('_gamepath = "[#]"','text');
			$result_nei = $Collect->GetBody($playpage,$regnei,1);
		}
		$result = array_merge($result,$result_nei);
		return $result;
	}	
}