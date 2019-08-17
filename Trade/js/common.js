//局部刷新页面,返回结果放在resdiv中
function loadPage(currenturl,resdiv)
{	
	$("#"+resdiv).load( currenturl, function( response, status, xhr ) {
		$("#"+resdiv).html(response);
	});
}

//======================= Common Function ============================
// 验证表单是否为空， 通过$.fn 扩展jQuery方法
function my_validate(element, tips){
	if($("#" + element).val() == "" || $.trim($("#" + element).val()).length == 0){
    alert(tips);
    throw SyntaxError(); //如果验证不通过，则不执行后面
  }
}

// 获取当前用户的id
function get_curr_user(){
	var storage=window.localStorage;
	var curr_uid = storage["curruserid"];
	return curr_uid;
}

// 获取当前用户的名称
function get_curr_uname(){
	var storage=window.localStorage;
	var curr_uname = storage["currusername"];
	return curr_uname;
}

// 返回上一页
function back_to_lastpage(){
	history.back(-1);
}

// 退出账户
function quit_account(){
	var certain_cancel = window.confirm("确认退出当前账户？");
	if(certain_cancel){
		localStorage.clear();
		window.location.href = "login.html";
	}
}

// 判断用户是否已经登陆, 未登录跳转至登陆界面
function check_login_state(){
	var user_login_state = get_curr_user();
	if(user_login_state == null){
		window.location.href="login.html";
	}
	set_operate_time();
}

// 存储上次操作时间
function set_operate_time(){
	var storage=window.localStorage;
	var last_operate_time = storage['operate_time'];
	var curr_time = new Date().getTime();
	var time_interval = (curr_time - last_operate_time)/(1000*60);
	var curr_uid = get_curr_user();
	// 微操作时长大于10分钟需重新登陆
	if(time_interval > 10){
    	localStorage.clear();
	    window.location.href = "login.html";
	}else{
		var check_info = {"uid":curr_uid};
		$.ajax({
	        type: "POST",
	        url: "php/checkoffsite.php" ,
	        data: {datas:JSON.stringify(check_info)},
	        dataType: "json",
	        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
	        success: function(json) {
	    		if(json.res == "1"){
	    			storage['operate_time'] = curr_time; // 更新操作时间
	    		}else{
	    			alert("登陆信息过期，请重新登陆");
	    			localStorage.clear();
	    			window.location.href = "login.html";
	    		}
	        }
	    });
	}
}

// 判断输入是否为数字
function checkRate(nubmer) {
　　var re = /^[0-9]+.?[0-9]*$/; //判断字符串是否为数字 //判断正整数 /^[1-9]+[0-9]*]*$/ 
　　if (!re.test(nubmer)) {
　　　　alert("请输入数字");
　　　　return false;
　　}else{
	   return true;
	}
}

//显示提示
function showTip(info){
	$('#tipInfo').html(info);
	$('#tip_loading_mask').show();
	$('#tip_loading_bg').show();
}
//关闭提示
function closeTip(){
	$('#tip_loading_mask').hide();
	$('#tip_loading_bg').hide();
}

function isMobile() {
    if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)))
        return true;
    else
        return false;
}