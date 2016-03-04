/*选择相关文章*/
	function load_data(infoid,cateid,sokey,page,ispush){
		$.get('/Admins/ajax/loaddata',{'infoid':infoid,'cateid':cateid,'sokey':sokey,'page':page},function(re){$('#so_result').html(re)},'html');
	}
	
	function sodata(ids){
		var sokey = $('#newskey').val();
		var infoid = $('#id').val();
		var socateid = $('#socateid').val();
		load_data(ids,socateid,sokey,1);
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

