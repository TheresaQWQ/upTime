<?
error_reporting(0);
$id = $_GET["id"];

$sql_host = "localhost";
$sql_user = "jk";
$sql_pwd = "20030616a";
$sql_dbname = "jk";

$conn = mysqli_connect($sql_host, $sql_user, $sql_pwd, $sql_dbname);
if (!$conn) {
    exit;
}

$sql = "SELECT * FROM `log` WHERE `id` = '$id' ORDER BY `time` DESC";
$result = mysqli_query($conn, $sql);
    
echo mysqli_error($conn);

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $yc[] = $row["netTime"];
        $t[] = date("H:i",$row["time"]);
    }
}
 
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,height=device-height">
    <title>曲线折线图</title>
    <style>::-webkit-scrollbar{display:none;}html,body{overflow:hidden;height:100%;margin:0;}</style>
</head>
<body>
<div id="mountNode"></div>
<script>/*Fixing iframe window.innerHeight 0 issue in Safari*/document.body.clientHeight;</script>
<script src="https://gw.alipayobjects.com/os/antv/pkg/_antv.g2-3.2.4/dist/g2.min.js"></script>
<script src="https://gw.alipayobjects.com/os/antv/pkg/_antv.data-set-0.8.9/dist/data-set.min.js"></script>
<script>
<?
$n = count($t);
if($n >= 30){
    $n = 30;
}
$i = 0;
while ($i <= $n)
{
    $i++;
    if(!$t[$i]){
        break;
    }
    $data .= "{
      时间: '".$t[$i]."',
      延迟: ".$yc[$i]."
    },";
}

?>
var data = [<?echo $data;?>];
var ds = new DataSet();
var dv = ds.createView().source(data);
dv.transform({
  type: 'fold',
  fields: ['延迟'], // 展开字段集
  key: 'city', // key字段
  value: 'temperature' // value字段
});
var chart = new G2.Chart({
  container: 'mountNode',
  forceFit: true,
  height: window.innerHeight
});
chart.source(dv, {
  时间: {
    range: [0, 1]
  }
});
chart.tooltip({
  crosshairs: {
    type: 'line'
  }
});
chart.line().position('时间*temperature').color('city').shape('smooth');
chart.point().position('时间*temperature').color('city').size(4).shape('circle').style({
  stroke: '#fff',
  lineWidth: 1
});
chart.render();
</script>
</body>
</html>