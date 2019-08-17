<?php
date_default_timezone_set(  'Asia/Shanghai'  );
$targetFolder = '/upload/';
if (!empty($_FILES)) {
    $file_name = iconv("UTF-8","gb2312", $_FILES['file']['name']); //文件名称
    $filenames= explode(".",$file_name); // 类似split函数，根据“.”划分字符串
    $tempFile = $_FILES['file']['tmp_name'];
    $rand = rand(1000, 9999);
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/' .ltrim($targetFolder,'/'); //图片存放目录
    $targetFile = rtrim($targetPath,'/') . '/' .time().$rand.".".$filenames[count($filenames)-1]; //图片完整路徑

    // Validate the file type
    $fileTypes = array('jpg', 'jpeg', 'png'); // File extensions
    $fileParts = pathinfo($_FILES['file']['name']);

    if (in_array($fileParts['extension'],$fileTypes)) {
        move_uploaded_file($tempFile,iconv("UTF-8","gb2312", $targetFile));
        exit(json_encode(array("url"=>$targetFile,'name'=>$file_name)));
    } else {
        echo 'Invalid file type.';
    }
}