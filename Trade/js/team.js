// 获取团队人数和激活码数量
function get_active_and_member(){
	var curr_user = get_curr_user();
	var user_info = {"uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/getmembers.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
        	team_array = eval(json);
        	var dir_amount = 0;
        	var sec_amount = 0;
        	var third_amount = 0;
		var other_amount = 0;
        	var json_obj = team_array[0]; 
        	
        	var active_code_amount = json_obj.activecode;
        	
        	$('#active_code_amount').text(active_code_amount);
        	$("#tabel_member_list tr:gt(0)").remove();
            for(var i = 1; i < team_array.length; ++i){
	  			json_obj = team_array[i];
	  			if(json_obj.relation == "直推" && json_obj.state==1){
	  				dir_amount++;
	  			}else if(json_obj.relation == "二代" && json_obj.state==1){
	  				sec_amount++;
	  			}else if(json_obj.relation == "三代" && json_obj.state==1){
	  				third_amount++;
	  			}else if(json_obj.relation == "其它" && json_obj.state==1){
	  				other_amount++;
	  			}
	  			var member_state_code = json_obj.state;
	  			// 2：账号封号，1：账号已激活， 0：账号未激活
	  			if(member_state_code == 2){
	  				var row = "<tr><td>"+json_obj.uname+"</td><td>"+json_obj.uid+"</td><td>"+json_obj.relation+"</td><td>封号</td><td>已激活</td></tr>";
	  			}else if(member_state_code == 1){
	  				var row = "<tr><td>"+json_obj.uname+"</td><td>"+json_obj.uid+"</td><td>"+json_obj.relation+"</td><td>正常</td><td>已激活</td></tr>";
	  			}else{
	  				var row = "<tr><td>"+json_obj.uname+"</td><td>"+json_obj.uid+"</td><td>"+json_obj.relation+"</td><td>正常</td><td style=\"color:#FF9900;\" onclick=\"active_new_member(this)\">激活</td></tr>";
	  			}
	          	$("#tabel_member_list").append(row);
	  		}
	    var member_amount = dir_amount + sec_amount + third_amount + other_amount; // 第一条保存的激活码核团队人员数量
            $('#dir_member_amount').text(dir_amount);
            $('#sec_member_amount').text(sec_amount);
            $('#third_member_amount').text(third_amount);
	    $('#team_member_amount').text(member_amount);
        }
    });
}

// 激活会员
function active_new_member(obj){
	//得到当前所在行
	var row = obj.parentNode.rowIndex;
	var table = document.getElementById("tabel_member_list");
	var member_name = table.rows[row].cells[0].innerHTML;
	var memberid = table.rows[row].cells[1].innerHTML;
	var active_code = $('#active_code_amount').text();
	if(active_code <1){
		alert("您的激活码已用完，请先购买");
	}else{
		var truthBeTold = window.confirm("会员账号：" + member_name + ", 激活会员将消耗一个激活码");
		if (truthBeTold) {
			var curr_user = get_curr_user();
			var user_info = {"flags":"1", "uid":curr_user, "memberid":memberid};
			$.ajax({
		        type: "POST",
		        url: "php/updatedata.php" ,
		        data: {datas:JSON.stringify(user_info)},
		        dataType: "json",
		        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
		        success: function(json) {
	        		alert(json.res);
	        		window.location.reload();
		        }
		    });
		} else{
			
		}
	}
}
