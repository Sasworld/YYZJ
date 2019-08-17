<?php
	// ===================== 本息提现 ========================= //
	
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$total_money = $array_data["total_money"];
	$orderid = $array_data["orderid"];
	
//	$uid = "13739182075";
//	$total_money = "5500";
//	$orderid = "JG855118";
	
	$sql = "select principal, reward, secreward, thirdreward from orderform where buyerid='$uid' and orderid='$orderid'"; // 获取需要提现的订单的本金
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
	    // 输出数据
	    while($row = $result->fetch_assoc()) {
	    	$principal = $row["principal"];
	    	$reward = $row["reward"];
	    	$secreward = $row["secreward"];
	    	$thirdreward = $row["thirdreward"];
//	    	if($total_money > 1.15*$principal){ // 超出最大值，有问题
//				$arr = array(
//					'result'=>$test, 
//				);
//			}else{ // 金额正常，进行提现操作
				// 开始事务
				mysqli_query($conn, 'BEGIN');
				// 加入本息金额中
				mysqli_query($conn,"update user set principal=principal+'$total_money' where uid='$uid';");
				$affectRow = mysqli_affected_rows($conn);
				if ($affectRow == 0 || mysqli_errno($conn)) {  
				    // 回滚事务重新提交  
				    mysqli_query($conn, 'ROLLBACK');
				    $arr = array(
						'result'=>"提现失败,请重试2", 
					);
				} else {
					// 修改订单状态
					mysqli_query($conn,"update orderform set orderstate='2',txdate=now() where orderid='$orderid';");
					$affectRow = mysqli_affected_rows($conn);
					if ($affectRow == 0 || mysqli_errno($conn)) {  
					    // 回滚事务重新提交  
					    mysqli_query($conn, 'ROLLBACK');
					    $arr = array(
							'result'=>"提现失败,请重试3", 
						);
					} else {
						// 发放父级奖金
						$parents_id = get_user_parents($conn, $uid);
						$dir_parent = $parents_id[0];
						$sec_parent = $parents_id[1];
						$third_parent = $parents_id[2];
						$finishstate1 = alter_parent_bonus($conn, $dir_parent, $reward);
						$finishstate2 = alter_parent_bonus($conn, $sec_parent, $secreward);
						$finishstate3 = alter_parent_bonus($conn, $third_parent, $thirdreward);
						if(!$finishstate1 || !$finishstate2 || !$finishstate3){
							// 回滚事务重新提交  
							mysqli_query($conn, 'ROLLBACK');
							$arr = array(
								'result'=>"提现失败,请重试4", 
							);
						}else{
							// 加入用户操作记录 1：提现
							$opsql = "insert into oprecord(uid, optype, opedid, opremarks) values('$uid','1', '$orderid', '$total_money')";
							$conn->query($opsql);
							$affectRow = mysqli_affected_rows($conn);
							if ($affectRow == 0 || mysqli_errno($conn)) {  
							    // 回滚事务重新提交  
							    mysqli_query($conn, 'ROLLBACK');
							    $arr = array(
									'result'=>"提现失败,请重试5", 
								);
							} else {
								mysqli_query($conn, 'COMMIT');
								$arr = array(
									'result'=>"提现成功", 
								);
							}
						}
					}
				}
//			}
	    }
	}
	$conn->close();
	echo json_encode($arr);
		// 获取用户的父级
	function get_user_parents($conn,$uid){
		$parents_id = array("", "",""); // 父级id，默认为空, 不包括直接父级，直接父级单独存储
		$sql_get_parent_tree = "select parenttree from user where uid='$uid'"; // 获取父级树
		$res_get_parent_tree = $conn->query($sql_get_parent_tree);
		if($res_get_parent_tree->num_rows > 0){
			$row_get_parent_tree = $res_get_parent_tree->fetch_assoc();
			$parent_tree = $row_get_parent_tree['parenttree'];
		}else{
			$parent_tree = "";
		}
		$ids = explode('/',$parent_tree);
		$parent_amount = sizeof($ids); // 父级层数
		for($i = 0; $i < $parent_amount; $i++){
			$parents_id[$i] = $ids[$i];
		}
		return $parents_id;
	}
	
		// 修改奖励
	function alter_parent_bonus($conn, $uid, $reward){
		$sql_update_bonus = "update user set bonus=bonus+'$reward' where uid='$uid'";
		$finish_state = mysqli_query($conn, $sql_update_bonus);
		return $finish_state;
	}
?>