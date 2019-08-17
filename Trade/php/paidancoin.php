<?php
	// ===================== 排单币转账 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$uname = $array_data["uname"];
	$to_amount = $array_data["to_amount"];
	//		$uid = "15755511770";
	//		$uname = "sai";
	//		$to_amount = "100";
	$sql_query_user = "select uid from user where uname='$uname'";
	$res_query_user = $conn->query($sql_query_user);
	if ($res_query_user->num_rows > 0) {
		// 开始事务
		mysqli_query($conn, 'BEGIN');
		$sql_get_active_code = "select paidancoin from user where uid='$uid'";
		$res_active_code = $conn->query($sql_get_active_code);
		if ($res_active_code->num_rows > 0) {
			$row = $res_active_code->fetch_assoc();
			$paidan_coin = $row['paidancoin'];
			if($paidan_coin > $to_amount){
				// 修改当前用户的排单币数量
				mysqli_query($conn,"update user set paidancoin=paidancoin-'$to_amount' where uid='$uid'");
				$affectRow = mysqli_affected_rows($conn);
				if ($affectRow == 0 || mysqli_errno($conn)) {  
				    // 回滚事务重新提交  
				    mysqli_query($conn, 'ROLLBACK');  
				} else {
					// 增加被转账用户的排单币
					$finish_op_add = mysqli_query($conn,"update user set paidancoin=paidancoin+'$to_amount' where uname='$uname'");
					$affectRow = mysqli_affected_rows($conn);
					if ($affectRow == 0 || mysqli_errno($conn)) {
						// 回滚事务重新提交  
				    	mysqli_query($conn, 'ROLLBACK');
						$arr = array(
							'result'=>"转账失败，请联系客服", 
						);
					}else{
						// 记录转账
						$opremarks = "转".$to_amount."个排单币给：" .$uname;
						$opsql = "insert into oprecord(uid, optype, opedid, opremarks) values('$uid','4', '$uname', '$opremarks')";
						$conn->query($opsql);
						$affectRow = mysqli_affected_rows($conn);
						if ($affectRow == 0 || mysqli_errno($conn)) {  
						    // 回滚事务重新提交  
						    mysqli_query($conn, 'ROLLBACK');
						    $arr = array(
								'result'=>"转账失败", 
							);
						} else {
							mysqli_query($conn, 'COMMIT');
							$arr = array(
								'result'=>"转账成功", 
							);
						}
					}
				}
			}else{
				$arr = array(
					'result'=>"排单币数量不足", 
				);
			}
		}else{
			$arr = array(
				'result'=>"查询失败，请重试", 
			);
		}
	}else{
		$arr = array(
			'result'=>"用户不存在", 
		);
	}
	$conn->close();
	echo json_encode($arr);
?>