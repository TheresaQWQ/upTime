<?php
include ("config.php");
$allow = true; // 允许添加监控

if (! $allow) {
    echo '{"code":-1,"msg":"禁止添加监控"}';
    exit();
}

echo add($_GET["timeout"], $_GET["email"], $_GET["name"], $_GET["ip"],
        $_GET["port"], $_GET["type"], $_GET["data"], $_GET["head"], $_GET["time"]);

function add ($timeout, $email, $name, $ip, $port, $type, $data, $head, $time)
{
    if (! $type || ! $timeout || ! $name || ! $ip || ! $time || ! $email) {
        return '{"code":-1,"msg":"请填写完整的信息"}';
        exit();
    }
    
    if (stripos("i" . $name, "ddos")) {
        return '{"code":-1,"msg":"名称中含有违禁词"}';
        exit();
    }
    
    if (stripos("i" . $ip, "jisuyingyong.com")) {
        return '{"code":-1,"msg":"黑名单网址，禁止添加"}';
        exit();
    }
    
    if ($time < 120) {
        return '{"code":-1,"msg":"监控频率过快"}';
        exit();
    }
    
    if ($timeout > 30) {
        return '{"code":-1,"msg":"超时时间最大30秒"}';
        exit();
    }
    
    if ($type == "GET") {
        $id = md5($ip . time() . $time);
        $id = str_rand($id);
        $r = sql_add_get($id, $email, $name, $timeout, $ip, $head, $time);
    } else 
        if ($type == "POST") {
            $id = md5($ip . time() . $time);
            $id = str_rand($id);
            $r = sql_add_post($id, $email, $name, $timeout, $ip, $data, $head,
                    $time);
        } else 
            if ($type == "PORT") {
                $id = md5($ip . time() . $time);
                $id = str_rand($id);
                $r = sql_add_port($id, $email, $name, $timeout, $ip, $time,
                        $port);
            } else {
                return '{"code":-1,"msg":"未知的监控类型"}';
            }
    
    if ($r) {
        return '{"code":200,"msg":"添加成功"}';
    } else {
        return '{"code":-1,"msg":"添加失败"}';
    }
}

function sql_add_get ($id, $email, $name, $timeout, $ip, $head, $time)
{
    $sql_host = config_read_mysql_host();
    $sql_user = config_read_mysql_username();
    $sql_pwd = config_read_mysql_password();
    $sql_dbname = config_read_mysql_dbname();
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    
    if (! $conn) {
        exit();
    }
    
    $sql = "SELECT * FROM `list` WHERE `ip` = '$ip'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        mysqli_close($conn);
        return false;
    }
    
    $sql = "INSERT INTO `list` (id,name,email,timeout,ip,port,type,data,head,time,lastTime) VALUES ('$id','$name','$email',$timeout,'$ip',0,'GET','','$head',$time,0)";
    $r = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
    
    if ($r) {
        return true;
    } else {
        return false;
    }
}

function sql_add_post ($id, $name, $timeout, $ip, $data, $head, $time)
{
    $sql_host = config_read_mysql_host();
    $sql_user = config_read_mysql_username();
    $sql_pwd = config_read_mysql_password();
    $sql_dbname = config_read_mysql_dbname();
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    
    if (! $conn) {
        exit();
    }
    
    $sql = "SELECT * FROM `list` WHERE `ip` = '$ip'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        mysqli_close($conn);
        return false;
    }
    
    $sql = "INSERT INTO `list` (id,name,timeout,ip,port,type,data,head,time,lastTime) VALUES ('$id','$name',$timeout,'$ip',0,'POST','$data','$head',$time,0)";
    $r = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
    
    if ($r) {
        return true;
    } else {
        return false;
    }
}

function sql_add_port ($id, $name, $timeout, $ip, $time, $port)
{
    $sql_host = config_read_mysql_host();
    $sql_user = config_read_mysql_username();
    $sql_pwd = config_read_mysql_password();
    $sql_dbname = config_read_mysql_dbname();
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    
    if (! $conn) {
        exit();
    }
    
    $sql = "SELECT * FROM `list` WHERE `ip` = '$ip'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        mysqli_close($conn);
        return false;
    }
    
    $sql = "INSERT INTO `list` (id,name,timeout,ip,port,type,data,head,time,lastTime) VALUES ('$id','$name',$timeout,'$ip','$port','PORT','','',$time,0)";
    $r = mysqli_query($conn, $sql);
    
    echo mysqli_error($conn);
    
    mysqli_close($conn);
    
    if ($r) {
        return true;
    } else {
        return false;
    }
}

function str_rand ($char)
{
    $length = 8;
    if (! is_int($length) || $length < 0) {
        return false;
    }
    
    $string = '';
    for ($i = $length; $i > 0; $i --) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }
    
    return $string;
}