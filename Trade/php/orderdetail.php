<?php
	// ===================== 查看订单详情 ========================= //
	include("connSql.php");
	$string_data = $_POST['datas']; //获取插入的表的索引
	$array_data = json_decode($string_data, true);
	$uid = $array_data["uid"];
	$orderid = $array_data["orderid"];
	$ordertype = $array_data["ordertype"];
	// 查看是否可以自动确认收款
//	$sql_confirm_sk = "update orderform set finishstate='2' where (TIME(shdate)-TIME(dkdate))>30"
	//$uid = "17512552723";
	//$orderid = "JG697845";
	//$ordertype = "卖出";
	if($ordertype == "卖出"){
		$sql = "select orderid,buyerid,sellerid,orderform.principal,orderdate,finishstate,paidanid,uname,parentid,dkdate from orderform,user where orderid='$orderid' and buyerid=uid";
	}else if($ordertype == "买入"){
		$sql = "select orderid,buyerid,sellerid,orderform.principal,orderdate,finishstate,paidanid,uname,parentid,dkdate from orderform,user where orderid='$orderid' and sellerid=uid";
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
	    $dkdate = $row["dkdate"];
	    
	    // 收款人信息
		$sql_sk_info = "select bank,banknumber,ownername,alipay from orderform,user where orderid='$orderid' and sellerid=uid";
	    $result_sk_info = $conn->query($sql_sk_info);
		if ($result_sk_info->num_rows > 0) {
		    $row_sk_info = $result_sk_info->fetch_assoc();
		    $bank = $row_sk_info["bank"];
		    $banknumber = $row_sk_info["banknumber"];
		    $ownername = $row_sk_info["ownername"];
		    $alipay = $row_sk_info["alipay"];
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
			'bank'=>$bank,
			'banknumber'=>$banknumber,
			'ownername'=>$ownername,
			'alipay'=>$alipay,
			'uname'=>$row["uname"],
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
?>