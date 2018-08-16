<?php
include ("config.php");
ignore_user_abort();
set_time_limit(0);
$interval = 60;

$config = json_decode($json);

do {
    file_get_contents($config["path"] . "/get.php");
    sleep($interval);
} while (1);
