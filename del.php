<?php
error_reporting(0);
// echo '{"code":-1,"msg":"出了一点bug，正在修复"}';
// exit;
include ("send.php");
if (! $_GET["type"]) {
    if (checkToken($_GET["token"], $_GET["id"])) {
        echo "Success";
    } else {
        echo "Fail";
    }
} else 
    if ($_GET["type"]) {
        $id = $_GET["id"];
        
        $sql_host = config_read_mysql_host();
        $sql_user = config_read_mysql_username();
        $sql_pwd = config_read_mysql_password();
        $sql_dbname = config_read_mysql_dbname();
        
        $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
        $sql = "SELECT * FROM `list` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $email = $row["email"];
            }
        }
        
        mysqli_close($conn);
        
        $token = createToken($id);
        $get[1] = $token;
        $get[2] = $id;
        $r = send_email($get, $email, 1);
        if ($r == 0) {
            echo '{"code":200,"msg":"邮件发送成功，请点击您邮箱中的链接删除此监控"}';
        } else {
            echo '{"code":-1,"msg":"未知错误"}';
        }
    } else {
        exit();
    }

function createToken ($id)
{
    $sql_host = config_read_mysql_host();
    $sql_user = config_read_mysql_username();
    $sql_pwd = config_read_mysql_password();
    $sql_dbname = config_read_mysql_dbname();
    
    $token = str_rand();
    $time = time() + 3600;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    $sql = "INSERT INTO `token` (id,token,time) VALUES ('$id','$token',$time)";
    $r = mysqli_query($conn, $sql);
    mysqli_close($conn);
    return $token;
}

function checkToken ($token, $id)
{
    $sql_host = config_read_mysql_host();
    $sql_user = config_read_mysql_username();
    $sql_pwd = config_read_mysql_password();
    $sql_dbname = config_read_mysql_dbname();
    
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    $sql = "SELECT * FROM `token` WHERE `token` = '$token'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row["id"] == $id) {
                if ($row["time"] > time()) {
                    $return = true;
                }
            }
        }
        mysqli_close($conn);
    } else {
        mysqli_close($conn);
        $return = false;
    }
    return $return;
}

function str_rand ()
{
    $char = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890";
    $length = 32;
    if (! is_int($length) || $length < 0) {
        return false;
    }
    
    $string = '';
    for ($i = $length; $i > 0; $i --) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }
    
    return $string;
}