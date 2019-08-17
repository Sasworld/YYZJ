//解析链接中携带的参数
function getUid(url){
    var param = url.split("?")[1];
    var uid = param.split('=')[1];
    return uid;
}
//

function submit_reg_info(){
	my_validate("uname", "请输入账号");
	my_validate("uid", "请输入手机号");
	my_validate("pw", "请输入密码");
	my_validate("repw", "请确认密码");
	my_validate("banknumber", "请输入银行卡号");
	my_validate("subbank", "请输入支行名称");
	my_validate("owner", "请输入持卡人");
	var name = $('#uname').val();
	var uid = $('#uid').val();
	var pw = $('#pw').val();
	var repw = $('#repw').val();
	var parentname = $('#parentid').val();
	var bank = $('#bank option:selected').text();
	var banknumber = $.trim($('#banknumber').val());
	var subbank = $('#subbank').val();
	var owner = $('#owner').val();
	var alipay = $('#alipay').val();
	if ($('#know_risk').is(':checked')==true) {
		if(isPhoneNo(uid) == false){
			alert("请输入正确的手机号");
		}else{
			if (bank == "") {
				alert("请选择银行");
			} else{
				if(CheckBankNo(banknumber) == true){
					if(pw==repw){
						var user_info = {"uid":uid, "pw":pw, "uname":name, "parentname":parentname, "bank":bank, "banknumber":banknumber, "subbank":subbank, "owner":owner, "alipay":alipay};
						$.ajax({
					        type: "POST",
					        url: "php/register.php" ,
					        data: {datas:JSON.stringify(user_info)},
					        dataType: "json",
					        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
					        success: function(json) {
					        	alert(json.result);
					        	window.location.reload();
					        }
					    });
					}else{
						alert("两次密码不一致");
					}
				}
			}
		}
	}else{
		alert("请确定您已完全了解风险的存在且愿意承担可能的风险");
	}
}

// 验证手机号
function isPhoneNo(phone) {
    var pattern = /^1[34578]\d{9}$/;
    return pattern.test(phone);
}

function CheckBankNo(bankno) {
   if(bankno.length < 16 || bankno.length > 19) {
       alert("银行卡号长度必须在16到19之间");
       return false;
   }
   var num = /^\d*$/; //全数字
   if(!num.exec(bankno)) {
     alert("银行卡号必须全为数字");
     return false;
   }
   //开头6位
   var strBin = "10,18,30,35,37,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,58,60,62,65,68,69,84,87,88,94,95,98,99";
   if(strBin.indexOf(bankno.substring(0, 2)) == -1) {
     alert("银行卡号开头6位不符合规范");
     return false;
   }
   //Luhm校验（新）
   if(!luhmCheck(bankno))
     return false;
   return true;
}

function luhmCheck(bankno){
	var lastNum=bankno.substr(bankno.length-1,1);//取出最后一位（与luhm进行比较）
	var first15Num=bankno.substr(0,bankno.length-1);//前15或18位
	var newArr=new Array();
	for(var i=first15Num.length-1;i>-1;i--){ //前15或18位倒序存进数组
		newArr.push(first15Num.substr(i,1));
	}
	var arrJiShu=new Array(); //奇数位*2的积 <9
	var arrJiShu2=new Array(); //奇数位*2的积 >9
	var arrOuShu=new Array(); //偶数位数组
	for(var j=0;j<newArr.length;j++){
	if((j+1)%2==1){//奇数位
		if(parseInt(newArr[j])*2<9)
			arrJiShu.push(parseInt(newArr[j])*2);
		else
			arrJiShu2.push(parseInt(newArr[j])*2);
	} else //偶数位
		arrOuShu.push(newArr[j]);
	}
	var jishu_child1=new Array();//奇数位*2 >9 的分割之后的数组个位数
	var jishu_child2=new Array();//奇数位*2 >9 的分割之后的数组十位数
	for(var h=0;h<arrJiShu2.length;h++){
		jishu_child1.push(parseInt(arrJiShu2[h])%10);
		jishu_child2.push(parseInt(arrJiShu2[h])/10);
	}
	var sumJiShu=0; //奇数位*2 < 9 的数组之和
	var sumOuShu=0; //偶数位数组之和
	var sumJiShuChild1=0; //奇数位*2 >9 的分割之后的数组个位数之和
	var sumJiShuChild2=0; //奇数位*2 >9 的分割之后的数组十位数之和
	var sumTotal=0;
	for(var m=0;m<arrJiShu.length;m++){
		sumJiShu=sumJiShu+parseInt(arrJiShu[m]);
	}
	for(var n=0;n<arrOuShu.length;n++){
		sumOuShu=sumOuShu+parseInt(arrOuShu[n]);
	}
	for(var p=0;p<jishu_child1.length;p++){
		sumJiShuChild1=sumJiShuChild1+parseInt(jishu_child1[p]);
		sumJiShuChild2=sumJiShuChild2+parseInt(jishu_child2[p]);
	}
	//计算总和
	sumTotal=parseInt(sumJiShu)+parseInt(sumOuShu)+parseInt(sumJiShuChild1)+parseInt(sumJiShuChild2);
	//计算Luhm值
	var k= parseInt(sumTotal)%10==0?10:parseInt(sumTotal)%10;
	var luhm= 10-k;
	if(lastNum==luhm){
		return true;
	}else{
		alert("银行卡号不合法");
		return false;
	}
}