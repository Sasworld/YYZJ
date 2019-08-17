var validate_code = ""; //登陆验证码
//生成随机数
function randomNum(min,max){
    return Math.floor(Math.random()*(max-min)+min);
}

//生成随机颜色RGB分量
function randomColor(min,max){
    var _r = randomNum(min,max);
    var _g = randomNum(min,max);
    var _b = randomNum(min,max);
    return "rgb("+_r+","+_g+","+_b+")";
}

//绘制验证码
function drawPic(){
    //获取到元素canvas
    var $canvas = document.getElementById("canvas");
    var _str = "0123456789";//设置随机数库
    var _picTxt = "";//随机数
    var _num = 4;//4个随机数字
    var _width = $canvas.width;
    var _height = $canvas.height;
    var ctx = $canvas.getContext("2d");//获取 context 对象
    ctx.textBaseline = "bottom";//文字上下对齐方式--底部对齐
    ctx.fillStyle = randomColor(180,240);//填充画布颜色
    ctx.fillRect(0,0,_width,_height);//填充矩形--画画
    for(var i=0; i<_num; i++){
        var x = (_width-10)/_num*i+10;
        var y = randomNum(_height/2,_height);
        var deg = randomNum(-45,45);
        var txt = _str[randomNum(0,_str.length)];
        _picTxt += txt;//获取一个随机数
        ctx.fillStyle = randomColor(10,100);//填充随机颜色
        ctx.font = randomNum(40,80)+"px SimHei";//设置随机数大小，字体为SimHei
        ctx.translate(x,y);//将当前xy坐标作为原始坐标
        ctx.rotate(deg*Math.PI/180);//旋转随机角度
        ctx.fillText(txt, 0,0);//绘制填色的文本
        ctx.rotate(-deg*Math.PI/180);
        ctx.translate(-x,-y);
    }
    for(var i=0; i<_num; i++){
        //定义笔触颜色
        ctx.strokeStyle = randomColor(90,180);
        ctx.beginPath();
        //随机划线--4条路径
        ctx.moveTo(randomNum(0,_width), randomNum(0,_height));
        ctx.lineTo(randomNum(0,_width), randomNum(0,_height));
        ctx.stroke();
    }
    for(var i=0; i<_num*10; i++){
        ctx.fillStyle = randomColor(0,255);
        ctx.beginPath();
        //随机画原，填充颜色
        ctx.arc(randomNum(0,_width),randomNum(0,_height), 1, 0, 2*Math.PI);
        ctx.fill();
    }
    return _picTxt;//返回随机数字符串
}

//==================== 登陆验证 =======================
// 验证表单是否为空， 通过$.fn 扩展jQuery方法
function login_validate_form(element, tips){
	if($("#" + element).val() == "" || $.trim($("#" + element).val()).length == 0){
    alert(tips);
    validate_code = drawPic(); // 重绘验证码
    throw SyntaxError(); //如果验证不通过，则不执行后面
  }
}

function check_login(){
	var uname = $('#text_uname').val();
	var upw = $('#text_pw').val();
	login_validate_form("text_uname", "请输入账号！");
	login_validate_form("text_pw", "请输入密码！");
	login_validate_form("text_vcode", "请填写验证码！");
	if($('#text_vcode').val() != validate_code){
		alert("验证码错误");
		validate_code = drawPic(); // 重绘验证码
		throw SyntaxError(); //如果验证不通过，则不执行后面
	}
//	flags : 1 表示查询登陆信息
	$("#login_sys").attr('disabled',true);
	var user_info = {"uname":uname, "upw":upw};
	$.ajax({
        type: "POST",
        url: "php/login.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
            if(json.res == '1'){
				//存储用户账号
            //	if ($('#remeber_user').is(':checked')==true) {
            		//$.cookie('login_uname', uname, { expires: 7 });
					//$.cookie('login_pw', upw, { expires: 7 });
            //	}else{
            	//	$.cookie('login_uname', "", { expires: 7 });
			//		$.cookie('login_pw', "", { expires: 7 });
            	//}
            	//保存用户id，设置时间，超时需重新登陆,存储数据到localStorage
				if(!window.localStorage){
			        alert("浏览器不支持");
			    }else{
			        var storage=window.localStorage;
			        storage["curruserid"] = json.uid;
			        storage["currusername"] = uname;
			        storage['operate_time'] = new Date().getTime();
			        storage['curr_user_session'] = json.curr_user_session;
			        window.location.href="home.html";
			    }
            }else if(json.res == '2'){
            	alert("账号不存在");
            	validate_code = drawPic(); // 重绘验证码
            }else if(json.res == '3'){
            	alert("账号已冻结");
            	validate_code = drawPic(); // 重绘验证码
            }else if(json.res == '4'){
            	alert("账号未激活");
            	validate_code = drawPic(); // 重绘验证码
            }else if(json.res == '5'){
            	alert("密码错误");
            	validate_code = drawPic(); // 重绘验证码
            }
	    	$("#login_sys").attr('disabled',false);
        }
    });
}
