<?php
include ("config.php");
include ("smtp.class.php");

function send_email ($r, $email, $type)
{
    // ******************** 配置信息 ********************************
    $smtpserver = config_read_email_server(); // SMTP服务器
    $smtpserverport = config_read_email_port();
    $smtpusermail = config_read_email_useremail();
    $smtpemailto = $email;
    $smtpuser = config_read_email_username();
    $smtppass = config_read_email_password();
    $mailtitle = "Public Status | IMOE站点监控";
    $mailtype = "HTML"; // 邮件格式（HTML/TXT）,TXT为文本邮件
    
    if (! $type) {
        $mailcontent = '
            <center>
            <h1>站点监控</h1>
            <hr>
            <p>您的站点' . $r[1] . '(ID:' . $r[2] . ')无法访问，查看详细信息请点击下面的链接<p>
            <a href="https://t.qgitf.cn/s.php?id=' . $r[2] .
                '">https://t.qgitf.cn/s.php?id=' . $r[2] . '</a>
            </center>
        ';
    } else 
        if ($type) {
            $mailcontent = '
            <center>
            <h1>站点监控</h1>
            <hr>
            <p>您正在删除您创建的监控，如果您没有此操作请无视此邮件</p>
            <p>点击此链接删除您的监控<a href="https://t.qgitf.cn/del.php?token=' .
                    $r[1] . '&id=' . $r[2] .
                    '">https://t.qgitf.cn/del.php?token=' . $r[1] . '&id=' .
                    $r[2] . '</a></p>
            </center>
        ';
        }
    
    // ************************ 配置信息 ****************************
    // 实例化对象
    $smtp = new smtpSend($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
    // 关闭调试信息
    $smtp->debug = false;
    // 发送邮件
    $state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle,
            $mailcontent, $mailtype);
    // 检查发送状态
    // echo $state;
    if ($state == "") {
        return 0; // 配置错误
    } else 
        if (strlen($state)) {
            return 1;
        } else {
            return 0; // 未知错误
        }
}