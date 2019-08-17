$(function () { 
//	get_all_paidan();
});
var paidan_money = 0;
var traderid = -1;
var paidanid = -1;
//页面切换
function switch_bar(obj){
	$('li').removeClass('active_trade_bar');
	$(obj).addClass("active_trade_bar");
    var index = $("#ul_trade_nav_bar li").index(obj);
//  隐藏所有页面
    $('#trade_page1').css('display', 'none');
    $('#trade_page2').css('display', 'none');
    $('#trade_page3').css('display', 'none');
    switch (index){
    	case 0:
    		$('#trade_page1').css('display', 'block');
    		break;
    	case 1:
    		$('#trade_page2').css('display', 'block');
    		break;
    	case 2:
    		$('#trade_page3').css('display', 'block');
    		break;
    	default:
    		break;
    }
}

// 获取上次交割时间
function get_jiaoge_time(){
	var curr_user = get_curr_user();
	var user_info = {"flags":"1", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/getJGtime.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	if(json.res == '1'){
        		var last_jg_time = new Date(json.orderdate.replace(/\-/g, '/'));
//      		var year = curr_date.getFullYear();
//			    var month = curr_date.getMonth() + 1;
//			    var day = curr_date.getDate();
//			    var hour = curr_date.getHours();
//			    var min = curr_date.getMinutes();
//			    var sec = curr_date.getSeconds();
//			    month = month < 10 ? ('0' + month) : month;
//			    day = day < 10 ? ('0' + day) : day;
//			    hour = hour < 10 ? ('0' + hour) : hour;
//			    min = min < 10 ? ('0' + min) : min;
//			    sec = sec < 10 ? ('0' + sec) : sec;
//			    var newdate = year + "-" + month + "-" + day + " " + hour + ":" + min + ":" + sec; // 解冻日期
				setInterval(function(){
					var curr_time = new Date();
				    var time_dis = (curr_time - last_jg_time)/(3600*1000); // 间隔小时
				    var rest_time = (24 - time_dis)*3600*1000;
					if(time_dis > 24){
						$('#text_jg_tip').text("可交割");
					}else{
						var dao_time = getDuration(rest_time);
						$('#text_jg_tip').text(dao_time);
					}
				},1000);
        	}else if(json.res == '2'){
        		$('#text_jg_tip').text("可交割");
        	}
        }
    });
}

// 循环执行
function getDuration(my_time) {  
	var days = my_time / 1000 / 60 / 60 / 24;
	var daysRound = Math.floor(days);
	var hours = my_time / 1000 / 60 / 60 - (24 * daysRound);
	var hoursRound = Math.floor(hours);
	var minutes = my_time / 1000 / 60 - (24 * 60 * daysRound) - (60 * hoursRound);
	var minutesRound = Math.floor(minutes);
	var seconds = parseInt(my_time / 1000 - (24 * 60 * 60 * daysRound) - (60 * 60 * hoursRound) - (60 * minutesRound));
	hoursRound = hoursRound < 10 ? ('0' + hoursRound) : hoursRound;
	minutesRound = minutesRound < 10 ? ('0' + minutesRound) : minutesRound;
	seconds = seconds < 10 ? ('0' + seconds) : seconds;
	var time = hoursRound + ' : ' + minutesRound + ' : ' + seconds;
	return time;
}
// 获取排单
function get_all_paidan(tableid, pdid, keywords){
	var curr_user = get_curr_user();
	var user_info = {"flags":"8", "uid":curr_user, "chooseid":pdid, "keywords":keywords};
	$.ajax({
        type: "POST",
        url: "php/getpaidan.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	paidan_array = eval(json);
        	$("#" + tableid +" tr:gt(0)").remove();
            for(var i = 0; i < paidan_array.length; ++i){
	  			json_obj = paidan_array[i];
	  			var tel = json_obj.uid.replace(json_obj.uid.substring(3,7), "****");
	  		    var row = "<tr><td>"+json_obj.pdid+"</td><td>"+json_obj.uname+"</td><td>"+json_obj.stars+"</td><td>"
		  				+tel+"</td><td>"+json_obj.needamount+"</td><td><input type=\"button\" value=\"交割\" class=\"btn_trade_submit\" onclick=\"open_pop(this)\"/></td></tr>";
	          	$("#" + tableid).append(row);
	  		}
        }
    });
}

