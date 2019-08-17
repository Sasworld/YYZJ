<?php
include("connSql.php");
$string_data = $_POST['datas']; //获取插入的表的索引
$array_data = json_decode($string_data, true);
$uname= $array_data["uname"];
$upw = $array_data["upw"];
//$uname= "aaa";
//$upw = "123";
$sql_check_islogin = "select state from user where uname='$uname'";
$result = $conn->query($sql_check_islogin);
if($result->num_rows > 0){
	$row = $result->fetch_assoc();
	if($row["state"] == "1"){
		$sql = "select uid from user where uname='$uname' and upw=HEX(aes_encrypt('$upw', 'sasworld'))";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$uid = $row["uid"];
			// 保存当前登陆session 
			$currtime = msectime();
			$session_id = $uname.$currtime;
			session_start(); // 初始化session
			$_SESSION['curr_user_session'] = $session_id; //保存某个session信息
			$affectRow =mysqli_query($conn,"update user set loginstate='$session_id' where uid='$uid'");
			if ($affectRow) {  
			    $arr = array(
					'res'=>"1", //登陆成功
					'uid'=>$row["uid"],
					'curr_user_session' => $session_id,
				);
			} else {
				$arr = array(
					'res'=>"6", //重试
				);
			}
		}else{
			$arr = array(
				'res'=>"5", //密码错误
			);
		}
	}else if($row["state"] == "0"){
		$arr = array(
			'res'=>"4", //未激活
		);
	}else{
		$arr = array(
			'res'=>"3", //已冻结
		);
	}
}else{
	$arr = array(
		'res'=>"2", //用户不存在
	);
}
$conn->close();
echo json_encode($arr);
//返回当前的毫秒时间戳
function msectime() {
	list($msec, $sec) = explode(' ', microtime());
	$msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	return $msectime;
}
?>