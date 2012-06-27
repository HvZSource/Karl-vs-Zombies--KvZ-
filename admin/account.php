<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat'); 
$sql = my_quick_con($config) or die("SQL problem"); 
$table_u = $config['admin_table'];
$username = $_SESSION['user'];
?>

<html>
<head>
<title>Edit Player Account</title>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<?php
if($_POST['submit'] == 'Change Password') {
	$ret = mysql_query("SELECT password FROM $table_u WHERE username='$username';");
	$pass_ret = mysql_fetch_assoc($ret);
	$pass_ret = $pass_ret['password'];
	$pass_cur = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_original']));
	$pass_new = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_new']));
	$pass_con = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_confirm']));
	print "<table width=100% height=100%><tr><td align=center valign=center>";
	
	if($pass_ret == $pass_cur) {
		if(strlen($_POST['pass_new']) >= 4 && strlen($_POST['pass_new']) <= 20) {
			if($pass_new == $pass_con) {
				mysql_query("UPDATE $table_u SET password = '$pass_new' WHERE username='$username';");
				print "Password successfully changed.<br>";
			} else {
				print "The passwords you entered did not match."; 
			}
		} else {
			print "Your new password must be between 4 and 20 alphanumerics.<br>";
		}
	} else {
		print "The password you entered was incorrect.<br>"; 
	}
	print "<a href='account.php'>Back</a>";
	print "</td></tr></table>";
} else {
?>
<body>
<h3>my account</h3>
<form method="POST" action="account.php">
<table>
<tr><td colspan="2" align="center"><b>change password</b></td></tr>
<tr>
<td>original password:</td>
<td><input type="password" name="pass_original" size="20" maxlength="20"></td>
</tr>
<tr>
<td>new password:</td>
<td><input type="password" name="pass_new" size="20" maxlength="20"></td>
</tr>
<tr>
<td>confirm new password:</td>
<td><input type="password" name="pass_confirm" size="20" maxlength="20"></td>
</tr>
<tr><td colspan="2" align="center">
<input type="submit" name="submit" value="Change Password">
</td></tr>
</table>
</form>

</body>
<?php
}
?>

</html>

<?php
mysql_close($sql);
ob_end_flush();
?>
