<?php
	include("connSql.php");
	$uid = "17071340069";
	$sql = "select * from orderform where buyerid='$uid' and finishstate='2' order by orderstate asc";
	$result = $conn->query($sql);
	$jsonArray = array();
	if ($result->num_rows > 0) {
	    // 输出数据
	    while($row = $result->fetch_assoc()) {
	    	$arr = array(
				'orderid'=>$row["orderid"],
				'principal'=>$row["principal"],
				'sellerid'=>$row["sellerid"],
				'orderstate'=>$row["orderstate"],
				'shdate'=>$row["shdate"],
				'currdate'=>date('Y-m-d H:i:s', time()),
			);
			$jsonArray[] = ($arr);
	    }
	}
	$conn->close();
	echo json_encode($jsonArray);
?>