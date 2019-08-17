<?php
	// ===================== 获取正在进行中的订单 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$sql = "select orderid,buyerid,sellerid,principal,orderdate from orderform where (buyerid='$uid' or sellerid='$uid') and finishstate<'2' order by orderstate desc";
	$jsonArray = array();
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
	    // 输出数据
	    while($row = $result->fetch_assoc()) {
	    	$arr = array(
				'orderid'=>$row["orderid"],
				'buyerid'=>$row["buyerid"],
				'sellerid'=>$row["sellerid"],
				'principal'=>$row["principal"],
				'orderdate'=>$row["orderdate"],
			);
			$jsonArray[] = ($arr);
	    }
	}
	$conn->close();
	echo json_encode($jsonArray);
?>