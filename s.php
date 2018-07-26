<?
include ("config.php");
require_once 'functions.php';
$id = $_GET["id"];
?>
<!DOCTYPE html>
<html>

<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Public Status Page</title>
<link rel="stylesheet"
	href="//cdnjs.loli.net/ajax/libs/mdui/0.4.1/css/mdui.min.css">
<script src="//cdnjs.loli.net/ajax/libs/mdui/0.4.1/js/mdui.min.js"></script>
</head>

<body>
	<style>
.page-top {
	background-color: rgb(12, 135, 235);
	margin: 0;
	padding: 0;
	width: 100%;
	height: 170px;
}

.page {
	width: 800px;
	background-color: #fff;
	margin: auto;
	margin-top: -100px;
}

.page .top {
	width: 800px;
	height: 10px;
	background-color: rgb(51, 145, 223);
}

.page .text {
	padding-left: 15px;
	padding-right: 15px;
	padding-bottom: 10px;
	padding-top: 5px;
}

.page .text div {
	margin-top: 10px;
}

.page .text .submit {
	margin-top: 15px;
	background-color: rgb(68, 171, 255);
}

.log {
	box-shadow: 0 0 5px #000;
	border-radius: 10px;
	padding-left: 10px;
	padding-right: 10px;
	padding-top: 5px;
	padding-bottom: 5px;
}

.log p {
	margin-left: 15px;
}

a {
	text-decoration: none;
	color: #555;
}
</style>
	<div class="page-top mdui-shadow-1"></div>
	<div class="page mdui-shadow-2">
		<div class="top"></div>
		<div class="text">
			<h1 class="mdui-text-center">
                    <?
                    $sql_host = config_read_mysql_host();
                    $sql_user = config_read_mysql_username();
                    $sql_pwd = config_read_mysql_password();
                    $sql_dbname = config_read_mysql_dbname();
                    
                    $id = $_GET["id"];
                    
                    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd,
                            $sql_dbname);
                    if (! $conn) {
                        exit();
                    }
                    
                    $sql = "SELECT * FROM `list` WHERE `id` = '$id'";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo $row["name"];
                    }
                    mysqli_close($conn);
                    ?>
                </h1>
			<h4>
				<a href="./index.php" class="mdui-valign"><i
					class="mdui-icon material-icons">&#xe5c4;</i>返回</a>
			</h4>
			<div class="mdui-tab mdui-tab-full-width" mdui-tab>
				<a href="#tab1" class="mdui-ripple">折线图</a> <a href="#tab2"
					class="mdui-ripple">表格</a>
			</div>
			<div id="tab1" class="mdui-p-a-2">
				<iframe
					src="./img.php?id=<?
    $sql_host = config_read_mysql_host();
    $sql_user = config_read_mysql_username();
    $sql_pwd = config_read_mysql_password();
    $sql_dbname = config_read_mysql_dbname();
    
    $conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
    if (! $conn) {
        exit();
    }
    
    if (! $_GET["s"]) {
        $s = 15;
    } else {
        $s = $_GET["s"];
    }
    
    $sql = "SELECT * FROM `log` WHERE `id` = '$id' ORDER BY `time` DESC LIMIT $s";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $i = 0;
        
        echo $id;
        ?>"
					width="100%" height="540px" frameborder="no" border="0"
					scrolling="no" allowtransparency="yes"> </iframe>
			</div>
			<div id="tab2" class="mdui-p-a-2">
				<div class="mdui-table-fluid">
					<table class="mdui-table mdui-table-hoverable">
						<thead>
							<tr>
								<th>#</th>
								<th>时间</th>
								<th>延迟</th>
								<th>状态</th>
								<th>HTTP响应码</th>
							</tr>
						</thead>
						<tbody>
                                <?
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row["id"];
            $sql = "SELECT * FROM `log` WHERE `id` = '$id' LIMIT 1";
            $res = mysqli_query($conn, $sql);
            $r = mysqli_fetch_assoc($res);
            if ($r["status"] == "true") {
                $online = '<td style="color: green">在线';
            } else {
                $online = '<td style="color: red">离线';
            }
            
            if ($row["netTime"] < 500 && $row["netTime"] != - 1) {
                $netTime = '<td style="color: green">' . $row["netTime"] . " ms";
            } else 
                if ($row["netTime"] < 1200 && $row["netTime"] != - 1) {
                    $netTime = '<td style="color: yellow">' . $row["netTime"] .
                            " ms";
                } else {
                    $netTime = '<td style="color: red">' . $row["netTime"] .
                            " ms";
                }
            
            $i ++;
            echo '<tr><td>';
            echo $i;
            echo '</td>';
            echo '<td>';
            echo date("Y-m-d H:i:s", $row["time"]);
            echo '</td>';
            echo $netTime;
            echo '</td>';
            echo $online;
            echo '</td>';
            echo '<td>';
            echo $row["httpCode"];
            echo '</td>';
            echo '</tr>';
        }
    }
    mysqli_close($conn);
    ?>
                            </tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</body>

</html>