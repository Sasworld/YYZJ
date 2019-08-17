<?php
	// ===================== 确认收款 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	//$uid = "17512552723";
	//$orderid = "JG944863";
	$uid = $array_data["uid"];
	$orderid = $array_data["orderid"];
	// 修改未激活会员状态
	$finish_op = mysqli_query($conn,"update orderform set shdate=NOW(),finishstate='2' where sellerid='$uid' and orderid='$orderid'");
	if($finish_op){
		$arr = array(
			'result'=>"已确认收款", 
		);
	}else{
		$arr = array(
			'result'=>"收款失败", 
		);
	}
	$conn->close();
	echo json_encode($arr);
?>