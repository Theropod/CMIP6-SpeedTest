<?php
// error_reporting(0);

function format($d){
    if($d<10) return number_format($d,2,".","");
    if($d<100) return number_format($d,1,".","");
    return number_format($d,0,".","");
}


$DL_TEXT="Download";
$UL_TEXT="Upload";
$PING_TEXT="Ping";
$JIT_TEXT="Jitter";
$MBPS_TEXT="Mbps";
$MS_TEXT="ms";

$id=$_GET["id"];
include_once('telemetry_settings.php');
require 'idObfuscation.php';
if($enable_id_obfuscation) $id=deobfuscateId($id);
$conn=null; $q=null;
$ispinfo=null; $dl=null; $ul=null; $ping=null; $jit=null;
if($db_type=="mysql"){
	$conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename);
	$q = $conn->prepare("select ispinfo,dl,ul,ping,jitter from speedtest_users where id=?");
	$q->bind_param("i",$id);
	$q->execute();
	$q->bind_result($ispinfo,$dl,$ul,$ping,$jit);
	$q->fetch();
}else if($db_type=="sqlite"){
	$conn = new PDO("sqlite:$Sqlite_db_file") or die();
	$q=$conn->prepare("select ispinfo,dl,ul,ping,jitter from speedtest_users where id=?") or die();
	$q->execute(array($id)) or die();
	$row=$q->fetch() or die();
	$ispinfo=$row["ispinfo"];
	$dl=$row["dl"];
	$ul=$row["ul"];
	$ping=$row["ping"];
	$jit=$row["jitter"];
	$conn=null;
}else if($db_type=="postgresql"){
    $conn_host = "host=$PostgreSql_hostname";
    $conn_db = "dbname=$PostgreSql_databasename";
    $conn_user = "user=$PostgreSql_username";
    $conn_password = "password=$PostgreSql_password";
    $conn = new PDO("pgsql:$conn_host;$conn_db;$conn_user;$conn_password") or die();
	$q=$conn->prepare("select ispinfo,dl,ul,ping,jitter from speedtest_users where id=?") or die();
	$q->execute(array($id)) or die();
	$row=$q->fetch() or die();
	$ispinfo=$row["ispinfo"];
	$dl=$row["dl"];
	$ul=$row["ul"];
	$ping=$row["ping"];
	$jit=$row["jitter"];
	$conn=null;
}else die();

$dl=format($dl);
$ul=format($ul);
$ping=format($ping);
$jit=format($jit);

$ispinfo=json_decode($ispinfo,true)["rawIspInfo"];
$ip=$ispinfo["ip"];

$testinfo=array(
	"OrigIP" => $ip,
	"Ping" => $ping,
	"Jitter" => $jit,
	"DL" => $dl,
);
$testinfo = json_encode($testinfo,JSON_UNESCAPED_SLASHES);
echo $testinfo;
?>
