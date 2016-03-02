//鼠标事件
$(function(){
	//更换验证码
	$('#verifyImg').on('click',function(){
		changeVerifyi('verifyimg');
	});
	
	$('.confirm').click(function(){
        var that = this;
		if(!confirm('确认要删除该记录吗?')){
			return false;
		}
	});
	//退出登录
	$('#uLogout').on('click',function(){
		Ajaxlogout();
	});
});

//页面验证码
function changeVerifyi(objname){
	var verifyUrl = $('#'+objname).attr('src')+"?_" + new Date();
	$('#'+objname).attr('src',verifyUrl);
}


// layer插件提示框
function showmsgbox(msg,type,url,time){
	//$('.xubox_shade,.xubox_layer').remove();
	//type 0-黄色叹号 1-绿色对钩 2-红色叉 3-蓝色问号 4-红色锁 5-红色哭脸 6-绿色笑脸
	if (!isExitsVariable(type)){type = 1;}
	if (!isExitsVariable(time)){time = 3;}
	if (!isExitsVariable(url) || url==''){url = 'reload';}
	layer.alert(msg,
		{icon:type,shade:[0.5, '#000000'],title: ['提示信息', 'font-size:16px;'],area:'auto',maxWidth:660,closeBtn:false,},
		function(index){
			switch(url){
				case 'reload':
					location.reload();
					break;
				case 'none':
					layer.close(index);
					break;
				default:
					window.location.href = url;
					break;
			}			
		}
	);
}

// layer插件消息框
function showmsgbox_back(msg,type,url,time){
	//$('.xubox_shade,.xubox_layer').remove();
	//type 0-黄色叹号 1-绿色对钩 2-红色叉 3-蓝色问号 4-红色锁 5-红色哭脸 6-绿色笑脸
	if (!isExitsVariable(type)){type = 1;}
	if (!isExitsVariable(time)){time = 3;}
	if (!isExitsVariable(url) || url==''){url = 'reload';}
	layer.msg(
		msg,
		{icon:type,time:time*1000,shade:[0.4, '#000000'],shadeClose:true,area:'auto',maxWidth:660,closeBtn:false,},
		function(index){
			switch(url){
				case 'reload':
					location.reload();
					break;
				case 'none':
					break;
				default:
					window.location.href = url;
					break;
			}			
		}
	);
}

// layer插件提示框
function showalertbox(msg,type,url,time){
	//$('.xubox_shade,.xubox_layer').remove();
	//type 0-黄色叹号 1-绿色对钩 2-红色叉 3-蓝色问号 4-红色锁 5-红色哭脸 6-绿色笑脸
	if (!isExitsVariable(type)){type = 1;}
	if (!isExitsVariable(time)){time = 3;}
	if (!isExitsVariable(url) || url==''){url = 'reload';}
	layer.alert(msg,
		{icon:type,time:time*1000,shade:[0.5, '#000000'],shadeClose:true,area:'auto',maxWidth:660,closeBtn:false,},
		function(index){
			switch(url){
				case 'reload':
					location.reload();
					break;
				case 'none':
				alert(2)
					layer.close(index);
					break;
				default:
					window.location.href = url;
					break;
			}			
		}
	);
}


//layer插件加载iframe
function loadpage(url,width,height){	
	layer.open({
		type: 2,
		title:false,
		closeBtn: true,
		shade:[0.5,'#000'],
		shadeClose: true,
		zIndex: layer.zIndex,
		area: [width+'px',height+'px'],
		content:url
	}); 
}		

//layer插件加载层
function showlayer(title,content,width){
	if (!isExitsVariable(width)){width = 640;}
	layer.open({
		type: 1,
		title: [title, 'font-size:16px;color:#b00000'],
		shadeClose: false,
		area: [width+'px','auto'], //宽高
		skin: 'layui-layer-rim', //加上边框
		content:content
	});
}


//js变量容错
function isExitsVariable(variableName){
    try {
        if (typeof(variableName) == "undefined") {
            return false;
        } else { 
            return true;
        }
    } catch(e) {}
    return false;
}

/*将字符串格式化为保留小数*/
function formatFloat(src,pos){ 
    return Math.round(src*Math.pow(10, pos))/Math.pow(10, pos); 
} 

/*元素固定在页面顶部
 * $('#demo').smartFloat();
*/
$.fn.smartFloat = function(){
	var position = function(element) {
	var top = element.position().top, pos = element.css("position");
	$(window).scroll(function() {
		var scrolls = $(this).scrollTop();
		if (scrolls > top){
			if (window.XMLHttpRequest) {
				element.css({
				position: "fixed",
				top: 0
				});	
			}else{
				element.css({
				top: scrolls
				});	
			}
		}else{
			element.css({
			position: pos,
			top: top
			});	
		}
	});
	};
	return $(this).each(function(){position($(this));});
};

//输入框默认文字
jQuery.fn.smartFocus = function(text) {
    $(this).val(text).focus(function() {
        if ($(this).val() == text) {
            $(this).val('');
        }
    }).blur(function() {
        if ($(this).val() == '') {
            $(this).val(text);
        }
    });
};

//返回顶部
function goScroll(objname){
	if (!isExitsVariable(objname)){objname = 'index';}
	if (objname == 'index' || objname == ''){
		$("html,body").animate({scrollTop:0},500);
	}else{
		$("html,body").animate({scrollTop:$('#'+objname).offset().top},500);
	}
}
//收藏夹
function addfavorite(sURL,sTitle) {
	try {
		window.external.addFavorite(sURL, sTitle);
	}
	catch (e) {
		try {
			window.sidebar.addPanel(sTitle, sURL, "");
		}
		catch (e) {
			alert("您的浏览器不支持一键收藏\n请按【Ctrl+D】将本站添加到收藏夹");
		}
	}
}