// 搜索排单
function search_paidan(){
	check_login_state();
	var curr_user = get_curr_user();
	var user_info = {"flags":"9", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	var times = 3 - json.result;
        	if(times > 0){
        		var placeholder = "今日还可搜索" + (times-1) + "次";
	        	$('#text_search_key_words').attr('placeholder',placeholder);
        		$('#div_page3_table').css('display', 'block');
				var key_words = $('#text_search_key_words').val();
				get_all_paidan("table_search_area", "3", key_words);
        	}else{
        		alert("今日搜索次数已用完");
        	}
        }
    });
}

// 搜索排单
function get_search_times(){
	var curr_user = get_curr_user();
	var user_info = {"flags":"9", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	var times = 3 - json.result;
        	var placeholder = "今日还可搜索" + times + "次";
        	$('#text_search_key_words').attr('placeholder',placeholder);
        }
    });
}

// 提交交割
function sumbit_jiaoge(){
	var trademoney = $('#text_jiaoge_money').val();
	var paytype = "";
	if ($('#pay_by_reward').is(':checked')==false) {
		paytype = "1"; // 本金支付
	}else{
		paytype = "2";
	}
	my_validate("text_jiaoge_money", "请输入交割数量");
	if(!checkRate(trademoney)){
		throw SyntaxError(); //如果验证不通过，则不执行后面
	}else{
		if(paytype=="2" && trademoney % 500 !=0){
			alert("使用奖金交割必须为500的倍数,且有正在成长中的订单");
		}else{
			if(trademoney % 100 != 0 || trademoney == 0){
				alert("交割金额必须为100的倍数");
			}else if(parseInt(trademoney) > parseInt(paidan_money)){
				alert("超过排单总额");
			}else{
				showTip("正在交割...");
				var curr_user = get_curr_user();
				var user_info = {"flags":"2", "uid":curr_user, "trademoney":trademoney, "paidanid":paidanid, "paytype":paytype};
				$.ajax({
			        type: "POST",
			        url: "php/jiaoge.php" ,
			        data: {datas:JSON.stringify(user_info)},
			        dataType: "json",
			        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
			        success: function(json) {
			        	closeTip();
		        		alert(json.result);
		        		window.location.reload();
			        }
			    });
				close_pop();
			}
		}
	}
}

// 交割弹窗
function open_pop(obj){
	check_login_state();
	var curr_date = new Date();
	var hour = curr_date.getHours();
	var min = curr_date.getMinutes();
	var sec = curr_date.getSeconds();
	hour = hour < 10 ? ('0' + hour) : hour;
	min = min < 10 ? ('0' + min) : min;
	sec = sec < 10 ? ('0' + sec) : sec;
	var curr_time = hour + ":" + min + ":" + sec;
	// 客户端时间检查, 服务端二次检查
	var start_time = '09:00:00';
	var end_time = '21:00:00';
	if (curr_time < start_time || curr_time > end_time) {
		alert("交割时间: " + start_time + " - " + end_time);
	} else{
		//得到当前所在行
		var curr_user = get_curr_user();
		var curr_uname = get_curr_uname();
		var row = obj.parentNode.parentNode.rowIndex;
		var tableid = $(obj).parent().parent().parent().parent().attr("id");
		var table = document.getElementById(tableid);
		var tradername = table.rows[row].cells[1].innerHTML;
		var jiaoge_tips = $('#text_jg_tip').text();
		if(jiaoge_tips != "可交割" && jiaoge_tips != ""){
			alert("今日已交割");
		}else{
			paidanid = table.rows[row].cells[0].innerHTML;
			traderid = table.rows[row].cells[1].innerHTML;
			if(curr_uname == tradername){
				alert("不能与自己的账户交割");
			}else{
				paidan_money = table.rows[row].cells[4].innerHTML;
				$('#jiaoge_people').text(tradername);
			    $('#trade_pop_window_jiaoge').css('display', 'block');
				$('#pop_mask').css('display', 'block');
			}
		}
	}
}

function close_pop(){
    $('#trade_pop_window_jiaoge').css('display', 'none');
	$('#pop_mask').css('display', 'none');
}