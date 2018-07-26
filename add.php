<?php
include ("config.php");
require_once 'functions.php';
$allow = true; // 允许添加监控

if (! $allow) {
    echo '{"code":-1,"msg":"禁止添加监控"}';
    exit();
}
$sql_host = config_read_mysql_host();
$sql_user = config_read_mysql_username();
$sql_pwd = config_read_mysql_password();
$sql_dbname = config_read_mysql_dbname();
$conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);

echo add($_GET["timeout"], $_GET["email"], $_GET["name"], $_GET["ip"],
        $_GET["port"], $_GET["type"], $_GET["data"], $_GET["head"], $_GET["time"],
        $conn);
