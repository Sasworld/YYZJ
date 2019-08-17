<?php
	// ===================== 提交交割 ========================= //
	include("connSql.php");
	include("../aliyun-dysms-php-sdk/api_demo/SmsDemo.php");
	//		$uid = "17512552723";
	//		$traderid = "15755511770";
	//		$trademoney = "1000";
	//		$paidanid = "PD833276";
	//		$paytype = "1";
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$trademoney = $array_data["trademoney"];
	$paidanid = $array_data["paidanid"];
	$paytype = $array_data["paytype"];
	// 服务端交割时间检查
	$start_time = "09:00:00";
	$end_time = "21:00:00";
	$curr_time = date('H:i:s', time());
	if($curr_time<$end_time && $curr_time >$start_time){
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
					$arr = jiaoge($conn, $curr_principal, $trademoney, $paidanid, $uid, $traderid, "1");
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
						$arr = jiaoge($conn, $curr_principal, $trademoney, $paidanid, $uid, $traderid, "2");

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
		}else{
			$arr = array(
				'result'=>"账户查询失败", 
			);
		}
	}else{
		$arr = array(
			'result'=>"非交割时间", 
		);
	}
	$conn->close();
	echo json_encode($arr);
	
	//交割
	function jiaoge($conn, $curr_principal, $trademoney, $paidanid, $uid, $traderid, $paytype){
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
							$sql_get_parent_team_member = "select count(uid) as memberamount from user where parenttree like '%$second_pid%' and state='1'"; // 团队数量
							$sql_get_parent_dir_memeber = "select count(uid) as dirmemberamount from user where parentid='$second_pid' and state='1'"; // 直推人数
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
							if($second_member_amount >= 20 && $second_dir_member_amount >= 5){
								$second_reward = min($sec_last_money, $trademoney) * 0.03;
							}
							if($third_pid != ""){
								// 计算三级父代奖励
								$sql_get_parent_team_member = "select count(uid) as memberamount from user where parenttree like '%$third_pid' and state='1'"; // 团队数量
								$sql_get_parent_dir_memeber = "select count(uid) as dirmemberamount from user where parentid='$third_pid' and state='1'"; // 直推人数
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
								if($third_member_amount >= 100 && $third_dir_member_amount >= 10){
									$third_reward = min($third_last_money, $trademoney) * 0.01;
								}
							}
						}
						// 开始事务
						mysqli_query($conn, 'BEGIN');
						// 添加新订单
						$sql = "insert into orderform(orderid, paidanid, buyerid, sellerid, principal, reward, secreward, thirdreward) values('$orderid','$paidanid', '$traderid','$uid', '$trademoney', '$reward', '$second_reward', '$third_reward')";
						$conn->query($sql);
						$affectRow = mysqli_affected_rows($conn);
						if ($affectRow == 0 || mysqli_errno($conn)) {
							// 回滚事务重新提交  
					    	mysqli_query($conn, 'ROLLBACK');
							$arr = array(
								'result'=>"服务器繁忙，请重新提交", 
							);
						}else{
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
							    // 更新用户的账户
								//mysqli_query($conn,"update user set principal=principal+'$trademoney' where uid='$traderid'");
								if($paytype == "1"){
								    mysqli_query($conn,"update user set principal=principal-'$trademoney' where uid='$uid'");
								}else{
							            mysqli_query($conn,"update user set bonus=bonus-'$trademoney' where uid='$uid'");
								}
								
								$update_affect_row = mysqli_affected_rows($conn);  
								if ($update_affect_row == 0 || mysqli_errno($conn)) {  
								    // 回滚事务重新提交  
								    mysqli_query($conn, 'ROLLBACK');
								    $arr = array(
										'result'=>"服务器繁忙，请重新提交", 
									);
								} else {
									mysqli_query($conn, 'COMMIT');
									$response = SmsDemo::sendSms($traderid);
									$arr = array(
										'result'=>"交割成功", 
									);
								}
							}
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
					'result'=>"本金不足", 
				);
			}
			return $arr;
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
?>