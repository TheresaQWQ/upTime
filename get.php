<?php
// error_reporting(0);
// include("config.php");
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

function get ($conn)
{
    if (! $conn) {
        exit();
    }
    
    $sql = "SELECT * FROM `list` WHERE `type` = 'GET'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lastTime = $row["lastTime"];
            $time = time();
            if ($time >= $lastTime + $row["time"] || $lastTime == 0) {
                curl_get($row["ip"], $row["id"], $row["head"], $row["timeout"],
                        $row["email"]);
            }
        }
    }
    
    mysqli_close($conn);
}

// POST监控
function post ($conn)
{
    if (! $conn) {
        exit();
    }
    
    $sql = "SELECT * FROM `list` WHERE `type` = 'POST'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lastTime = $row["lastTime"];
            $time = time();
            if ($time >= $lastTime + $row["time"]) {
                curl_post($row["ip"], $row["id"], $row["head"], $row["data"],
                        $row["timeout"], $row["email"]);
            }
        }
    }
    
    mysqli_close($conn);
}

// PORT监控
function port ($conn)
{
    if (! $conn) {
        exit();
    }
    
    $sql = "SELECT * FROM `list` WHERE `type` = 'PORT'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lastTime = $row["lastTime"];
            $time = time();
            if ($time >= $lastTime + $row["time"]) {
                port_get($row["id"], $row["ip"], $row["port"], $row["timeout"],
                        $row["email"]);
            }
        }
    }
    
    mysqli_close($conn);
}

function curl_get ($url, $id, $head, $timeout, $email)
{
    $t = new runTime();
    $t->rTime();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($head) {
        $head = json_decode($head);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    }
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $time = $t->rTime(1);
    if (200 <= $httpCode && $httpCode < 300 || $httpCode == 301 ||
            $httpCode == 302) {
        sql_add($id, $httpCode, "true", $time);
    } else {
        $get[1] = $url;
        $get[2] = $id;
        send_email($get, $email, 0);
        sql_add($id, $httpCode, "false", - 1);
    }
}

function curl_post ($url, $id, $head, $data, $timeout, $email)
{
    $t = new runTime();
    $t->rTime();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($head) {
        $head = json_decode($head);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    }
    curl_setopt($ch, CURLOPT_POST, 1);
    if ($data) {
        $data = json_decode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $time = $t->rTime(1);
    if (200 <= $httpCode && $httpCode < 300 || $httpCode == 301 ||
            $httpCode == 302) {
        sql_add($id, $httpCode, "true", $time);
    } else {
        $get[1] = $url;
        $get[2] = $id;
        send_email($get, $email, 0);
        sql_add($id, $httpCode, "false", $time);
    }
}

function port_get ($id, $ip, $port, $timeout, $email)
{
    $t = new runTime();
    $t->rTime();
    
    $r = fsockopen($ip, $port, $errno, $errstr, $timeout);
    
    $time = $t->rTime(1);
    if ($r) {
        sql_add($id, - 1, "true", $time);
    } else {
        $get[1] = $url;
        $get[2] = $id;
        send_email($get, $email, 0);
        sql_add($id, - 1, "false", - 1);
    }
    
    fclose($r);
}

function sql_add ($id, $httpCode, $status, $netTime)
{
    global $sql_host, $sql_user, $sql_pwd, $sql_dbname;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (! $conn) {
        exit();
    }
    
    $time = time();
    
    $sql = "INSERT INTO `log` (id, httpCode, status, netTime, time) VALUES ('$id', '$httpCode', '$status', $netTime, $time)";
    mysqli_query($conn, $sql);
    
    $sql = "UPDATE list SET lastTime=$time WHERE id='$id'";
    mysqli_query($conn, $sql);
    
    mysqli_close($conn);
}

function port_add ($id, $httpCode, $status, $netTime)
{
    global $sql_host, $sql_user, $sql_pwd, $sql_dbname;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (! $conn) {
        exit();
    }
    
    $time = time();
    
    $sql = "INSERT INTO log (id, httpCode, status, netTime, time) VALUES ('$id', '$httpCode', '$status', $netTime, $time)";
    $result = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
}

class runTime
{

    public function rTime ($mode = 0)
    {
        static $t;
        if (! $mode) {
            $t = microtime();
            return;
        }
        $t1 = microtime();
        list ($m0, $s0) = split(" ", $t);
        list ($m1, $s1) = split(" ", $t1);
        return sprintf("%.3f", ($s1 + $m1 - $s0 - $m0) * 1000);
    }
}