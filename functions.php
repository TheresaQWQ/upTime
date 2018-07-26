<?php

function add ($timeout, $email, $name, $ip, $port, $type, $data, $head, $time,
        $conn)
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
        $r = sql_add_get($id, $email, $name, $timeout, $ip, $head, $time, $conn);
    } else 
        if ($type == "POST") {
            $id = md5($ip . time() . $time);
            $id = str_rand($id);
            $r = sql_add_post($id, $email, $name, $timeout, $ip, $data, $head,
                    $time, $conn);
        } else 
            if ($type == "PORT") {
                $id = md5($ip . time() . $time);
                $id = str_rand($id);
                $r = sql_add_port($id, $email, $name, $timeout, $ip, $time,
                        $conn);
            } else {
                return '{"code":-1,"msg":"未知的监控类型"}';
            }
    
    if ($r) {
        return '{"code":200,"msg":"添加成功"}';
    } else {
        return '{"code":-1,"msg":"添加失败"}';
    }
}

function sql_add_get ($id, $email, $name, $timeout, $ip, $head, $time, $conn)
{
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

function sql_add_post ($id, $name, $timeout, $ip, $data, $head, $time, $conn)
{
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

function sql_add_port ($id, $name, $timeout, $ip, $time, $conn)
{
    if (! $conn) {
        exit();
    }
    
    $sql = "SELECT * FROM `list` WHERE `ip` = '$ip'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        mysqli_close($conn);
        return false;
    }
    
    $sql = "INSERT INTO `list` (id,name,timeout,ip,port,type,data,head,time,lastTime) VALUES ('$id','$name',$timeout,'$ip',0,'PORT','','',$time,0)";
    $r = mysqli_query($conn, $sql);
    
    mysqli_close($conn);
    
    if ($r) {
        return true;
    } else {
        return false;
    }
}

function config_read_mysql_host ()
{
    global $json;
    $j = json_decode($json);
    return $j->mysql->host;
}

function config_read_mysql_username ()
{
    global $json;
    $j = json_decode($json);
    return $j->mysql->username;
}

function config_read_mysql_password ()
{
    global $json;
    $j = json_decode($json);
    return $j->mysql->password;
}

function config_read_mysql_dbname ()
{
    global $json;
    $j = json_decode($json);
    return $j->mysql->dbname;
}

function config_read_email_server ()
{
    global $json;
    $j = json_decode($json);
    return $j->email->server;
}

function config_read_email_port ()
{
    global $json;
    $j = json_decode($json);
    return $j->email->port;
}

function config_read_email_useremail ()
{
    global $json;
    $j = json_decode($json);
    return $j->email->useremail;
}

function config_read_email_username ()
{
    global $json;
    $j = json_decode($json);
    return $j->email->username;
}

function config_read_email_password ()
{
    global $json;
    $j = json_decode($json);
    return $j->email->password;
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
        send_email($url, $id, $email);
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
        send_email($url, $id, $email);
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
        send_email($url, $id, $email);
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

function send_email ($url, $id, $email)
{
    $smtpserver = config_read_email_server();
    // SMTP端口号
    $smtpserverport = config_read_email_port();
    // SMTP发邮件的邮箱
    $smtpusermail = config_read_email_useremail();
    // 收信邮箱
    $smtpemailto = $email;
    // SMTP用户名
    $smtpuser = config_read_email_username();
    // SMTP用户密码
    $smtppass = config_read_email_password();
    // 主题
    $mailtitle = "Public Status | IMOE站点监控";
    // 构建内容
    $mailcontent = '
        <center>
        <h1>站点监控</h1>
        <hr>
        <p>您的站点' . $url .
            '(ID:' . $id . ')无法访问，查看详细信息请点击下面的链接<p>
        <a href="https://t.qgitf.cn/s.php?id=' . $id .
            '">https://t.qgitf.cn/s.php?id=' . $id . '</a>
        </center>
    ';
    // 邮件内容为HTML格式
    $mailtype = "HTML";
    // 实例化对象
    $smtp = new smtpSend($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
    // 关闭调试信息
    $smtp->debug = false;
    // 发送邮件
    $state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle,
            $mailcontent, $mailtype);
    // 检查发送状态
    if ($state == "") {
        return false; // 配置错误
    } else 
        if (strlen($state)) {
            return true;
        } else {
            return false; // 未知错误
        }
}

function send_email_token ($token, $id, $email)
{
    $smtpserver = config_read_email_server();
    // SMTP端口号
    $smtpserverport = config_read_email_port();
    // SMTP发邮件的邮箱
    $smtpusermail = config_read_email_useremail();
    // 收信邮箱
    $smtpemailto = $email;
    // SMTP用户名
    $smtpuser = config_read_email_username();
    // SMTP用户密码
    $smtppass = config_read_email_password();
    // 主题
    $mailtitle = "Public Status | IMOE站点监控";
    // 构建内容
    $mailcontent = '
        <center>
        <h1>站点监控</h1>
        <hr>
        <p>您正在删除您创建的监控，如果您没有此操作请无视此邮件</p>
        <p>点击此链接删除您的监控<a href="https://t.qgitf.cn/del.php?token=' .
            $token . '&id=' . $id . '">https://t.qgitf.cn/del.php?token=' .
            $token . '&id=' . $id . '</a></p>
        </center>
    ';
    // 邮件内容为HTML格式
    $mailtype = "HTML";
    // 实例化对象
    $smtp = new smtpSend($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
    // 关闭调试信息
    $smtp->debug = false;
    // 发送邮件
    $state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle,
            $mailcontent, $mailtype);
    // 检查发送状态
    if ($state == "") {
        return false; // 配置错误
    } else 
        if (strlen($state)) {
            return true;
        } else {
            return false; // 未知错误
        }
}
