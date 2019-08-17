<?php
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	mysqli_query($conn,"update user set loginstate='0' where uid='$uid';");
	$affectRow = mysqli_affected_rows($conn);
	if ($affectRow == 0 || mysqli_errno($conn)) {  
	    $arr = array(
			'res'=>"2", 
		);
	} else {
		$arr = array(
			'res'=>"1", 
		);
	}
	$conn->close();
	echo json_encode($arr);
?>