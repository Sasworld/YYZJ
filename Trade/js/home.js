// 获取当前用户
var curr_user = get_curr_user();
var pdcoin_amount = 0;
//
function open_pop(id){
	switch (id){
    	case 1:
    		get_wechat_account("kefu_wechat", "3");
    		$('#home_pop_window_kefu').css('display', 'block');
    		$('#pop_mask').css('display', 'block');
    		break;
    	case 2:
    		get_wechat_account("user_account", "4");
    		$('#home_pop_window_account').css('display', 'block');
    		$('#pop_mask').css('display', 'block');
    		break;
    	case 3:
    		var curr_date = new Date();
		var hour = curr_date.getHours();
		var min = curr_date.getMinutes();
		var sec = curr_date.getSeconds();
		hour = hour < 10 ? ('0' + hour) : hour;
		min = min < 10 ? ('0' + min) : min;
		sec = sec < 10 ? ('0' + sec) : sec;
    		var curr_time = hour + ":" + min + ":" + sec;
		var start_time = '09:00:00';
		var end_time = '18:00:00';
		if (curr_time < start_time || curr_time > end_time) {
			alert("排单时间: " + start_time + " - " + end_time);
		} else{
    			$('#home_pop_window_paidan').css('display', 'block');
		}
    		break;
    	default:
    		break;
   }
}
function close_pop(id){
	switch (id){
    	case 1:
    		$('#home_pop_window_kefu').css('display', 'none');
    		$('#pop_mask').css('display', 'none');
    		break;
    	case 2:
    		$('#home_pop_window_account').css('display', 'none');
    		$('#pop_mask').css('display', 'none');
    		break;
    	case 3:
    		$('#home_pop_window_paidan').css('display', 'none');
    		$('#pop_mask').css('display', 'none');
    		break;
		case 4:
			$('#home_pop_window_order_detail').css('display', 'none');
			$('#pop_mask').css('display', 'none');
			break;
		case 5:
			$('#pop_mask_images').css('display', 'none');
			break;
    	default:
    		break;
    }
}

// ================== 排单 ====================
function insert_paidan(pdamount){
	var user_info = {"flags":"1", "uid":curr_user, 'pdamount':pdamount};
	$.ajax({
        type: "POST",
        url: "php/paidan.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	closeTip();
        	alert(json.result);
        }
    });
}

// 获取客户的排单币总数
function submit_paidan(){
	my_validate("text_pd_money", "请输入排单金额");
	var pd_money = $('#text_pd_money').val(); //排单金额
	if(!checkRate(pd_money)){
		throw SyntaxError(); //如果验证不通过，则不执行后面
	}else{
		if(pd_money % 500 != 0 || pd_money == 0){
			alert("排单金额必须为500的倍数");
			throw SyntaxError(); //如果验证不通过，则不执行后面
		}
		// flags:2 查询排单币余额
		var user_info = {"flags":"2", "uid":curr_user, "queryid":"1"};
		$.ajax({
	        type: "POST",
	        url: "php/querydata.php" ,
	        data: {datas:JSON.stringify(user_info)},
	        dataType: "json",
	        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
	        success: function(json) {
	            if(json.paidancoin == "F"){
	            	alert("发生未知错误，请稍后重试");
	            }else{
	            	handling_fee = pd_money*0.01;
	            	pdcoin_amount = json.paidancoin;
	            	if(pdcoin_amount < handling_fee){
	            		alert("排单币不足");
	            	}else{
	            		var truthBeTold = window.confirm("此单手续费：" + handling_fee + ",扣除后剩余排单币：" + (pdcoin_amount - handling_fee));
						if (truthBeTold) {
							$('#text_pd_money').val("");
							close_pop(3);
							showTip("正在提交...");
							insert_paidan(pd_money);
						} else{
							$('#text_pd_money').val("");
							close_pop(3);
						}
	            	}
	            }
	        }
	    });
	}
}

