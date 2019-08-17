// 获取用户信息
function get_user_info(){
	var curr_user = get_curr_user();
//	flags:2 查询用户表  queryid:2 查询多个信息
	var user_info = {"flags":"2", "uid":curr_user, "queryid":"2"};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	if(json.result == "1"){
        		$('#span_uname').text(json.uname);
        		$('#span_uid').text(json.uid);
        		$('#span_regdate').text(json.regdate);
        		$('#span_bank').text(json.bank);
        		$('#span_banknumber').text(json.banknumber);
        		$('#span_owner').text(json.ownername);
        		$('#span_alipay').text(json.alipay);
        	}else{
        		alert("加载失败，请刷新");
        	}
        }
    });
}
