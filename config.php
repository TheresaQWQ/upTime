<?php
$json = '
{
    "mysql":{
        "host":"",
        "username":"",
        "password":"",
        "dbname":""
    },
    "email":{
        "server":"",
        "port":25,
        "useremail":"",
        "username":"",
        "password":""
    }
}
';

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