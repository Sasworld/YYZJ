<?php
	// =================================== 获取交易区的排单 ============================= //
	include("connSql.php");
//	$uid = "13739182075";
//	$chooseid = "3";
//	$keywords = "";
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$chooseid = $array_data["chooseid"];
	$keywords = $array_data["keywords"];
	
	$sql_get_max_amount = "select * from setting";
	$result = $conn->query($sql_get_max_amount);
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$best_pd_amount = $row["bestpdamount"];
		$rand_pd_amount = $row["randpdamount"];		
	}else{
		$best_pd_amount = 0;
		$rand_pd_amount = 0;
	}
	if($chooseid == 1){
		$sql = "select pdid, uname, stars, uid, needamount from user,paidan where pduid=uid and needamount>'0' order by pddate asc limit $best_pd_amount";
	}else if($chooseid == 2){
		$sql = "select pdid, uname, stars, uid, needamount from user,paidan where pduid=uid and needamount=pdamount order by rand() limit $rand_pd_amount";
	}else if($chooseid == 3){
		$sql = "select pdid, uname, stars, uid, needamount from user,paidan where pduid=uid and uname like '%$keywords%' and needamount>'0'";
	}
	$result = $conn->query($sql);
	$jsonArray = array();
//	$res_id = "";
	if ($result->num_rows > 0) {
	    // 输出数据
	    while($row = $result->fetch_assoc()) {
	    	$arr = array(
				'pdid'=>$row["pdid"],
				'uname'=>$row["uname"],
				'stars'=>$row["stars"],
				'uid'=>$row["uid"],
				'needamount'=>$row["needamount"],
			);
			$jsonArray[] = ($arr);
//			$res_id = $res_id. $row["pdid"]. ";" ;
	    }
	}
//	if($chooseid == 3){
//		// 操作记录， 2：查询排单账户
//		$opremarks = "查询账户关键字：" . $keywords;
//		$opsql = "insert into oprecord(uid, optype, opedid, opremarks) values('$uid','2', '$res_id', '$opremarks')";
//		$conn->query($opsql);
//	}
	$conn->close();
	echo json_encode($jsonArray);
?>