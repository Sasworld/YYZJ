<?php   
include("connSql.php");
$string_data = $_POST['datas']; //获取插入的表的索引
$array_data = json_decode($string_data, true);
//$uid = $array_data["uid"];
//$uname = $array_data["uname"];
//$to_amount = $array_data["to_amount"];
$uid = "17512552723";
$uname = "hong";
$to_amount = "5";
$sql_query_user = "select uid from user where uname='$uname'";
$res_query_user = $conn->query($sql_query_user);
if ($res_query_user->num_rows > 0) {
	// 开始事务
	mysqli_query($conn, 'BEGIN');
	$sql_get_active_code = "select activecode from user where uid='$uid'";
	$res_active_code = $conn->query($sql_get_active_code);
	if ($res_active_code->num_rows > 0) {
		$row = $res_active_code->fetch_assoc();
		$active_code = $row['activecode'];
		if($active_code > $to_amount){
			// 修改当前用户的激活码数量
			$finish_op_red = mysqli_query($conn,"update user set activecode=activecode-'$to_amount' where uid='$uid'");
			$affectRow = mysqli_affected_rows($conn);
			if ($affectRow == 0 || mysqli_errno($conn)) {  
			    // 回滚事务重新提交  
			    mysqli_query($conn, 'ROLLBACK');  
			} else {
				// 增加被转账用户的激活码
				mysqli_query($conn,"update user set activecode=activecode+'$to_amount' where uname='hong'");
				$affectRow = mysqli_affected_rows($conn);
				if ($affectRow == 0 || mysqli_errno($conn)) {
					// 回滚事务重新提交  
			    	mysqli_query($conn, 'ROLLBACK');
					$arr = array(
						'result'=>"转账失败，请联系客服2", 
					);
				}else{
					$arr = array(
						'result'=>"转账成功1", 
					);
					mysqli_query($conn, 'COMMIT');
				}  
			}
		}else{
			$arr = array(
				'result'=>"激活码数量不足3", 
			);
		}
	}else{
		$arr = array(
			'result'=>"查询失败，请重试4", 
		);
	}
}else{
	$arr = array(
		'result'=>"用户不存在5", 
	);
}
$conn->close();
echo json_encode($arr);