// 获取两小时内完成打款的次数
function get_stars(){
	var curr_user = get_curr_user();
	var user_info = {"flags":"16", "uid":curr_user};
	$.ajax({
        type: "POST",
        url: "php/querydata.php" ,
        data: {datas:JSON.stringify(user_info)},
        dataType: "json",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",//设置字符集
        success: function(json) {
			$('#fast_hand_times').text(json.stars);
        }
    });
}
