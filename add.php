<?php
$allow = false; //允许添加监控

if(!$allow){
    echo '{"code":-1,"msg":"禁止添加监控"}';
    exit;
}

echo add($_GET["timeout"],$_GET["ip"],$_GET["port"],$_GET["type"],$_GET["data"],$_GET["head"],$_GET["time"]);

$sql_host = ""; //数据库地址
$sql_user = ""; //数据库用户名
$sql_pwd = ""; //数据库密码
$sql_dbname = ""; //数据库名

function add($timeout,$ip,$port,$type,$data,$head,$time){
    if($type == "GET") {
        $id = md5($ip.time().$time);
        $id = str_rand($id);
        $r = sql_add_get($id,$timeout,$ip,$head);
    }else if($type == "POST") {
        $id = md5($ip.time().$time);
        $id = str_rand($id);
        $r = sql_add_post($id,$timeout,$ip,$data,$head);
    }else if($type == "PORT") {
        $id = md5($ip.time().$time);
        $id = str_rand($id);
        $r = sql_add_port($id,$timeout,$ip);
    }else {
        return '{"code":-1,"msg":"未知的监控类型"}';
    }
    
    if($r){
        return '{"code":200,"msg":"添加成功"}';
    }else{
        return '{"code":-1,"msg":"添加失败"}';
    }
}

function sql_add_get($id,$name,$timeout,$ip,$head){
    global $sql_host,$sql_user,$sql_pwd,$sql_dbname;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (!$conn) {
        exit;
    }
    
    $sql = "INSERT INTO `list` (id,name,timeout,ip,port,type,data,head,time,lastTime) VALUES ('$id','$name',$timeout,'$ip',0,'GET','','$head',$time,0)";
    $r = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
    
    if($r) {
        return true;
    }else {
        return false;
    }
}

function sql_add_post($id,$name,$timeout,$ip,$data,$head){
    global $sql_host,$sql_user,$sql_pwd,$sql_dbname;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (!$conn) {
        exit;
    }
    
    $sql = "INSERT INTO `list` (id,name,timeout,ip,port,type,data,head,time,lastTime) VALUES ('$id','$name',$timeout,'$ip',0,'POST','$data','$head',$time,0)";
    $r = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
    
    if($r) {
        return true;
    }else {
        return false;
    }
}

function sql_add_port($id,$name,$timeout,$ip){
    global $sql_host,$sql_user,$sql_pwd,$sql_dbname;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (!$conn) {
        exit;
    }
    
    $sql = "INSERT INTO `list` (id,name,timeout,ip,port,type,data,head,time,lastTime) VALUES ('$id','$name',$timeout,'$ip',0,'PORT','','',$time,0)";
    $r = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
    
    if($r) {
        return true;
    }else {
        return false;
    }
}

function str_rand($char) {
    $length = 8;
    if(!is_int($length) || $length < 0) {
        return false;
    }

    $string = '';
    for($i = $length; $i > 0; $i--) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }

    return $string;
}