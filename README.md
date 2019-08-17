# YYZJ
本项目为基于H5的虚拟货币交易平台,另外该平台的管理项目为YYManager

项目搭建：
  apache服务器+MySQL数据库
  本地搭建和配置好服务器和数据库后将yuyuan.sql导入数据库,在php\conSql.php中设置数据库信息：
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
