<?php
ob_start();
session_start();
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$goto = $config['admin_login_goto'];
if(isset($_SESSION['user'])) header("Location: $goto");
?>

<html>
<head>
<title>admin login</title>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<body OnLoad="document.adLogin.user.focus();">
<center>
<table height=100% width=100%>
<tr><td align=center valign=center>
<h3>admin login</h3>
<form name="adLogin" method=POST action="login.php">
username: <input type='text' name='user' size=10><p>
password: <input type='password' name='pass' size=10><p>

<?php
if($_POST['submit'] == 'Login') {
	$logname = ereg_replace("[^A-Za-z0-9]","",$_POST['user']);
	$logpass = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass']));
	$sql = my_quick_con($config) or die("MySQL problem"); 
	$table_a = $config['admin_table'];
	$ret = mysql_query("SELECT password FROM $table_a WHERE username='$logname';");
	$pass = mysql_fetch_assoc($ret); 
	$pass = $pass['password'];
	if(mysql_num_rows($ret) == 1 && $pass == $logpass) {
		mysql_free_result($ret);
		$_SESSION['user'] = $logname;
		$_SESSION['pass_hash'] = $logpass;
		header("Location:$goto");
	} else {
		echo "<font color=red>Invalid username/password</font><p>";
	}
	mysql_free_result($ret);
}
?>

<input type='submit' name='submit' value='Login'>
</form><p>
<a href='register.php'>Register Administrator</a>
</td>
</tr>
</table>
</body>
</html>

<?php
ob_end_flush();
?>
