<?php
	// ===================== 获取团队成员信息 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$sql_active_code = "select activecode from user where uid='$uid'";
	$code_result = $conn->query($sql_active_code);
	$code_data = $code_result->fetch_assoc();
	$active_code_amount = $code_data["activecode"];
	$jsonArray = array();
	// 激活码数量作为第一条信息传回
	$arr = array(
		'activecode'=>$code_data["activecode"],
	);
	$jsonArray[] = ($arr);
	$sql_dir = "select uid, uname, state from user where parentid='$uid' order by state asc"; // 直推
	$sql_sec = "select uid, uname, state from user where parenttree like '____________$uid%' order by state asc"; // 二代
	$sql_third = "select uid, uname, state from user where parenttree like '________________________$uid' order by state asc"; //三代
	$jsonArray = get_team_member($conn, $uid, $sql_dir, "直推", $jsonArray);
	$jsonArray = get_team_member($conn, $uid, $sql_sec, "二代", $jsonArray);
	$jsonArray = get_team_member($conn, $uid, $sql_third, "三代", $jsonArray);
	$conn->close();
	echo json_encode($jsonArray);
	
	// 获取用户团队成员
	function get_team_member($conn, $uid, $sql, $degree, $jsonArray){
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    // 输出数据
		    while($row = $result->fetch_assoc()) {
		    	$arr = array(
					'uid'=>$row["uid"],
					'uname'=>$row["uname"],
					'relation'=>$degree,
					'state'=>$row["state"],
				);
				$jsonArray[] = ($arr);
		    }
		}
		return $jsonArray;
	}
?>