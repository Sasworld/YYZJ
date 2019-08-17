var curr_user = "";
//获取用户本息总额
function get_principal_money(id, index){
	var curr_user = get_curr_user();
	var user_info = {"flags":index, "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
            if(json.result == "F"){
            	alert("发生未知错误，请稍后重试");
            }else{
            	$('#' + id).text(json.result);
            }
        }
    });
}

// 获取冻结资金
function get_freeze_money(){
	curr_user = get_curr_user();
	var user_info = {"flags":"6", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
            freeze_array = eval(json);
    		$("#table_trading_list tr:gt(0)").remove();
	      	for(var i = 0; i < freeze_array.length; ++i){
	  			json_obj = freeze_array[i];
	  			var shdate = new Date(json_obj.shdate.replace(/\-/g, '/')); // 打款时间
	  			var curr_date = new Date(json_obj.currdate.replace(/\-/g, '/')); // 当前日期
	  			var order_state = json_obj.orderstate; // 订单状态  1：表示未提现， 2：表示已经提现
	  			var pass_days = parseFloat((curr_date - shdate)/(24*3600*1000));
	  			shdate.setDate(shdate.getDate() + 10);
	  			var year = shdate.getFullYear();
			    var month = shdate.getMonth() + 1;
			    var day = shdate.getDate();
			    var hour = shdate.getHours();
			    var min = shdate.getMinutes();
			    var sec = shdate.getSeconds();
			    month = month < 10 ? ('0' + month) : month;
			    day = day < 10 ? ('0' + day) : day;
			    hour = hour < 10 ? ('0' + hour) : hour;
			    min = min < 10 ? ('0' + min) : min;
			    sec = sec < 10 ? ('0' + sec) : sec;
			    
			    var newdate = year + "-" + month + "-" + day + " " + hour + ":" + min + ":" + sec; // 解冻日期
	  			var principal = parseFloat(json_obj.principal); // 本金
	  			var row;
	  			if(pass_days > 10){
					pass_days = Math.floor(pass_days);
	  				if(order_state == 1){
	  					var pass_days = Math.min(pass_days, 15); // 最多可拿15天利息
	  					row = "<tr><td>"+json_obj.orderid+"</td><td>"+(principal*pass_days*0.01 + principal).toFixed(2)+"</td><td>"+principal+"</td><td>"+newdate+"</td><td style=\"color: #FF9900\" onclick=\"unfreeze_money(this)\">提现</td></tr>";
	  				}else if(order_state == 2){
	  					row = "<tr><td>"+json_obj.orderid+"</td><td>"+(principal*pass_days*0.01 + principal).toFixed(2)+"</td><td>"+principal+"</td><td>"+newdate+"</td><td value=\"2\" onclick=\"show_tips(this)\">已提现</td></tr>";
	  				}
	  			}else{
	  				row = "<tr><td>"+json_obj.orderid+"</td><td>"+(principal*0.1 + principal).toFixed(2)+"</td><td>"+principal+"</td><td>"+newdate+"</td><td value=\"1\" onclick=\"show_tips(this)\">待解冻</td></tr>";
	  			}
	          	$("#table_trading_list").append(row);
	          	// 隐藏订单id
	          	$('#table_trading_list tr').find('th:eq(0)').hide();
	          	$('#table_trading_list tr').find('td:eq(0)').hide();
	          	
	          	$('#table_trading_list tr').find('th:eq(1)').hide();
	          	$('#table_trading_list tr').find('td:eq(1)').hide();
	  		}
        }
    });
}

// 提现
function unfreeze_money(obj){
	//得到当前所在行
	var row = obj.parentNode.rowIndex;
	var table = document.getElementById("table_trading_list");
	var total_money = table.rows[row].cells[1].innerHTML;// 获取应得金额
	var orderid = table.rows[row].cells[0].innerHTML; // 获取订单编号
	// 确认提现
	var truthBeTold = window.confirm("确认提现 " + total_money + " 到本息账户？");
	if (truthBeTold) {
		var user_info = {"flags":"7", "uid":curr_user, "total_money":total_money, "orderid":orderid};
		$.ajax({
	        type: "POST",
	        url: "php/unfreezemoney.php" ,
	        data: {datas:JSON.stringify(user_info)},
	        dataType: "json",
	        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
	        success: function(json) {
            	alert(json.result);
            	window.location.reload(); // 刷新当前页面.
	        }
	    });
	}
}

// 错误操作提示
function show_tips(obj){
	var type = obj.getAttribute("value");
	if(type == 1){
		alert("解冻可提现");
	}else if(type == 2){
		alert("已提现");
	}
}

