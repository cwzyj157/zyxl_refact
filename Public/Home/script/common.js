$(function() {
    /*
	$(".pageGgo").on("click",function(){
        var skippage=parseInt($("#skipnum").val());
        var url=$(this).attr("data");
        if (isNaN(skippage)) {
            skippage = 1;
        };
        url=url.replace("#P#",skippage);
        window.location.href=url;
    });	
	*/
	$("#favorite").on("click",function(){
        var data = $(this).attr('datatype').split('##');
		if(data.length != 2){
			return false;
		}else{
			addfavorite(data[0],data[1]);
		}
    });	
})

function checksoform(){
	var keyword = $('#key').val();
	if(keyword == ''){
		showmsgbox('请输入搜索关键词',0,'none');
		return false;
	}
}