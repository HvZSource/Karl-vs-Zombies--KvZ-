<?php
ob_start();
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
?>

<html>
<head>
<title>Admin Registration</title>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<?php
if($_POST['submit'] == 'Register') {
$err = 0; 
$username = ereg_replace("[^A-Za-z0-9]","",$_POST['username']); 
$password1 = ereg_replace("[^A-Za-z0-9]","",$_POST['password1']);
$password2 = ereg_replace("[^A-Za-z0-9]","",$_POST['password2']);
$email_address = ereg_replace("[^A-Za-z0-9@.]","",$_POST['email_address']);
$reg_confirm = ereg_replace("[^A-Za-z0-9]","",$_POST['confirm']);

print "<body><table height=100% width=100%><tr><td align=center valign=center>";
if(strlen($username) < 4) {
$err = 1; 
print "Username is too short.<br>";
}
if(strlen($username) > 20) {
$err = 1; 
print "Username is too long.<br>"; 
}
if(strlen($password1) < 4) {
$err = 1; 
print "Password is too short.<br>"; 
}
if(strlen($password1) > 20) {
$err = 1; 
print "Password is too long.<br>";
}
if($password1 != $password2) {
$err = 1; 
print "Passwords do not match.<br>";
}
if($reg_confirm != $config['reg_admin']) {
$err = 1; 
print "Incorrect confirmation password.<br>";
}

if($err == 1) {
print "<a href='register.php'>Try again</a>";
} else {
$sql = my_quick_con($config) or die("MySQL Problem");
$table_a = $config['admin_table'];
$password = md5($password1);
$ret = mysql_query("SELECT * FROM $table_a WHERE username='$username';");
if(mysql_num_rows($ret) > 0) {
	print "An admin with this username already exists.";
} else {
	$ret = mysql_query("INSERT INTO $table_a (username, password, email) VALUES ('$username','$password','$email_address');");
	if(!$ret) print "ERROR." . mysql_error($sql) . "<br>";
	print "Registered.<br>";
	print "<a href=login.php>Back to admin login</a>";
}
if(is_resource($ret)) {
     mysql_free_result($ret);
}

mysql_close($sql);
}
print "</td></tr></table></body>";

} else {
?>
<body>
<table height=100% width=100%><tr><td align=center valign=center>
<h3>Admin Registration</h3>
<form method=POST action=<?php echo $PHP_SELF; ?>>
<table>
<tr>
<td>Username:</td>
<td><input type='text' name='username' size=20 maxlength=20></td>
</tr>
<tr><td colspan=2 align=center>(between 4 and 20 alphanumerics)</td></tr>
<tr>
<td>Password:</td>
<td><input type='password' name='password1' size=20 maxlength=20></td>
</tr>
<tr>
<td>Confirm Password:</td>
<td><input type='password' name='password2' size=20 maxlength=20></td>
</tr>
<tr>
<td colspan=2 align=center>(between 4 and 20 alphanumerics)</td>
</tr>
<tr>
<td>Email Address:</td>
<td><input type='text' name='email_address' size=20 maxlength=50></td>
</tr>
<tr>
<td colspan=2 align=center>(up to 30 alphanumerics, @, or .)</td>
</tr>
<tr>
<td>Admin Reg Pass:</td>
<td><input type='password' name='confirm' size=20 maxlength=20></td>
</tr>
<tr>
<td colspan=2 align=center>
<input type='submit' name='submit' value='Register'>
<input type='reset' value='Reset' onClick="return confirm('Reset?');">
</td>
</tr>
</table>
</form>
<a href="login.php">Back to admin login</a>
</td></tr></table>
</body>
<?php
}
?>

</html>

<?php
ob_end_flush();
?>
