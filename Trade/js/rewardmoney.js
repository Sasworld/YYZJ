// 获取奖励记录
function get_reward_record(){
	var curr_user = get_curr_user();
	var user_info = {"flags":"11", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	reward_array = eval(json);
        	json_obj = reward_array[0];
        	var reward_money = json_obj.bonus;
        	$('#bonus_money').text(reward_money);
        	$("#table_reward_list tr:gt(0)").remove();
            for(var i = 1; i < reward_array.length; ++i){
	  			json_obj = reward_array[i];
	  			var order_state = json_obj.orderstate;
	  			// 2：提现，1：未体现
	  			if(order_state == 2){
	  				var row = "<tr><td>"+json_obj.uname+"</td><td>"+json_obj.relation+"</td><td>"+json_obj.reward+"</td><td>已解冻</td></tr>";
	  			}else if(order_state == 1){
	  				var row = "<tr><td>"+json_obj.uname+"</td><td>"+json_obj.relation+"</td><td>"+json_obj.reward+"</td><td>待解冻</td></tr>";
	  			}
	          	$("#table_reward_list").append(row);
	  		}
        }
    });
}
