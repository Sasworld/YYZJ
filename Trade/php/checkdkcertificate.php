<?php
	// ===================== 查看打款凭证 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$orderid = $array_data["orderid"];
	$sql = "select certificate from orderform where orderid='$orderid'";
	$result = $conn->query($sql);
	$jsonArray = array();
	if ($result->num_rows > 0) {
	    $row = $result->fetch_assoc();
	    $certificate = $row["certificate"];
	    if($certificate == null){
	    	$arr = array(
				'result'=>"2",
			);
	    }else{
	    	$arr = array(
				'result'=>"1",
				'certificate'=>$row["certificate"],
			);
	    }
	}else{
		$arr = array(
			'result'=>"3",
		);
	}
	$conn->close();
	echo json_encode($arr);
?>