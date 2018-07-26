<?php
require_once 'functions.php';
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
</style>
	<div class="page-top mdui-shadow-1"></div>
	<div class="page mdui-shadow-2">
		<div class="top"></div>
		<div class="text">
			<h1 class="mdui-text-center">Public Status Page</h1>
			<div class="mdui-tab mdui-tab-full-width" mdui-tab>
				<a href="#tab1" class="mdui-ripple">列表</a> <a href="#tab2"
					class="mdui-ripple">添加</a> <a href="#tab3" class="mdui-ripple">删除</a>
				<a href="#tab4" class="mdui-ripple">更新日志</a>
			</div>
			<div id="tab1" class="mdui-p-a-2">
				<div class="mdui-table-fluid">
					<table class="mdui-table mdui-table-hoverable">
						<thead>
							<tr>
								<th>#</th>
								<th>名称</th>
								<th>ID</th>
								<th>类型</th>
								<th>状态</th>
							</tr>
						</thead>
						<tbody class="mdui-typo">
                                <?
                                include ("config.php");
                                $sql_host = config_read_mysql_host();
                                $sql_user = config_read_mysql_username();
                                $sql_pwd = config_read_mysql_password();
                                $sql_dbname = config_read_mysql_dbname();
                                
                                $conn = mysqli_connect($sql_host, $sql_user,
                                        $sql_pwd, $sql_dbname);
                                if (! $conn) {
                                    exit();
                                }
                                
                                $sql = "SELECT * FROM `list`";
                                $result = mysqli_query($conn, $sql);
                                
                                if (mysqli_num_rows($result) > 0) {
                                    $i = 0;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $id = $row["id"];
                                        $sql = "SELECT * FROM `log` WHERE `id` = '$id' ORDER BY `time` DESC LIMIT 1";
                                        $res = mysqli_query($conn, $sql);
                                        $r = mysqli_fetch_assoc($res);
                                        if ($r["status"] == "true") {
                                            $online = '<td style="color: green">在线';
                                        } else {
                                            $online = '<td style="color: red">离线';
                                        }
                                        
                                        $name = '<a href="./s.php?id=' . $id .
                                                '">' . $row["name"] . '</a>';
                                        
                                        $i ++;
                                        echo '<tr><td>';
                                        echo $i;
                                        echo '</td>';
                                        echo '<td>';
                                        echo $name;
                                        echo '</td>';
                                        echo '<td>';
                                        echo $row["id"];
                                        echo '</td>';
                                        echo '<td>';
                                        echo $row["type"];
                                        echo '</td>';
                                        echo $online;
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
			<div id="tab2" class="mdui-p-a-2">
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="name" class="mdui-textfield-input" type="text"
						placeholder="监控名称" />
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="email" class="mdui-textfield-input" type="email"
						placeholder="邮箱（离线时邮件通知，收不到可以将imoe233@163.com加入白名单）" />
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="type" class="mdui-textfield-input" type="text"
						placeholder="类型（GET，POST或PORT，大写）" />
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="ip" class="mdui-textfield-input" type="text"
						placeholder="网址或IP" />
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="port" class="mdui-textfield-input" type="text"
						placeholder="端口（类型为PORT时生效）" />
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="timeout" class="mdui-textfield-input" type="text"
						placeholder="超时时间（单位秒）" />
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<textarea id="data" class="mdui-textfield-input" rows="4"
						placeholder="POST提交数据（Json格式）"></textarea>
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<textarea id="head" class="mdui-textfield-input" rows="4"
						placeholder="头信息（Json格式）"></textarea>
				</div>
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="time" class="mdui-textfield-input" type="text"
						placeholder="监控频率（多少秒一次，至少120秒）" />
				</div>
				<center>
					<button onclick="ajax();"
						class="mdui-btn mdui-btn-raised mdui-ripple">提交</button>
				</center>
			</div>
			<div id="tab3" class="mdui-p-a-2">
				<div class="mdui-textfield mdui-textfield-floating-label">
					<input id="id" class="mdui-textfield-input" type="text"
						placeholder="您的监控ID" />
				</div>
				<center>
					<button onclick="ajax_del();"
						class="mdui-btn mdui-btn-raised mdui-ripple">提交</button>
				</center>
			</div>
			<div id="tab4" class="mdui-p-a-2 mdui-typo">
				<h1>
					V 0.1 <br /> <small>2018-7-24</small>
				</h1>
				<p>这是 IMoe Public Status 的第一个版本</p>
				<p>
					GitHub：<a href="https://github.com/xiaoyaoge233/upTime">戳我</a>
				</p>
				<hr />
				<h1>
					V 0.2 <br /> <small>2018-7-25</small>
				</h1>
				<p>1.修复了0.1中的BUG，由原来的socket检测改为了fsockopen</p>
				<p>2.增加折线图</p>
				<hr />
				<h1>
					V 0.3 <br /> <small>2018-7-25</small>
				</h1>
				<p>1.增加邮箱提醒（imoe233@163.com）</p>
				<hr />
				<h1>
					V 0.4 <br /> <small>2018-7-26</small>
				</h1>
				<p>增加删除功能</p>
			</div>
		</div>
	</div>
	<script>
            function ajax_del(){
                var xmlhttp;
                
                var id = document.getElementById("id").value;
                if (window.XMLHttpRequest){
                    // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
                    xmlhttp=new XMLHttpRequest();
                }
                else
                {
                    // IE6, IE5 浏览器执行代码
                    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.onreadystatechange=function()
                {
                    if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    {
                        var json = xmlhttp.responseText
                        console.log(json);
                        obj = JSON.parse(json);
                        var text = obj.msg;
                        var code = obj.code;
                        mdui.alert(text);
                        if(code == 200){
                            document.getElementById("id").value == "";
                        }
                    }
                }
                //Ajax请求
                xmlhttp.open("GET","./del.php?type=send&id="+id,true);
                xmlhttp.send();
            }
        </script>
	<script>
            function ajax(){
                var xmlhttp;
                
                var timeout = document.getElementById("timeout").value;
                var ip = document.getElementById("ip").value;
                var port = document.getElementById("port").value;
                var type = document.getElementById("type").value;
                var data = document.getElementById("data").value;
                var head = document.getElementById("head").value;
                var time = document.getElementById("time").value;
                var name = document.getElementById("name").value;
                var email = document.getElementById("email").value;
                if (window.XMLHttpRequest){
                    // IE7+, Firefox, Chrome, Opera, Safari 浏览器执行代码
                    xmlhttp=new XMLHttpRequest();
                }
                else
                {
                    // IE6, IE5 浏览器执行代码
                    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.onreadystatechange=function()
                {
                    if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    {
                        var json = xmlhttp.responseText
                        console.log(json);
                        obj = JSON.parse(json);
                        var text = obj.msg;
                        var code = obj.code;
                        mdui.alert(text);
                        if(code == 200){
                            document.getElementById("timeout").value == "";
                            document.getElementById("ip").value == "";
                            document.getElementById("port").value == "";
                            document.getElementById("type").value == "";
                            document.getElementById("data").value == "";
                            document.getElementById("head").value == "";
                            document.getElementById("time").value == "";
                            document.getElementById("name").value == "";
                        }
                    }
                }
                //Ajax请求
                xmlhttp.open("GET","./add.php?timeout="+timeout+"&email="+email+"&name="+name+"&ip="+ip+"&port="+port+"&type="+type+"&data="+data+"&head="+head+"&time="+time,true);
                xmlhttp.send();
            }
        </script>
</body>

</html>