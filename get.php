<?php
error_reporting(0);

$sql_host = "localhost";
$sql_user = "jk";
$sql_pwd = "20030616a";
$sql_dbname = "jk";
$connA = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
$connB = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
$connC = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);

get($connA);
post($connB);
port($connC);
function get($conn){
    if (!$conn) {
        exit;
    }
     
    $sql = "SELECT * FROM `list` WHERE `type` = 'GET'";
    $result = mysqli_query($conn, $sql);
    
    echo mysqli_error($conn);
    
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $lastTime = $row["lastTime"];
            $time = time();
            if($time >= $lastTime + $row["time"] || $lastTime == 0){
                curl_get($row["ip"],$row["id"],$row["head"],$row["timeout"]);
            }
        }
    }
     
    mysqli_close($conn);
}
//POST监控
function post($conn) {
    if (!$conn) {
        exit;
    }

    $sql = "SELECT * FROM `list` WHERE `type` = 'POST'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lastTime = $row["lastTime"];
            $time = time();
            if ($time >= $lastTime + $row["time"]) {
                curl_post($row["ip"],$row["id"],$row["head"],$row["data"],$row["timeout"]);
            }
        }
    }

    mysqli_close($conn);
}
//PORT监控
function port($conn){
    if (!$conn) {
        exit;
    }
     
    $sql = "SELECT * FROM `list` WHERE `type` = 'PORT'";
    $result = mysqli_query($conn, $sql);
     
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $lastTime = $row["lastTime"];
            $time = time();
            if($time >= $lastTime + $row["time"]){
                port_get($row["id"],$row["ip"],$row["port"]);
            }
        }
    }
     
    mysqli_close($conn);
}

//计时器 start
function runtimeA($mode = 0) {
    static $t;
    if (!$mode) {
        $t = microtime();
        return;
    }
    $t1 = microtime();
    list($m0,$s0) = split(" ",$t);
    list($m1,$s1) = split(" ",$t1);
    return sprintf("%.3f",($s1+$m1-$s0-$m0)*1000);
}

function runtimeB($mode = 0) {
    static $t;
    if (!$mode) {
        $t = microtime();
        return;
    }
    $t1 = microtime();
    list($m0,$s0) = split(" ",$t);
    list($m1,$s1) = split(" ",$t1);
    return sprintf("%.3f",($s1+$m1-$s0-$m0)*1000);
}

function runtimeC($mode = 0) {
    static $t;
    if (!$mode) {
        $t = microtime();
        return;
    }
    $t1 = microtime();
    list($m0,$s0) = split(" ",$t);
    list($m1,$s1) = split(" ",$t1);
    return sprintf("%.3f",($s1+$m1-$s0-$m0)*1000);
}
//计时器 end

function curl_get($url,$id,$head,$timeout) {
    runtimeA();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch,CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($head){
        $head = json_decode($head);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    }
    curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    $time = runtimeA(1);
    if(200 <= $httpCode && $httpCode < 300 || $httpCode == 301 || $httpCode == 302){
        sql_add($id,$httpCode,"true",$time);
    }else{
        sql_add($id,$httpCode,"false",-1);
    }
}

function curl_post($url,$id,$head,$data,$timeout) {
    runtimeB();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch,CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($head){
        $head = json_decode($head);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    }
    curl_setopt($ch, CURLOPT_POST, 1);
    if($data){
        $data = json_decode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    $time = runtimeB(1);
    if(200 <= $httpCode && $httpCode < 300 || $httpCode == 301 || $httpCode == 302){
        sql_add($id,$httpCode,"true",$time);
    }else{
        sql_add($id,$httpCode,"false",$time);
    }
}

function port_get($id,$ip,$port){
    runtimeC();
    $health = new Health();
    $health->check($ip, $port);
    $r = $health->status();
    $time = runtimeC(1);
    if($r){
        sql_add($id,-1,"true",$time);
    }else{
        sql_add($id,-1,"false",-1);
    }
}

function sql_add($id,$httpCode,$status,$netTime){
    global $sql_host,$sql_user,$sql_pwd,$sql_dbname;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (!$conn) {
        exit;
    }
    
    $time = time();
    
    $sql = "INSERT INTO `log` (id, httpCode, status, netTime, time) VALUES ('$id', '$httpCode', '$status', $netTime, $time)";
    echo mysqli_query($conn, $sql);

    $sql = "UPDATE list SET lastTime=$time WHERE id='$id'";
    echo mysqli_query($conn, $sql);
    
    mysqli_close($conn);
}

function port_add($id,$httpCode,$status,$netTime){
    global $sql_host,$sql_user,$sql_pwd,$sql_dbname;
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (!$conn) {
        exit;
    }
    
    $time = time();
    
    $sql = "INSERT INTO log (id, httpCode, status, netTime, time) VALUES ('$id', '$httpCode', '$status', $netTime, $time)";
    $result = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
}

class Health {
  public static $status;
  public function __construct()
  {
  }
  public function check($ip, $port){
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_nonblock($sock);
    socket_connect($sock,$ip, $port);
    socket_set_block($sock);
    self::$status = socket_select($r = array($sock), $w = array($sock), $f = array($sock), 5);
    return(self::$status); 
  }
  public function checklist($lst){
  }
  public function status(){
    switch(self::$status)
    {
      case 2:
        return false;
        break;
      case 1:
        return true;
        break;
      case 0:
        return false;
        break;
    }  
  }
}