$(function(){
	//全选&反选
	$(".check_all").click(function(){
		$(".select_id").prop("checked",this.checked);
	});	
	$(".select_id").click(function(){
		var option = $(".select_id");
		option.each(function(i){if(!this.checked){$(".check_all").prop("checked", false);return false;}else{$(".check_all").prop("checked", true);}});
	});	
	//input效果
	$('.f_input').focus(function(){$(this).removeClass("f_error").addClass("f_input_cur");});
	//删除操作询问提示
	$('.confirm').click(function(){
        var that = this;
		if(!confirm('确认要执行该操作吗?')){
			return false;
		}
	});
})
function selectthis(obj){
	$(obj).select();
}

function ShowUrlTr(){
	if ($("#label_j").is(":checked")){
		$('#returnurl').show();
	}else{
		$('#goourl').val('');
		$('#returnurl').hide();
	}
}
	
//导航高亮
function highlight_subnav(url){
    $('.lefumenuBox').find('a[href="'+url+'"]').addClass('cur');
}

function form_inputMsg(objname,msg,status){
	$('#'+objname).addClass('f_error');
	$('#tip_'+objname).addClass('t_error').html('( * '+msg+' )');	
}
//搜索游戏
function so_game(objname){
	var sokey = $("#sokey").val();
	var gametype = $("#gametype").val();
	$.get('index.php?m=Admins&c=ajax&a=loadgame',{'sokey':sokey,'gametype':gametype},function(re){changelocation(re,objname);},'json');
}
function changelocation(subcat,objname){
	if (subcat.length ==0 ){
		$("#"+objname).empty();
		$("#"+objname).append("<option value='0'>未找到游戏</option>");
		alert('未找到合适游戏');
		return false;
	}else{
		$("#"+objname).empty();
		$("#"+objname).append("<option value='0'>在结果中选择游戏</option>");
		for (var i=0;i<subcat.length;i++){
			$("#"+objname).append("<option value='"+subcat[i]['id']+"'>"+subcat[i]['title']+"</option>"); 
		}	
	}	
}
//选择Tags标签
function selectTag(even,tagid,tagname){
	var oldtags = $('#tagtext').val();
	if (oldtags.length > 0){
		var arrtag = oldtags.split(' ');
		var newtags='';
		var result = false;
		var j = 0;
		for (var i=0;i<arrtag.length;i++){
			if (arrtag[i] == tagname){ //移除标签
				result = true;
			}else{
				j ++;
				if (j > 4){
					alert('最多指定5个Tag');
					return false;
				}
				if (newtags.length>0){
					newtags += ' '+arrtag[i];
				}else{
					newtags = arrtag[i];
				}
			}
		}
		if (result){
			$(even).removeClass('cur');
		}else{
			if (newtags.length>0){
				newtags += ' '+tagname;
			}else{
				newtags = tagname;
			}
			$(even).addClass('cur');
		}	
		$('#tagtext').val(newtags);	
		$('#choosetag').html(newtags);
	}else{
		$(even).addClass('cur');
		$('#tagtext').val(tagname);
		$('#choosetag').html(tagname);
	}
}

/*锁定已选择的标签*/
function loadchecked(){
	var checktag = $('#tagtext').val();
	if (checktag.length > 0){
		var checkcode = ' '+checktag+' ';
		$('.tagsbox').find("li").each(function(i){
			var rowdata = $(this).attr('data');
			var value = checkcode.indexOf(rowdata);
			if (value >= 0){
				$(this).addClass('cur');
			}
		});
	}
	$('#choosetag').html(checktag);
}

function showlayer(title,content,width){
	if (!isExitsVariable(width)){width = 640;}
	layer.open({
		type: 1,
		title: [title, 'font-size:16px;'],
		shadeClose: true,
		area: [width+'px','auto'], //宽高
		skin: 'layui-layer-rim', //加上边框
		content:content
	});
}

function copyInput(inputname,tipname){
	var inputValue=$('#'+inputname).val();
	init_clipboard(inputValue,tipname,function(){
	$('#'+tipname).text('复制成功').css({'color':'#ffff00'});
	$('#'+inputname).css({'color':'#008000'});
	});
}

function copySpan(inputname,tipname){
	var inputValue=$('#'+inputname).html();
	init_clipboard(inputValue,tipname,function(){
	$('#'+tipname).text('复制成功').css({'color':'#ffff00'});
	$('#'+inputname).css({'color':'#008000'});
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
