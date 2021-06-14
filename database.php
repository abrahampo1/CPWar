<?
include_once("config.php");
$servername = $config[0]["bd"];
$database = $config[0]["bd_name"];
$username = $config[0]["bd_user"];
$password = $config[0]["bd_pass"];
// Create connection
$link = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$link) {
      die("Connection failed: " . mysqli_connect_error());
}