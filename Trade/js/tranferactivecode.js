// 激活码转账
function acode_tranfer(){
	my_validate("text_to_uname", "请确定转账对象");
	my_validate("text_to_active_code", "请输入转账数量");
	var to_uname = $('#text_to_uname').val();
	var to_amount = $('#text_to_active_code').val();
	if(!checkRate(to_amount)){
		throw SyntaxError(); //如果验证不通过，则不执行后面
	}else{
		if(confirm("确定转帐" + to_amount + "个激活码给：" + to_uname + "？")){
			var curr_user = get_curr_user();
			var curr_username = get_curr_uname();
			if(curr_username == to_uname){
				alert("不能对自己转账");
			}else{
				showTip("正在转账...");
				var user_info = {"flags":"4", "uid":curr_user, "to_amount":to_amount, "uname":to_uname};
				$.ajax({
			        type: "POST",
			        url: "php/activecode.php" ,
			        data: {datas:JSON.stringify(user_info)},
			        dataType: "json",
			        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
			        success: function(json) {
			        	closeTip();
			        	alert(json.result);
			        	$('#text_to_uname').val("");
			        	$('#text_to_active_code').val("");
			        	window.location.reload();
			        }
			    });
			}
		}else{
			$('#text_to_uname').val("");
			$('#text_to_active_code').val("");
		}
	}
}
// 查询激活码数量
function get_ac_code(){
	var curr_user = get_curr_user();
	var user_info = {"flags":"17", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	$('#active_code_amount').text(json.activecode + "个");
        }
    });
}