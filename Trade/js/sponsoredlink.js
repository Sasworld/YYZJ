var home_link = "https://yyzj.store/Trade/register.html?username="
var curr_user = get_curr_user();
// 生成推广链接
function generate_link(){
	var user_info = {"flags":"4", "uid":curr_user};
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
            	exclusive_link = home_link + json.result;
            	$('#text_spd_link').text(exclusive_link);
            }
        }
    });
}

function copy_link(){
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
	    input.value = $('#text_spd_link').val();
	    document.body.appendChild(input);
	    input.select();
	    input.setSelectionRange(0, input.value.length), document.execCommand('Copy');
	    alert("复制成功");
	    document.body.removeChild(input);
	} else{
	    var oInput = document.createElement('input');
	    oInput.value = $('#text_spd_link').val();
	    document.body.appendChild(oInput);
	    oInput.select(); // 选择对象
	    document.execCommand("Copy"); // 执行浏览器复制命令
	    oInput.className = 'oInput';
	    oInput.style.display='none';
	    alert('已复制到剪切板');
	}
//	var input = document.createElement("input");
//	input.setAttribute("disabled", "disabled");
//  //只需要改变取值方式即可
//  input.value = $('#text_spd_link').val();
//  document.body.appendChild(input);
//  input.select();
//  input.setSelectionRange(0, input.value.length), document.execCommand('Copy');
//  alert("复制成功");
//  document.body.removeChild(input);
}
