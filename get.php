<?php
require_once 'functions.php';
include ("config.php");
include ("send.php");

$sql_host = config_read_mysql_host();
$sql_user = config_read_mysql_username();
$sql_pwd = config_read_mysql_password();
$sql_dbname = config_read_mysql_dbname();
$connA = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
$connB = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
$connC = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
$con = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);

get($connA);
post($connB);
port($connC);

$t = time() - 3600;
$sql = "DELETE FROM `log` WHERE `time` < '$t'";
mysqli_query($con, $sql);

mysqli_close($con);

