<?php
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	if ($array_data["flags"] == 2) {  // 查询排单币
		$uid = $array_data["uid"];
		$queryid = $array_data["queryid"];
		if($queryid == "1"){
			$sql = "select paidancoin from user where uid='$uid'";
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
			    // 输出数据
			    while($row = $result->fetch_assoc()) {
			    	$arr = array(
						'paidancoin'=>$row["paidancoin"],
					);
			    }
			} else {
			    $arr = array(
					'paidancoin'=>"F",
				);
			}
		}else if($queryid == "2"){
			$sql = "select uname, uid, regdate, bank, banknumber, ownername, alipay from user where uid='$uid'";
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
			    // 输出数据
			    while($row = $result->fetch_assoc()) {
			    	$arr = array(
						'uname'=>$row["uname"],
						'uid'=>$row["uid"],
						'regdate'=>$row["regdate"],
						'bank'=>$row["bank"],
						'banknumber'=>$row["banknumber"],
						'ownername'=>$row["ownername"],
						'alipay'=>$row["alipay"],
						'result'=>"1",
					);
			    }
			} else {
			    $arr = array(
					'result'=>"2",
				);
			}
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 3){ // 查询官方微信
		$sql = "select wechat from setting";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    // 输出数据
		    while($row = $result->fetch_assoc()) {
		    	$arr = array(
					'result'=>$row["wechat"],
				);
		    }
		} else {
		    $arr = array(
				'result'=>"F",
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 4){ // 查询账户
		$uid = $array_data["uid"];
		$sql = "select uname from user where uid='$uid'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    // 输出数据
		    while($row = $result->fetch_assoc()) {
		    	$arr = array(
					'result'=>$row["uname"],
				);
		    }
		} else {
		    $arr = array(
				'result'=>"F",
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 5){ // 查询本息钱包
		$uid = $array_data["uid"];
		$sql = "select principal from user where uid='$uid'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    // 输出数据
		    while($row = $result->fetch_assoc()) {
		    	$arr = array(
					'result'=>$row["principal"],
				);
		    }
		} else {
		    $arr = array(
				'result'=>"F",
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 6){ // 查询冻结资金
		$uid = $array_data["uid"];
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
	}else if($array_data["flags"] == 7){ // 冻结资金提现
		$uid = $array_data["uid"];
		$total_money = $array_data["total_money"];
		$orderid = $array_data["orderid"];
		$sql = "select principal, reward, secreward, thirdreward from orderform where buyerid='$uid' and orderid='$orderid'"; // 获取需要提现的订单的本金
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    // 输出数据
		    while($row = $result->fetch_assoc()) {
		    	$principal = $row["principal"];
		    	$reward = $row["reward"];
		    	$secreward = $row["secreward"];
		    	$thirdreward = $row["thirdreward"];
		    	if($total_money > 1.15*$principal){ // 超出最大值，有问题
					$arr = array(
						'result'=>"提现失败，请重试", 
					);
				}else{ // 金额正常，进行提现操作
					// 开始事务
					mysqli_query($conn, 'BEGIN');
//					 加入本息金额中
					$finish_add_money = mysqli_query($conn,"update user set principal=principal+'$total_money' where uid='$uid';");
					if($finish_add_money){
						// 修改订单状态
						$finish_alter_order = mysqli_query($conn,"update orderform set orderstate='2',txdate=now() where orderid='$orderid';");
						if($finish_alter_order){
							// 发放父级奖金
							$parents_id = get_user_parents($uid);
							$dir_parent = $parent_id[0];
							$sec_parent = $parent_id[1];
							$third_parent = $parent_id[2];
							$finishstate1 = alter_parent_bonus($conn, $dir_parent, $reward);
							$finishstate2 = alter_parent_bonus($conn, $sec_parent, $secreward);
							$finishstate3 = alter_parent_bonus($conn, $third_parent, $thirdreward);
							if(!$finishstate1 || !$finishstate2 || !$finishstate3){
								// 回滚事务重新提交  
								mysqli_query($conn, 'ROLLBACK');
								$arr = array(
									'result'=>"提现失败,请重试", 
								);
							}else{
								// 加入用户操作记录 1：提现
								$opremarks = "提现金额：" . $total_money . ", 其中本金为：" . $row["principal"];
								$opsql = "insert into oprecord(uid, optype, opedid, opremarks) values('$uid','1', '$orderid', '$opremarks')";
								$conn->query($opsql);
								mysqli_query($conn, 'COMMIT');
								$arr = array(
									'result'=>"提现成功", 
								);
							}
						}else{
							// 回滚事务重新提交  
							mysqli_query($conn, 'ROLLBACK');
							$arr = array(
								'result'=>"提现失败,请重试", 
							);
						}
					}else{
						$arr = array(
							'result'=>"提现失败,请重试", 
						);
					}
				}
		    }
		} else {
		    $arr = array(
				'result'=>"订单查询失败，请稍后重试",
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 8){ // 
		$uid = $array_data["uid"];
		$chooseid = $array_data["chooseid"];
		$keywords = $array_data["keywords"];
		if($chooseid == 1){
			$sql = "select pdid, uname, stars, uid, needamount from user,paidan where pduid=uid and needamount>'0' order by stars desc";
		}else if($chooseid == 2){
			$sql = "select pdid, uname, stars, uid, needamount from user,paidan where pduid=uid and needamount=pdamount order by rand() limit 5";
		}else if($chooseid == 3){
			$sql = "select pdid, uname, stars, uid, needamount from user,paidan where pduid=uid and uname like '%$keywords%' and needamount>'0'";
		}
		$result = $conn->query($sql);
		$jsonArray = array();
		$res_id = "";
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
				$res_id = $res_id. $row["pdid"]. ";" ;
		    }
		}
		if($chooseid == 3){
			// 操作记录， 2：查询排单账户
			$opremarks = "查询账户关键字：" . $keywords;
			$opsql = "insert into oprecord(uid, optype, opedid, opremarks) values('$uid','2', '$res_id', '$opremarks')";
			$conn->query($opsql);
		}
		$conn->close();
		echo json_encode($jsonArray);
	}else if($array_data["flags"] == 9){ // 查询今日已用搜索次数
		$uid = $array_data["uid"];
		$sql = "select count(opid) as times from oprecord where uid='$uid' and optype='2' and TO_DAYS(opdate) = TO_DAYS(NOW())"; //可用查询次数
		$result = $conn->query($sql);
		$jsonArray = array();
		if ($result->num_rows > 0) {
		    $row = $result->fetch_assoc();
	    	$arr = array(
				'result'=>$row["times"],
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 10){ // 查询团队信息
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
	}else if($array_data["flags"] == 11){ // 查询奖金记录
		$uid = $array_data["uid"];
		$sql_active_code = "select bonus from user where uid='$uid'";
		$bonus_result = $conn->query($sql_active_code);
		$bonus_data = $bonus_result->fetch_assoc();
		$jsonArray = array();
		// 奖金作为第一条信息传回
		$arr = array(
			'bonus'=>$bonus_data["bonus"],
		);
		$jsonArray[] = ($arr);
		$sql = "select uname,orderstate,reward,txdate,orderdate from user,orderform where parentid='$uid' and uid=buyerid and finishstate='2' order by orderstate";
		$sql_sec = "select uname,orderstate,secreward as reward,txdate,orderdate from user,orderform where parenttree like '____________$uid%' and uid=buyerid and finishstate='2' order by orderstate";
		$sql_third = "select uname,orderstate,thirdreward as reward,txdate,orderdate from user,orderform where parenttree like '%$uid' and uid=buyerid and finishstate='2' order by orderstate";
		$jsonArray = get_reward_record($conn, $uid, $sql, "直推", $jsonArray);
		$jsonArray = get_reward_record($conn, $uid, $sql_sec, "二代", $jsonArray);
		$jsonArray = get_reward_record($conn, $uid, $sql_third, "三代", $jsonArray);
		$conn->close();
		echo json_encode($jsonArray);
	}else if($array_data["flags"] == 13){ // 查询正在进行的记录
		$uid = $array_data["uid"];
		$sql = "select orderid,buyerid,sellerid,principal,orderdate from orderform where buyerid='$uid' or sellerid='$uid' and finishstate<'2' order by orderstate desc";
		$jsonArray = array();
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    // 输出数据
		    while($row = $result->fetch_assoc()) {
		    	$arr = array(
					'orderid'=>$row["orderid"],
					'buyerid'=>$row["buyerid"],
					'sellerid'=>$row["sellerid"],
					'principal'=>$row["principal"],
					'orderdate'=>$row["orderdate"],
				);
				$jsonArray[] = ($arr);
		    }
		}
		$conn->close();
		echo json_encode($jsonArray);
	}else if($array_data["flags"] == 14){ // 查询打款凭证
		$orderid = $array_data["orderid"];
		$sql = "select certificate from dkcertificate where orderid='$orderid'";
		$result = $conn->query($sql);
		$jsonArray = array();
		if ($result->num_rows > 0) {
		    $row = $result->fetch_assoc();
	    	$arr = array(
	    		'result'=>"1",
				'certificate'=>$row["certificate"],
			);
		}else{
			$arr = array(
				'result'=>"2",
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 15){ // // 查看订单详情
//		$uid = "15755511770";
//		$orderid = "JG815426";
//		$ordertype = "买入";
		$uid = $array_data["uid"];
		$orderid = $array_data["orderid"];
		$ordertype = $array_data["ordertype"];
		if($ordertype == "卖出"){
			$sql = "select orderid,buyerid,sellerid,orderform.principal,orderdate,finishstate,paidanid,uname,parentid,bank,banknumber,ownername,alipay from orderform,user where sellerid='$uid' and buyerid=uid";
		}else if($ordertype == "买入"){
			$sql = "select orderid,buyerid,sellerid,orderform.principal,orderdate,finishstate,paidanid,uname,parentid,bank,banknumber,ownername,alipay from orderform,user where buyerid='$uid' and sellerid=uid";
		}
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    $row = $result->fetch_assoc();
		    // 获取对方父代信息
		    $parent_id = $row["parentid"];
		    if($parent_id != null){
		    	$sql_query_parent_name = "select uname from user where uid='$parent_id'";
		        $res_query_parent_name = $conn->query($sql_query_parent_name);
		        $row_query_parent_name = $res_query_parent_name->fetch_assoc();
		        $parent_name = $row_query_parent_name["uname"];
		    }else{
		    	$parent_name = "";
		    }
		    // 获取当前用户的父代信息
		    $sql_query_curr_parent = "select user2.uid as cpuid,user2.uname as cpuname from user as user1,user as user2 where user1.uid='$uid' and user2.uid=user1.parentid";
		    $res_query_curr_parent = $conn->query($sql_query_curr_parent);
		    if($res_query_curr_parent->num_rows > 0){
		    	$row_query_curr_parent = $res_query_curr_parent->fetch_assoc();
		    	$curr_parent_name = $row_query_curr_parent["cpuname"];
		    	$curr_parent_id = $row_query_curr_parent["cpuid"];
		    }else{
		    	$curr_parent_name = "";
		    	$curr_parent_id = "";
		    }
		    // 如果已经打款获取打款时间
		    $finishstate = $row["finishstate"];
		    if($finishstate == "1" || $finishstate == "2"){
		    	$sql_get_dkdate = "select dkdate from dkcertificate where orderid='$orderid'";
		    	$res_get_dkdate = $conn->query($sql_get_dkdate);
		        $row_get_dkdate = $res_get_dkdate->fetch_assoc();
		        $dkdate = $row_get_dkdate["dkdate"];
		    }else{
		    	$dkdate = "";
		    }
		    // 返回数据
	    	$arr = array(
				'orderid'=>$row["orderid"],
				'paidanid'=>$row["paidanid"],
				'buyerid'=>$row["buyerid"],
				'sellerid'=>$row["sellerid"],
				'principal'=>$row["principal"],
				'orderdate'=>$row["orderdate"],
				'parentid'=>$row["parentid"],
				'bank'=>$row["bank"],
				'banknumber'=>$row["banknumber"],
				'ownername'=>$row["ownername"],
				'alipay'=>$row["alipay"],
				'uname'=>$row["uname"],
				'alipay'=>$row["alipay"],
				'dkdate'=>$dkdate,
				'finishstate'=>$finishstate,
				'parentname'=>$parent_name, //对方父代昵称
				'parentid'=>$parent_id, 
				'currparentname'=>$curr_parent_name, // 当前账号父代昵称
				'currparentid'=>$curr_parent_id,
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 16){ // 查询星级
		$uid = $array_data["uid"];
		$sql = "select stars from user where uid='$uid'"; 
		$result = $conn->query($sql);
		$jsonArray = array();
    	if ($result->num_rows > 0) {
		    $row = $result->fetch_assoc();
	    	$arr = array(
				'stars'=>$row["stars"],
			);
		}
		$conn->close();
		echo json_encode($arr);
	}else if($array_data["flags"] == 17){ // 查询激活码
		$uid = $array_data["uid"];
		$sql = "select activecode from user where uid='$uid'"; 
		$result = $conn->query($sql);
		$jsonArray = array();
    	if ($result->num_rows > 0) {
		    $row = $result->fetch_assoc();
	    	$arr = array(
				'activecode'=>$row["activecode"],
			);
		}
		$conn->close();
		echo json_encode($arr);
	}
	
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
	
	// 获取奖金记录
	function get_reward_record($conn, $uid, $sql, $degree, $jsonArray){
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
		    // 输出数据
		    while($row = $result->fetch_assoc()) {
		    	$arr = array(
					'reward'=>$row["reward"],
					'uname'=>$row["uname"],
					'txdate'=>$row["txdate"],
					'relation'=>$degree,
					'orderstate'=>$row["orderstate"],
				);
				$jsonArray[] = ($arr);
		    }
		}
		return $jsonArray;
	}
	
	// 获取用户的父级
	function get_user_parents($uid){
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
		return $parent_id;
	}
	
	// 修改奖励
	function alter_parent_bonus($conn, $uid, $reward){
		$sql_update_bonus = "update set user bonus=bonus+'$reward' where uid='$uid'";
		$finish_state = mysqli_query($conn, $sql_update_bonus);
		return $finish_state;
	}
?>