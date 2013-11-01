<?php
ob_start();
session_start();

require_once('security.php');
require_once('functions/functions.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.php'); 
$sql = my_quick_con($config) or die("SQL problem"); 
$table_u = $config['user_table'];
$id = $_SESSION['id'];
$display = sprintf("{%s}", $id);
echo($display);
?>