// ==================== 客服、账户 =========================
function get_wechat_account(id, index){
	// flags:3 查询客服微信号
	// flags:4 查询会员账户
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

// 获取正在进行的订单信息
function get_ordering_info(){
	var curr_user = get_curr_user();
	var user_info = {"flags":"13", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/currorder.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	ordering_array = eval(json);
        	order_amount = ordering_array.length;
        	if(order_amount > 0){
        		$("#self_ordering_list").css('display', 'block');
        		$('#no_ordering_tips').css('display', 'none');
        	}
        	$("#self_ordering_list tr:gt(0)").remove();
            for(var i = 0; i < order_amount; ++i){
	  			json_obj = ordering_array[i];
	  			var buyerid = json_obj.buyerid;
	  			var tel_sell = json_obj.sellerid.replace(json_obj.sellerid.substring(3,7), "****");
	  			var tel_buy = buyerid.replace(buyerid.substring(3,7), "****");
	  			if(buyerid == curr_user){ // 购买排单
	  				var row = "<tr><td>"+json_obj.orderid+"</td><td>"+tel_sell+"</td><td>"+json_obj.principal+"</td><td>"
		  				+json_obj.orderdate+"</td><td>买入</td><td style=\"color: #FF9900;\" onclick=\"check_order_detial(this)\">查看</td></tr>";
	  			}else{
	  				var row = "<tr><td>"+json_obj.orderid+"</td><td>"+tel_buy+"</td><td>"+json_obj.principal+"</td><td>"
		  				+json_obj.orderdate+"</td><td>卖出</td><td style=\"color: #FF9900;\" onclick=\"check_order_detial(this)\">查看</td></tr>";
	  			}
	          	$("#self_ordering_list").append(row);
	  		}
        }
    });
}

// 查看订单详情
function check_order_detial(obj){
	//得到当前所在行
	var row = obj.parentNode.rowIndex;
	var table = document.getElementById("self_ordering_list");
	var order_type = table.rows[row].cells[4].innerHTML;// 获取订单类型
	var orderid = table.rows[row].cells[0].innerHTML; // 获取订单编号
	var curr_user = get_curr_user();
	var curr_name = get_curr_uname();
	var user_info = {"uid":curr_user, "orderid":orderid, "ordertype":order_type};
	$.ajax({
        type: "POST",
        url: "php/orderdetail.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
//      	alert(json.parentname);
        	if(json != null){
        		$('#sell_orderid').text(json.orderid);
        		$('#sell_paidanid').text(json.paidanid);
    			$('#order_date').text("订单创建日期：" + json.orderdate);
    			$('#text_bank').text(json.bank);
    			$('#text_bank_number').val(json.banknumber);
    			$('#text_bank_owner').text(json.ownername);
    			$('#text_bank_alipay').text(json.alipay);
    			var storage=window.localStorage;
        		if(order_type == "卖出"){
        			if(json.finishstate == "0"){
        				$('#certain_state').text("未打款");
        			}else if(json.finishstate == "1"){
        				$('#certain_state').text("待确认");
        			}else if(json.finishstate == "2"){
        				$('#certain_state').text("已收货");
        			}
        			$('#buyer_name').text(json.uname + "(" + json.buyerid.replace(json.buyerid.substring(3,7), "****") + ")");
	    			if(json.parentid != null && json.parentid != ""){
	    				$('#buyer_parent_name').text(json.parentname + "(" + json.parentid.replace(json.parentid.substring(3,7), "****") + ")");
	    			}
        			$('#seller_name').text(curr_name + "(" + json.sellerid.replace(json.sellerid.substring(3,7), "****") + ")");
	    			if(json.currparentid != null && json.currparentid != ""){
	    				$('#seller_parent_name').text(json.currparentname + "(" + json.currparentid.replace(json.currparentid.substring(3,7), "****") + ")");
	    			}
        			
        			$('#dk_money_info').text(json.uname + "应向您打款：" + json.principal + "元");
        			$('#certain_shoukuan_btn').css('display','inline');
        			$('#file_certificate').css('display','none');
        			$('#upload_certificate_btn').css('display','none');
        		}else if(order_type == "买入"){
        			if(json.finishstate == "0"){
        				$('#certain_state').text("未打款");
        			}else if(json.finishstate == "1"){
        				$('#certain_state').text("已打款");
        				$('#text_dkdate').text('打款时间：' + json.dkdate);
        				$('#text_dkdate').css('display', 'block');
        			}else if(json.finishstate == "2"){
        				$('#certain_state').text("已完成");
        				$('#text_dkdate').text('打款时间：' + json.dkdate);
        				$('#text_dkdate').css('display', 'block');
        			}
        			$('#buyer_name').text(curr_name + "(" + json.buyerid.replace(json.buyerid.substring(3,7), "****") + ")");
				if(json.currparentid != null && json.currparentid != ""){
	    				$('#buyer_parent_name').text(json.currparentname + "(" + json.currparentid.replace(json.currparentid.substring(3,7), "****") + ")");
	    			}
        			$('#upload_certificate_btn').css('display','inline');
        			$('#file_certificate').css('display','block');
        			$('#certain_shoukuan_btn').css('display','none');
        			$('#dk_money_info').text("您应向" +json.uname + "打款：" + json.principal + "元");
        			$('#seller_name').text(json.uname + "(" + json.sellerid.replace(json.sellerid.substring(3,7), "****") + ")");
	    			if(json.parentid != null && json.currparentid != ""){
	    				$('#seller_parent_name').text(json.parentname + "(" + json.parentid.replace(json.parentid.substring(3,7), "****") + ")");
	    			}
        		}
        	}
        }
    });
	//显示弹窗
	$('#home_pop_window_order_detail').css('display', 'block');
	$('#pop_mask').css('display', 'block');
}

