<?php
	// ===================== 提交排单 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$pdamount = $array_data["pdamount"];
	//$uid = "17512552723";
	//$pdamount = "8000";
	// 查询你是否排过单
	$sql_query_pd = "select MAX(pdamount) as pdamount from paidan where pduid='$uid' and pddate<NOW()";
	$res_query_pd = $conn->query($sql_query_pd);
	if($res_query_pd->num_rows == 0 && ($pdamount < 1000 || $pdamount > 10000)){
		$arr = array(
			'result'=>"首次排单，金额必须为：1000-10000",
		);
	}else{
		// 上次排单金额
		$row_last_pdamount = $res_query_pd->fetch_assoc();
		$last_pdamount = $row_last_pdamount["pdamount"];
		
		$sql_get_stars = "select stars from user where uid='$uid'";
		$res_get_stars = $conn->query($sql_get_stars);
		$row_get_stars = $res_get_stars->fetch_assoc();
		$stars = $row_get_stars["stars"];
		$top_pdamount = $stars*1000;
		if($pdamount > $top_pdamount || $pdamount < $last_pdamount){
			$arr = array(
				'result'=>"您的排单金额范围为：".$last_pdamount."-".$top_pdamount,
			);
		}else{
			// 判断是否有未完成的排单
			$sql_check_pd = "select * from paidan where pduid='$uid' and needamount>0";
			$res_check_pd = $conn->query($sql_check_pd);
			if($res_check_pd->num_rows>0){
				$arr = array(
					'result'=>"您有尚未完成的排单",
				);
			}else{
				$handlingFee = $pdamount * 0.01; // 应扣除的排单币数量
				// 构造排单号
				$curr_time = substr(time()+8*3600,-4);
				$rdnumber = rand(10,99); // 生成两位随机数
				$pdid = "PD".$curr_time.$rdnumber;
				$sql_check_pdid = "select pdid from paidan where pdid='$pdid'"; //检查当前id是否存在
				$result = $conn->query($sql_check_pdid);
				// 如果存在,重新生成
				while($result->num_rows > 0){
					$curr_time = substr(time()+8*3600,-4);
					$rdnumber = rand(10,99); // 生成两位随机数
					$pdid = "PD".$curr_time.$rdnumber;
					$sql_check_pdid = "select pdid from paidan where pdid='$pdid'"; //检查当前id是否存在
					$result = $conn->query($sql_check_pdid);
				}
				// 开启事务
				mysqli_query($conn, 'BEGIN');
				$sql = "insert into paidan(pdid, pdamount, needamount, pduid) values('$pdid', '$pdamount', '$pdamount', '$uid')";
				if ($conn->query($sql) == TRUE) {
				    $arr = array(
						'result'=>"排单提交成功",
					);
					//会不会出现更新失败的情况
					$finish_minus = mysqli_query($conn,"update user set paidancoin=paidancoin-'$handlingFee' where uid='$uid';");
					if($finish_minus){
						mysqli_query($conn, 'COMMIT');
					}else{
						// 回滚事务重新提交  
						mysqli_query($conn, 'ROLLBACK');
					    $arr = array(
							'result'=>"排单提交失败，请稍后重试",
						);
					}
				} else {
					// 回滚事务重新提交  
					mysqli_query($conn, 'ROLLBACK');
				    $arr = array(
						'result'=>"排单提交失败，请稍后重试",
					);
				}
			}
		}
	}
	$conn->close();
	echo json_encode($arr);
?>