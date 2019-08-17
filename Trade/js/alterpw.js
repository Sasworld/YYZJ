function alter_pw(){
	var oldpw = $('#old_pw').val();
	var newpw = $('#new_pw').val();
	var renewpw = $('#re_new_pw').val();
	my_validate("old_pw", "请输入原密码");
	my_validate("new_pw", "请输入新密码");
	my_validate("re_new_pw", "请确认密码");
	if(newpw != renewpw){
		alert("两次密码不一致");
	}else{
		showTip("修改中...");
		var uid = get_curr_user();
		var user_info = {"uid":uid, "oldpw":oldpw, "newpw":newpw};
		$.ajax({
	        type: "POST",
	        url: "php/alterpw.php" ,
	        data: {datas:JSON.stringify(user_info)},
	        dataType: "json",
	        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
	        success: function(json) {
	        	if(json.result == 2){
	        		closeTip();
	        		alert("密码修改成功，请重新登陆");
	        		window.location.href="login.html";
	        	}else{
	        		alert(json.result);
	        	}
	        }
	    });
	}
}
