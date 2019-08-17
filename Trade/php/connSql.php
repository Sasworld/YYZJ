<?php
	$servername = "47.110.83.139";
	$username = "root";
	$password = "6130e41bb94a";
	$dbname = "testyuyuan";
	// 创建连接
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	// 检测连接
	if (!$conn) {
	    die("连接失败: " . mysqli_connect_error());
	}
	$conn->query('set names utf8');
?>