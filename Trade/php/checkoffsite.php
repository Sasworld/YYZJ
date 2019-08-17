<?php
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$sql = "select loginstate from user where uid='$uid'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$loginstate = $row["loginstate"];
	}else{
		$loginstate = "";
	}
	session_start(); // 初始化session
	$last_session = $_SESSION['curr_user_session'];
	if($last_session != $loginstate){
		$arr = array(
			'res'=>"2", // 登陆过期，请重新登陆
		);
	}else{
		$arr = array(
			'res'=>"1",
		);
	}
	echo json_encode($arr);
?>