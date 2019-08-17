<?php
	// ===================== 获取上次交割时间 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	if ($array_data["flags"] == 1) {
		$uid= $array_data["uid"];
		$sql_get_jg_time = "select orderdate from orderform where sellerid='$uid' and orderdate<NOW() order by orderdate desc limit 1";
		$res_get_jg_time = $conn->query($sql_get_jg_time);
		if ($res_get_jg_time->num_rows > 0) {
			$row_get_jg_time = $res_get_jg_time->fetch_assoc();
			$arr = array(
				'res'=>"1",
				'orderdate'=>$row_get_jg_time['orderdate'],
				'currdate'=>date('Y-m-d H:i:s', time()),
			);
		} else {
		    $arr = array(
				'res'=>"2",
			);
		}
		$conn->close();
		echo json_encode($arr);
	}
?>