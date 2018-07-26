<?php
require_once 'functions.php';
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
        
        include ("send.php");
        $token = createToken($id);
        $r = send_email_token($token, $id, $email);
        if ($r) {
            echo '{"code":200,"msg":"邮件发送成功，请点击您邮箱中的链接删除此监控"}';
        } else {
            echo '{"code":200,"msg":"未知错误"}';
        }
    } else {
        exit();
    }

