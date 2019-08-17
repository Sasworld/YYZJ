<?php
// ===================== 修改密码 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$oldpw = $array_data["oldpw"];
	$newpw = $array_data["newpw"];
//	$uid = "17512552723";
//	$oldpw = "123";
//	$newpw = "12345";
	$sql_query_user = "select uid from user where uid='$uid' and upw=HEX(aes_encrypt('$oldpw', 'sasworld'))";
	$res_query_user = $conn->query($sql_query_user);
	if ($res_query_user->num_rows > 0) {
		mysqli_query($conn,"update user set upw=HEX(aes_encrypt('$newpw', 'sasworld')) where uid='$uid'");
		$affectRow = mysqli_affected_rows($conn);
		if ($affectRow == 0 || mysqli_errno($conn)) {  
		    // 回滚事务重新提交  
		    $arr = array(
				'result'=>"修改失败，请稍后重试1", 
			);
		} else {
			$arr = array(
				'result'=>"2", 
			);
		}
	}else{
		$arr = array(
				'result'=>"密码错误3", 
			);
	}
	$conn->close();
	echo json_encode($arr);
?>