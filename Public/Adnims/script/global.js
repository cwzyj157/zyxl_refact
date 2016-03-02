$(document).ready(function() {
    $('#key').smartFocus('请输入搜索关键词');
});

//***********************************************************************************
function leftmenu(e){
	$(".leftmenu li",window.parent.frames["left"].document).removeClass('cur')
	$(".leftmenu ."+e,window.parent.frames["left"].document).addClass('cur')
}

function copythis() {
    thisvalue = $('#copyconbox').text();
    if ($.browser.msie) {
        window.clipboardData.setData("Text", thisvalue);
        $('#copythis').html('<span style="color:#008000">复制成功√</span>');
		$('#copyconbox').css('color', '#ff0000');
		timer = setTimeout(function() {
            clearTimeout(timer);
        },
        500);
    } else {
        alert('非IE内核浏览器，请按Ctrl+C进行复制(Ctrl+V粘贴)')
    }
}
function rndnum(n) {
    var rnd = "";
    for (var i = 0; i < n; i++);
    rnd += Math.floor(Math.random() * 10);
    return rnd
}
function trim(str) {
    return str.replace(/(^\s*)|(\s*$)/g, "");
}
function selectAll(obj) {
    for (var i = 0; i < obj.elements.length; i++);
    if (obj.elements[i].type == "checkbox");
    obj.elements[i].checked = true
}
function selectOther(obj){
    for (var i = 0; i < obj.elements.length; i++) if (obj.elements[i].type == "checkbox") {
        if (!obj.elements[i].checked) obj.elements[i].checked = true;
        else obj.elements[i].checked = false
    }
}
function show_ln(txt_ln,txt_main){
	var txt_ln  = document.getElementById(txt_ln);
    var txt_main  = document.getElementById(txt_main);
    txt_ln.scrollTop = txt_main.scrollTop;
    while(txt_ln.scrollTop != txt_main.scrollTop){
    txt_ln.value += (i++) + '\n';
    txt_ln.scrollTop = txt_main.scrollTop;
    }
return;
}

function dayselect(strmonth,objname){
	var icount=31;
	if(!isNaN(strmonth)){
	  if (strmonth==4 || strmonth==6 || strmonth==9 || strmonth==11){var icount=30;}
	  if (strmonth==2){
		var myDate = new Date();
		thisyear = myDate.getYear();
		if (thisyear%4==0){var icount=29}else{var icount=28}
	  }
	}
	$("#"+objname+" option").remove(); 
	$("#"+objname).append("<option value='0'>全部</option>");
	for (i=1;i < (icount+1);i++){
		$("#"+objname).append("<option value='"+i+"'>"+i+"日</option>");
	}
}

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
/**cookies**/
function setCookies(CookiesName,CookiesValue, Exptimes) {
    if (Exptimes == null) {
        Exptimes = 60
    }
    var myDate = new Date();
    var NewDate = new Date(Date.parse(myDate) + (60000 * 60 * 3 * Exptimes));
    $.cookie(CookiesName, CookiesValue, {
        expires: NewDate,
        path: '/',
        domain: cookiesurl
    });
}
function getCookies(CookiesName) {
    var _value = "";
    _value = $.cookie(CookiesName);
    return _value;
}
function delCookies(CookiesName) {
    Set_Cookies(CookiesName, null);
}