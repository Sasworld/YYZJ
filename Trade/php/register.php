<?php
include("connSql.php");
$string_data = $_POST['datas']; //获取插入的表的索引
$array_data = json_decode($string_data, true);
$uid = $array_data["uid"];
$name = $array_data["uname"];
$upw = $array_data["pw"];
$parentname = $array_data["parentname"];
$bank = $array_data["bank"];
$banknumber = $array_data["banknumber"];
$subbank = $array_data["subbank"];
$owner = $array_data["owner"];
$alipay = $array_data["alipay"];

//$uid = "15756192345";
//$name = "4";
//$upw = "12345";
//$parentname = "sas";
//$bank = "233";
//$banknumber = "6222620210019587137";
//$subbank = "4";
//$owner = "4";
//$alipay = "4";
// 获取父级id
if($parentname == ""){
	// 判断是否含有重复信息
	$sql_check_pd = "select * from user where uid='$uid' or uname='$name' or banknumber='$banknumber' or (alipay='$alipay' and alipay<>'')";
	$res_check_pd = $conn->query($sql_check_pd);
	if($res_check_pd->num_rows>0){
		$arr = array(
			'result'=>"该套资料含重复注册内容",
		);
	}else{
		$sql = "insert into user(uid, uname, upw, parentid, parentname, parenttree, bank, subbank, banknumber, alipay, ownername) values('$uid', '$name', HEX(aes_encrypt('$upw', 'sasworld')), '', '', '', '$bank', '$subbank', '$banknumber', '$alipay', '$owner')";
		if ($conn->query($sql) == TRUE) {
		    $arr = array(
				'result'=>"注册成功,账号激活后可登陆系统",
			);
		}else{
			$arr = array(
				'result'=>"注册失败,请检查信息是否有误",
			);
		}
	}
}else{
	$sql_get_paid = "select uid from user where uname='$parentname' and state='1'";
	$res_get_paid = $conn->query($sql_get_paid);
	if($res_get_paid->num_rows > 0){
		$row_get_paid = $res_get_paid->fetch_assoc();
		$parentid = $row_get_paid['uid'];
		// 判断是否含有重复信息
		$sql_check_pd = "select * from user where uid='$uid' or uname='$name' or banknumber='$banknumber' or (alipay='$alipay' and alipay<>'')";
		$res_check_pd = $conn->query($sql_check_pd);
		if($res_check_pd->num_rows>0){
			$arr = array(
				'result'=>"该套资料含重复注册内容",
			);
		}else{
			$parents_id = array("", "",""); // 父级id，默认为空, 不包括直接父级，直接父级单独存储
			$sql_get_parent_tree = "select parenttree from user where uid='$parentid'"; // 获取父级树
			$res_get_parent_tree = $conn->query($sql_get_parent_tree);
			if($res_get_parent_tree->num_rows > 0){
				$row_get_parent_tree = $res_get_parent_tree->fetch_assoc();
				$parent_tree = $row_get_parent_tree['parenttree'];
			}else{
				$parent_tree = "";
			}
	//		$ids = explode('/',$parent_tree);
	//		$parent_amount = sizeof($ids); // 父级层数
	//		for($i = 0; $i < 3; $i++){ // 最多维持三代奖励
	//			$parents_id[$i] = $ids[$i];
	//		}
	//		$dirparentid =  $parents_id[0];
	//		$second_pid = $parents_id[1];
	//		$third_pid = $parents_id[2];
			$curr_parent_tree = $parentid.'/'.$parent_tree;
			$sql = "insert into user(uid, uname, upw, parentid, parentname, parenttree, bank, subbank, banknumber, alipay, ownername) values('$uid', '$name', HEX(aes_encrypt('$upw', 'sasworld')), '$parentid', '$parentname', '$curr_parent_tree', '$bank', '$subbank', '$banknumber', '$alipay', '$owner')";
			
			if ($conn->query($sql) == TRUE) {
			    $arr = array(
					'result'=>"注册成功,账号激活后可登陆系统",
				);
			}else{
				$arr = array(
					'result'=>"注册失败,请检查信息是否有误",
				);
			}
		}
	}else{
		$arr = array(
			'result'=>"父级用户不存在",
		);
	}
}

$conn->close();
echo json_encode($arr);
?>