<?php
$allow = true; //允许添加监控

if(!$allow){
    echo '{"code":-1,"msg":"禁止添加监控"}';
    exit;
}
$sql_host = "localhost";
$sql_user = "jk";
$sql_pwd = "20030616a";
$sql_dbname = "jk";
$conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);

echo add($_GET["timeout"],$_GET["name"],$_GET["ip"],$_GET["port"],$_GET["type"],$_GET["data"],$_GET["head"],$_GET["time"],$conn);


function add($timeout,$name,$ip,$port,$type,$data,$head,$time,$conn){
    if($type == "GET") {
        $id = md5($ip.time().$time);
        $id = str_rand($id);
        $r = sql_add_get($id,$name,$timeout,$ip,$head,$time,$conn);
    }else if($type == "POST") {
        $id = md5($ip.time().$time);
        $id = str_rand($id);
        $r = sql_add_post($id,$name,$timeout,$ip,$data,$head,$time,$conn);
    }else if($type == "PORT") {
        $id = md5($ip.time().$time);
        $id = str_rand($id);
        $r = sql_add_port($id,$name,$timeout,$ip,$time,$conn);
    }else {
        return '{"code":-1,"msg":"未知的监控类型"}';
    }
    
    if($r){
        return '{"code":200,"msg":"添加成功"}';
    }else{
        return '{"code":-1,"msg":"添加失败"}';
    }
}

function sql_add_get($id,$name,$timeout,$ip,$head,$time,$conn){
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

function sql_add_post($id,$name,$timeout,$ip,$data,$head,$time,$conn){
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

function sql_add_port($id,$name,$timeout,$ip,$time,$conn){
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