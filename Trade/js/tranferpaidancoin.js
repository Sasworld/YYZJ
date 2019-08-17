// 排单币转账
function paidancoin_tranfer(){
	my_validate("text_paidan_uname", "请确定转账对象");
	my_validate("text_paidan_coin", "请输入转账数量");
	var to_uname = $('#text_paidan_uname').val();
	var to_amount = $('#text_paidan_coin').val();
	if(!checkRate(to_amount)){
		throw SyntaxError(); //如果验证不通过，则不执行后面
	}else{
		if(confirm("确定转帐" + to_amount + "个排单币给：" + to_uname + "？")){
			var curr_user = get_curr_user();
			var curr_username = get_curr_uname();
			if(curr_username == to_uname){
				alert("不能对自己转账");
			}else{
				showTip("正在转账...");
				var user_info = {"flags":"5", "uid":curr_user, "to_amount":to_amount, "uname":to_uname};
				$.ajax({
			        type: "POST",
			        url: "php/paidancoin.php" ,
			        data: {datas:JSON.stringify(user_info)},
			        dataType: "json",
			        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
			        success: function(json) {
			        	closeTip();
			        	alert(json.result);
			        	$('#text_paidan_uname').val("");
			        	$('#text_paidan_coin').val("");
			        	window.location.reload();
			        }
			    });
			}
		}else{
			$('#text_paidan_uname').val("");
			$('#text_paidan_coin').val("");
		}
	}
}

// 查询排单币数量
function get_paidan_coin(){
	var curr_user = get_curr_user();
	var user_info = {"flags":"2", "uid":curr_user, "queryid":"1"};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	$('#paidan_coin_amount').text(json.paidancoin + "个");
        }
    });
}