// 提交打款凭证
function upload_dkcertificate(){
	var truthBeTold = window.confirm("打款凭证只能提交一次，请确认凭证无误！");
	if (truthBeTold) {
		showTip("正在提交...");
		var curr_id = get_curr_user();
		var orderid = $("#sell_orderid").text();
	    var file1 = document.getElementById('file_certificate').files[0]; //获取文件路径名，注意了没有files[1]这回事，已经试过坑
	    var formData = new FormData();
	    formData.append('orderid',orderid);
	    formData.append('uid',curr_id);
	    formData.append('file',file1);
	   	$.ajax({
	   		type: "POST",
	        url: "Certificate/uploadcertificate.php",  //同目录下的php文件
	    	data:formData,
	    	dataType:"json", //声明成功使用json数据类型回调
	        //如果传递的是FormData数据类型，那么下来的三个参数是必须的，否则会报错
	     	cache:false,  //默认是true，但是一般不做缓存
	     	processData:false, //用于对data参数进行序列化处理，这里必须false；如果是true，就会将FormData转换为String类型
	     	contentType:false,  //一些文件上传http协议的关系，自行百度，如果上传的有文件，那么只能设置为false
	     	success: function(msg){  //请求成功后的回调函数
	      		closeTip();
	      		alert(msg.result);
	        }
	    });
	}
}

// 查看打款凭证
function check_dkcertificate(obj){
	var click_id = obj.getAttribute("id");
	var orderid = $("#sell_orderid").text();
	var info = {"orderid":orderid};
	$.ajax({
        type: "POST",
        url: "php/checkdkcertificate.php" ,
        data: {datas:JSON.stringify(info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
            if(json.result == "2"){
            	alert("尚未上传打款凭证");
            }else if(json.result == "3"){
            	alert("订单查询失败");
            }else{
            	$("#img_dkcertificate").attr("src", "Certificate/" + json.certificate);
            	$('#pop_mask_images').css('display', 'block');
            }
        }
    });
}

// 确认收款
function confirm_receipt(){
	var orderid = $("#sell_orderid").text();
	var info = {"orderid":orderid};
	$.ajax({
        type: "POST",
        url: "php/checkdkcertificate.php" ,
        data: {datas:JSON.stringify(info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
            if(json.result == "2"){
            	alert("对方尚未上传打款凭证");
            }else if(json.result == "3"){
            	alert("订单查询失败");
            }else{
            	var truthBeTold = window.confirm("确认收款？");
				if (truthBeTold) {
					var curr_uid = get_curr_user();
					var orderid = $("#sell_orderid").text();
					var info = {"uid":curr_uid, "orderid":orderid};
					$.ajax({
				        type: "POST",
				        url: "php/confirmsk.php" ,
				        data: {datas:JSON.stringify(info)},
				        dataType: "json",
				        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
				        success: function(json) {
				            alert(json.result);
				            window.location.reload();
				        }
				    });
				}
            }
        }
    });
}

//复制银行卡号
function copy_banknumber(){
	document.activeElement.blur();//屏蔽默认键盘弹出；
	//userAgent 属性是一个只读的字符串，声明了浏览器用于 HTTP 请求的用户代理头的值
	var u = navigator.userAgent;
	//Android终端
	var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1;
	//iOS终端
	var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
	if (isiOS) {
		var input = document.createElement("input");
		input.setAttribute("disabled", "disabled");
	    //只需要改变取值方式即可
	    input.value = $('#text_bank_number').val();
	    document.body.appendChild(input);
	    input.select();
	    input.setSelectionRange(0, input.value.length), document.execCommand('Copy');
	    alert("复制成功");
	    document.body.removeChild(input);
	} else{
	    var oInput = document.createElement('input');
	    oInput.value = $('#text_bank_number').val();
	    document.body.appendChild(oInput);
	    oInput.select(); // 选择对象
	    document.execCommand("Copy"); // 执行浏览器复制命令
	    oInput.className = 'oInput';
	    oInput.style.display='none';
	    alert('已复制到剪切板');
	}
}