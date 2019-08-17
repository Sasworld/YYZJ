<?php
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	if($array_data["flags"] == 1){ // 激活会员
		$uid = $array_data["uid"];
		$memberid = $array_data["memberid"];
		$sql_get_active_code = "select activecode from user where uid='$uid'";
		$res_active_code = $conn->query($sql_get_active_code);
		if ($res_active_code->num_rows > 0) {
			$row = $res_active_code->fetch_assoc();
			$active_code = $row['activecode'];
			if($active_code > 0){
				// 开始事务
				mysqli_query($conn, 'BEGIN');
				mysqli_query($conn,"update user set state='1' where uid='$memberid';");
				$affectRow = mysqli_affected_rows($conn);
				if ($affectRow == 0 || mysqli_errno($conn)) {  
				    // 回滚事务重新提交  
				    mysqli_query($conn, 'ROLLBACK');  
				    $arr = array(
						'res'=>"激活失败，请稍后重试", 
					);
				} else {
					mysqli_query($conn,"update user set activecode=activecode-1 where uid='$uid';");
					$affectRow = mysqli_affected_rows($conn);
					if ($affectRow == 0 || mysqli_errno($conn)) {  
					    // 回滚事务重新提交  
					    mysqli_query($conn, 'ROLLBACK');
					    $arr = array(
							'res'=>"激活失败，请稍后重试", 
						);
					} else {
						mysqli_query($conn, 'COMMIT');
						$arr = array(
							'res'=>"激活成功", 
						);
					}
				}
			}else{
				$arr = array(
					'res'=>"激活码不足",
				);
			}
		} else {
		    $arr = array(
				'res'=>"查询失败，请重试",
			);
		}
		// 修改未激活会员状态
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 2){ // 交割 
//		$uid = "17512552723";
//		$last_money = 0;
//		$traderid = "15725123131";
//		$trademoney = "3000";
//		$paidanid = "PD259838";
		$uid = $array_data["uid"];
		$trademoney = $array_data["trademoney"];
		$paidanid = $array_data["paidanid"];
		$paytype = $array_data["paytype"];
		// 开始事务
		mysqli_query($conn, 'BEGIN');
		$sql = "select pduid from paidan where pdid='$paidanid'";
		$res = $conn->query($sql);
		if ($res->num_rows > 0) {
		    $row = $res->fetch_assoc();
			$traderid=$row["pduid"];
			if($paytype == "1"){
				$sql_get_principal = "select principal from user where uid='$uid'";
				// 查询用户本金是否充足
				$res_get_principal = $conn->query($sql_get_principal);
				if ($res_get_principal->num_rows > 0) {
				    $row_get_principal = $res_get_principal->fetch_assoc();
					$curr_principal=$row_get_principal["principal"];
					$arr = jiaoge($conn, $curr_principal, $trademoney);
				}else{
					$arr = array(
						'result'=>"账户查询失败", 
					);
				}
			}else{
				$sql_get_principal = "select bonus from user where uid='$uid'";
				// 查询用户奖金是否充足
				$res_get_principal = $conn->query($sql_get_principal);
				if ($res_get_principal->num_rows > 0) {
				    $row_get_principal = $res_get_principal->fetch_assoc();
					$curr_principal=$row_get_principal["bonus"];
					$sql_get_access = "select * from orderform where buyerid='$uid' and orderstate='1'";
					$res_get_access = $conn->query($sql_get_access);
					if($res_get_access->num_rows > 0){
						$arr = jiaoge($conn, $curr_principal, $trademoney);
					}else{
						$arr = array(
							'result'=>"无成长中的订单，不可使用奖金交割", 
						);
					}
				}else{
					$arr = array(
						'result'=>"账户查询失败", 
					);
				}
			}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 3){ // 确认收款
		$uid = $array_data["uid"];
		$orderid = $array_data["orderid"];
		// 修改未激活会员状态
		$finish_op = mysqli_query($conn,"update orderform set shdate=NOW(),finishstate='2' where sellerid='$uid' and orderid='$orderid'");
		if($finish_op){
			$arr = array(
				'result'=>"已确认收款", 
			);
		}else{
			$arr = array(
				'result'=>"收款失败", 
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 4){ // 激活码转账
		$uid = $array_data["uid"];
		$uname = $array_data["uname"];
		$to_amount = $array_data["to_amount"];
//		$uid = "15755511770";
//		$uname = "hong";
//		$to_amount = "5";
		$sql_query_user = "select uid from user where uname='$uname'";
		$res_query_user = $conn->query($sql_query_user);
		if ($res_query_user->num_rows > 0) {
			$sql_get_active_code = "select activecode from user where uid='$uid'";
			$res_active_code = $conn->query($sql_get_active_code);
			if ($res_active_code->num_rows > 0) {
				$row = $res_active_code->fetch_assoc();
				$active_code = $row['activecode'];
				if($active_code > $to_amount){
					// 修改当前用户的激活码数量
					$finish_op_red = mysqli_query($conn,"update user set activecode=activecode-'$to_amount' where uid='$uid'");
					if($finish_op_red){
						// 增加被转账用户的激活码
						$finish_op_add = mysqli_query($conn,"update user set activecode=activecode+'$to_amount' where uname='$uname'");
						if($finish_op_add){
							$arr = array(
								'result'=>"转账成功", 
							);
						}else{
							$arr = array(
								'result'=>"转账失败，请联系客服", 
							);
						}
					}else{
						$arr = array(
							'result'=>"转账失败，请重试", 
						);
					}
				}else{
					$arr = array(
						'result'=>"激活码数量不足", 
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
	}else if($array_data["flags"] == 5){ // 排单币转账
		$uid = $array_data["uid"];
		$uname = $array_data["uname"];
		$to_amount = $array_data["to_amount"];
//		$uid = "15755511770";
//		$uname = "sai";
//		$to_amount = "100";
		$sql_query_user = "select uid from user where uname='$uname'";
		$res_query_user = $conn->query($sql_query_user);
		if ($res_query_user->num_rows > 0) {
			$sql_get_active_code = "select paidancoin from user where uid='$uid'";
			$res_active_code = $conn->query($sql_get_active_code);
			if ($res_active_code->num_rows > 0) {
				$row = $res_active_code->fetch_assoc();
				$paidan_coin = $row['paidancoin'];
				if($paidan_coin > $to_amount){
					// 修改当前用户的排单币数量
					$finish_op_red = mysqli_query($conn,"update user set paidancoin=paidancoin-'$to_amount' where uid='$uid'");
					if($finish_op_red){
						// 增加被转账用户的排单币
						$finish_op_add = mysqli_query($conn,"update user set paidancoin=paidancoin+'$to_amount' where uname='$uname'");
						if($finish_op_add){
							$arr = array(
								'result'=>"转账成功", 
							);
						}else{
							$arr = array(
								'result'=>"转账失败，请联系客服", 
							);
						}
					}else{
						$arr = array(
							'result'=>"转账失败，请重试", 
						);
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
	}
	
	// 获取用户的最近排单金额
	function get_last_paidan($conn, $querySQL){
		$user_last_paidan_result = $conn->query($querySQL);
		if($user_last_paidan_result->num_rows > 0){
			$user_last_paidan_data = $user_last_paidan_result->fetch_assoc();
			$last_money = $user_last_paidan_data['pdamount'];
		}else{
			$last_money = 0;
		}
		return $last_money;
	}
	//交割
	function jiaoge($conn, $curr_principal, $trademoney){
		if($curr_principal >= $trademoney){
				// 查看需求总额
				$sql_get_needamount = "select needamount,version from paidan where pdid='$paidanid'"; // 
				$res_get_needamount = $conn->query($sql_get_needamount);
				if ($res_get_needamount->num_rows > 0) {
				    $row_get_needamount = $res_get_needamount->fetch_assoc();
					$needamount=$row_get_needamount["needamount"];
					$curr_version = $row_get_needamount["version"]; // 记录当前版本号
					if($needamount >= $trademoney){
						// 生成订单编号
						$curr_time = substr(time()+8*3600,-4);
						$rdnumber = rand(10,99); // 生成两位随机数
						$orderid = "JG".$curr_time.$rdnumber;
						$sql_check_orderid = "select orderid from orderform where orderid='$orderid'"; //检查当前id是否存在
						$result = $conn->query($sql_check_orderid);
						// 如果存在,重新生成
						while($result->num_rows > 0){
							$curr_time = substr(time()+8*3600,-4);
							$rdnumber = rand(10,99); // 生成两位随机数
							$orderid = "JG".$curr_time.$rdnumber;
							$sql_check_orderid = "select orderid from orderform where orderid='$orderid'"; //检查当前id是否存在
							$result = $conn->query($sql_check_orderid);
						}
						// 计算各级奖励
						$parents_id = array("", "",""); // 父级id，默认为空, 不包括直接父级，直接父级单独存储
						$sql_get_parent_tree = "select parenttree from user where uid='$traderid'"; // 获取父级树
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
						$dirparentid =  $parents_id[0];
						$second_pid = $parents_id[1];
						$third_pid = $parents_id[2];
						$second_reward = 0;
						$third_reward = 0;
						// 获取父级最近一次排单的金额
						$sql_dp_last_money = "select pdamount from paidan where pduid='$dirparentid' and pddate<NOW() order by pddate desc";
						$dp_last_money = get_last_paidan($conn, $sql_dp_last_money);
						// 获取二代父级最近一次排单的金额
						$sql_sec_last_money = "select pdamount from paidan where pduid='$second_pid' and pddate<NOW() order by pddate desc";
						$sec_last_money = get_last_paidan($conn, $sql_sec_last_money);
						// 获取三代...
						$sql_third_last_money = "select pdamount from paidan where pduid='$third_pid' and pddate<NOW() order by pddate desc";
						$third_last_money = get_last_paidan($conn, $sql_third_last_money);
						
						$reward = min($dp_last_money, $trademoney) * 0.05;
						// 计算二级父级的奖励
						if($second_pid != ""){
							$sql_get_parent_team_member = "select count(uid) as memberamount from user where parenttree like '%$second_pid%'"; // 团队数量
							$sql_get_parent_dir_memeber = "select count(uid) as dirmemberamount from user where parentid='$second_pid'"; // 直推人数
							$res_get_parent_team_member = $conn->query($sql_get_parent_team_member);
							$res_get_parent_dir_memeber = $conn->query($sql_get_parent_dir_memeber);
							if($res_get_parent_team_member->num_rows > 0){
								$row_get_parent_team_member= $res_get_parent_team_member->fetch_assoc();
								$second_member_amount = $row_get_parent_team_member['memberamount'];
							}else{
								$second_member_amount = 0;
							}
							if($res_get_parent_dir_memeber->num_rows > 0){
								$row_get_parent_dir_memeber= $res_get_parent_dir_memeber->fetch_assoc();
								$second_dir_member_amount = $row_get_parent_dir_memeber['dirmemberamount'];
							}else{
								$second_dir_member_amount = 0;
							}
//								if($second_member_amount >= 20 && $second_dir_member_amount >= 5){
								$second_reward = min($sec_last_money, $trademoney) * 0.03;
//								}
							if($third_pid != ""){
								// 计算三级父代奖励
								$sql_get_parent_team_member = "select count(uid) as memberamount from user where parenttree like '%$third_pid'"; // 团队数量
								$sql_get_parent_dir_memeber = "select count(uid) as dirmemberamount from user where parentid='$third_pid'"; // 直推人数
								$res_get_parent_team_member = $conn->query($sql_get_parent_team_member);
								$res_get_parent_dir_memeber = $conn->query($sql_get_parent_dir_memeber);
								if($res_get_parent_team_member->num_rows > 0){
									$row_get_parent_team_member= $res_get_parent_team_member->fetch_assoc();
									$third_member_amount = $row_get_parent_team_member['memberamount'];
								}else{
									$third_member_amount = 0;
								}
								if($res_get_parent_dir_memeber->num_rows > 0){
									$row_get_parent_dir_memeber= $res_get_parent_dir_memeber->fetch_assoc();
									$third_dir_member_amount = $row_get_parent_dir_memeber['dirmemberamount'];
								}else{
									$third_dir_member_amount = 0;
								}
//									if($third_member_amount >= 100 && $third_dir_member_amount >= 10){
									$third_reward = min($third_last_money, $trademoney) * 0.01;
//									}
							}
						}
						// 添加新订单
						$sql = "insert into orderform(orderid, paidanid, buyerid, sellerid, principal, reward, secreward, thirdreward) values('$orderid','$paidanid', '$traderid','$uid', '$trademoney', '$reward', '$second_reward', '$third_reward')";
						$finish_op = $conn->query($sql);
						if($finish_op){
							// 更新交易对象的排单信息
							mysqli_query($conn,"update paidan set needamount=needamount-'$trademoney', version=version+1 where pduid='$traderid' and version='$curr_version'");
							$update_affect_row = mysqli_affected_rows($conn);  
							if ($update_affect_row == 0 || mysqli_errno($conn)) {  
							    // 回滚事务重新提交  
							    mysqli_query($conn, 'ROLLBACK');
							    $arr = array(
									'result'=>"服务器繁忙，请重新提交", 
								);
							} else {  
							    mysqli_query($conn, 'COMMIT');  
							    // 更新用户的账户
								//mysqli_query($conn,"update user set principal=principal+'$trademoney' where uid='$traderid'");
								$finishmin_prin = mysqli_query($conn,"update user set principal=principal-'$trademoney' where uid='$uid'");
								if($finishmin_prin){
									// 操作记录，3：交割
									$opremarks = "交割：";
									$opsql = "insert into oprecord(uid, optype, opedid, opremarks) values('$uid','3', 'paidanid', '$opremarks')";
									$conn->query($opsql);
									$arr = array(
										'result'=>"交割成功", 
									);
								}else{
									// 回滚事务重新提交  
								    mysqli_query($conn, 'ROLLBACK');
								    $arr = array(
										'result'=>"服务器繁忙，请重新提交", 
									);
								}
							}
						}else{
							$arr = array(
								'result'=>"交割失败，请重新提交", 
							);
						}
					}else{
						$arr = array(
							'result'=>"超出需求总额", 
						);
					}
				}else{
					$arr = array(
						'result'=>"订单查询失败", 
					);
				}
			}else{
				$arr = array(
					'result'=>"本金不足".$curr_principal, 
				);
			}
		}
		return $arr;
	}
?>