$(function(){
	var api_layer = '<div class="newsbox">';
	api_layer += '<div class="sonews">';
	api_layer += '<input type="text" name="newskey" id="newskey" class="newskey" placeholder="输入标题关键词查找">';
	api_layer += '<select name="socateid" id="socateid" class="socateid">';
	for (var i=0;i<trees.length;i++){
	api_layer += '<option value="'+trees[i]['id']+'">'+trees[i]['title_show']+'</option>';
	}
	api_layer += '</select>';
	api_layer += '<input type="button" name="sobtn" id="sobtn" class="sobtn" value="搜索图文" onclick="sowapdata('+id+')" />';
	api_layer += '</div>';
	api_layer += '<div id="so_result" class="so_result"></div>';
	api_layer += '<div class="botton_close"><input type="button" name="queding" id="closeBtn" class="queding" value="确定"/></div>';
	api_layer += '</div>';
	$("#selectMore").bind("click",function(){		
		var pagei = $.layer({
			type:1,
			title:['选择关联图文集','background:ccc'],
			border:[4,0.5,'#999'], 
			move:false,
			shadeClose: true,
			closeBtn:[0,true],
			area: ['480px','313px'],// 控制层宽高 当设置为auto时，意味着采用自适应（iframe层不能设置auto）, 对于宽度，并不推荐您设置auto
			page: {html:api_layer},
		});
		//载入图文信息			
		load_wapnews(id,cateid,'',1,ispush);
		$('#closeBtn').on('click', function(){
			layer.close(pagei);
		});	
	});
});

	function load_wapnews(infoid,cateid,sokey,page,ispush){
		$.get('/adnims/ajax/loadnews',{'infoid':infoid,'cateid':cateid,'sokey':sokey,'page':page,'ispush':ispush},function(re){$('#so_result').html(re)},'html');
	}
	
	function sowapdata(ids){
		var sokey = $('#newskey').val();
		var infoid = $('#infoid').val();
		var socateid = $('#socateid').val();
		load_wapnews(ids,socateid,sokey,1,1);
	}
	
	function choseid(re){
		var chkvalue = re.value.split('###');
		if (re.checked){			
			reloadnewsid(chkvalue[0],1,chkvalue[1],chkvalue[2]);
		}else{
			reloadnewsid(chkvalue[0],0,chkvalue[1],chkvalue[2]);
		}
	}
	function removeli(rowid){
		var oldids = $('#relates').val();
		arrids = oldids.split(',');
		var newvalue = '';
		for (var i = 0; i < arrids.length; i++){
			var chkids =  arrids[i];
			if (rowid != chkids){
			newvalue = newvalue + ',' + chkids;
			}
		}
		if (newvalue.indexOf(',')==0){newvalue = newvalue.substr(1)}
		$('#row_'+rowid).remove();
		$('#relates').val(newvalue);
	}
	
	function addrowli(ids,catename,title){
		var addhtml = '<li id="row_'+ids+'"><span onclick="removeli('+ids+')" title="移除文档"></span><font color="#008000">['+catename+']</font> '+title+'</li>';
		$('#newsids').prepend(addhtml).show();
	}
	
	function reloadnewsid(ids,type,catename,title){
		var oldids = $('#relates').val();
		if (oldids ==''){
			if (type == 1){
				$('#relates').val(ids);
				addrowli(ids,catename,title);
			}
		}else{
			if (type == 1){
				var newids = '';
				arrids = oldids.split(',');
				if (arrids.length > 9){
					alert('最多只能选择10个相关文档');
					return false;
				}
				for (var i = 0; i < arrids.length; i++){
					var chkids =  arrids[i];
					if (ids != chkids){
						newids = oldids + ',' + ids;
					}else{
						return false;
					}
				}
				addrowli(ids,catename,title);
				if (newids.indexOf(',')==0){newids = newids.substr(1)}
				$('#relates').val(newids);
			}else{
				removeli(ids);
			}
		}
	}

