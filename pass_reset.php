<?php
ob_start();
session_start();
require_once('functions/load_config.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.dat');

if($_POST['submit'] == 'Reset Password') {
	print "<table width=100% height=100%><tr><td align=center valign=center>";
	$user = ereg_replace("[^A-Za-z0-9]","",$_POST['username']);
	$email = ereg_replace("[^A-Za-z0-9@.]","",$_POST['email']);
	$sql = my_quick_con($config) or die("mysql problem");
	$query = "SELECT * FROM  $config[user_table] WHERE username='$user' AND email='$email';";
	$ret = mysql_query($query, $sql);
	if(mysql_num_rows($ret) > 0) {
		$new = "";
		$c_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		srand();
		for($i = 0; $i < 8; $i++) {
			$n = rand() % 25;
			$new .= substr($c_list, $n, 1); 
		}
		$new_hash = md5($new);
		$ret = mysql_query("UPDATE $config[user_table] SET password = '$new_hash' WHERE username='$user';", $sql);
		$header = "From: no-reply@HvZSource.com \r\n";
		$body  = "Your password has been reset.\n\n\n";
		$body .= "Your new password is: $new.\n\n";
		$body .= "You can change your password in your account page, once you login.\n\n";
		$body .= "--HvZSource";
		mail($email, "HvZSource: Password Reset", $body, $header);
		print "<body bgcolor='#000000'><font color='#ffffff'>Your password has been reset.<br>Check your email address for your new password.<br><a href='index.php'>Back to login</a></font></body>";
	} else {
		print "<body bgcolor='#000000'><font color='#ffffff'>That username or email address could not be found.<br>Please email your game administrator.<br><a href='index.php'>Back to login</a></font></body>";
	}
	mysql_close($sql); 
	print "</td></tr></table>";
} else {
include('template_top.php'); 
?>
<td>
<h1>Password Reset:</h1>
         <center>
<form method=POST action="pass_reset.php">
<table>
<tr>
	<td>username:</td>
	<td><input type='text' name='username' size=20 maxlength=20></td>
</tr>
<tr>
	<td>email address:</td>
	<td><input type='text' name='email' size=20 maxlength=30></td>
</tr>
<tr><td colspan=2 align=center valign=center>
	<input type='submit' name='submit' value='Reset Password'>
</td></tr>
</table>
</form>
</center>
<small>
<ul>
<li>You can use this page to reset your password in case you've forgotten it.</li>
<li><b>Both</b> username <b>and</b> password are case sensitive.</li>
<li>Both username and password can be alphanumeric <b>only</b>. This means that if you did something clever, like making your username "I'm so awesome!" what got saved to the database was "Imsoawesome".</li>
</ul>
</small>
</body>

<?php
}
?>
</td>
<?php include('template_bottom.php');

ob_end_flush();
?>
