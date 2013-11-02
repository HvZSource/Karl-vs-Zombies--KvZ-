<?php
ob_start();
require_once('functions/functions.php');
require_once('functions/quick_con.php'); 
$config = load_config('settings/config.php'); 
$id = $_SESSION['id'];
$sql = my_quick_con($config) or die("MySQL problem: " . mysql_error()); 
$table_v = $config['var_table']; 
$ret = mysql_query("SELECT value FROM {$table_v} WHERE keyword='reg-open';"); 

$open = mysql_fetch_assoc($ret);
$open = $open['value'];

if($open == 0) {
	mysql_free_result($ret);
	mysql_close($sql); 
	header("Location:reg_closed.php");
}

$ret = mysql_query("SELECT value FROM {$table_v} WHERE keyword='reg-closed';");
$closed = mysql_fetch_assoc($ret); 
$closed = $closed['value'];
if($closed == 1) {
	mysql_free_result($ret); 
	mysql_close($sql); 
	header("Location:reg_closed.php");
}
include('template_top.php');

if($_POST['submit'] == 'Register') {
$err = 0; 
$fname = preg_replace("/[^A-Za-z0-9]/","",$_POST['firstname']);
$lname = preg_replace("/[^A-Za-z0-9]/","",$_POST['lastname']);
$username = preg_replace("/[^A-Za-z0-9]/","",$_POST['username']);
$password1 = preg_replace("/[^A-Za-z0-9]/","",$_POST['password1']);
$password2 = preg_replace("/[^A-Za-z0-9]/","",$_POST['password2']); 
$email_address = preg_replace("/[^A-Za-z0-9@_.-]/","",$_POST['email_address']);

if($_POST['oz_opt'] == 'oz') {
	$oz_opt = 1; 
} else {
	$oz_opt = 0; 
}
print "<table height=100% width=100%><tr><td align=center valign=center>";
if(strlen($fname) > 30) {
$err = 1; 
print "Your first name is too long. Get a new one."; 
}
if(strlen($lname) > 30) {
$err = 1; 
print "Your last name is too long. Get a new one.";
}
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
if($err == 1) {
print "<a href='register.php'>Try again</a>";
} else {
$sql = my_quick_con($config) or die("MySQL problem: " . mysql_error()); 
$table_u = $config['user_table'];
$password = md5($password1);
$ret = mysql_query("SELECT * FROM $table_u WHERE username='$username';");
if(mysql_num_rows($ret) > 0) {
print "Someone has already registered with that username.<br>"; 
print "<a href='register.php'>Try again</a>";
} else {
	$id = '';
	$ret = mysql_query("SELECT * FROM {$table_u} WHERE email='{$email_address}';"); 
	if(mysql_num_rows($ret) > 0) {
		print "Someone has already registered with that email address.<br>"; 
		print "<a href='register.php'>Try again</a>";
	} else {
		$valid_id = 0; 
		$c_list = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
		srand(); 

		while($valid_id == 0) {
			$id = '';
			for($i = 0; $i < $config['id_length']; $i++) {
				$n = rand() % 35;
				$id .= substr($c_list, $n, 1); 
			}
			$ret = mysql_query("SELECT * FROM {$table_u} WHERE id='{$id}';");
			if(mysql_num_rows($ret) == 0) {
				$valid_id = 1; 
			}
		}
		mysql_query("INSERT INTO $table_u (id, fname, lname, username, password, email, oz_opt, state, kills, killed) VALUES ('{$id}','{$fname}','{$lname}','{$username}','{$password}','{$email_address}','{$oz_opt}', 1, 0, '0000-00-00 00:00:00');");
		print "Registered.<br>";
                print "<a href='index.php'>Home</a><br><br> Your ID is: {$id}";
                print "<br>Please write it down and carry it with you during the game.  If a zombie tags you, please give them your ID. <br>You can also find your ID in the My Account section of the site.";

		//TWITTER
		// The message you want to send
		$message = $fname . " " . $lname . " has registered for HvZ!";
		include("twitter.php");

		//email player

$header = "From: no-reply@HvZSource.com \r\n";
$body  = "Hi there {$fname}.\n\n";
$body .= "Thanks for Registering for HvZ.\n\n\n";
$body .= "Your ID number is: {$id}.  Please write it down and carry it with you at all times.\n\n";
$body .= "You can change your password and find your ID number in your account page, once you login.\n\n";
$body .= "--HvZSource";
mail($email_address, "HvZSource: Registration Confirmation", $body, $header);

	}
}

mysql_close($sql);
}
print "</td></tr></table>";

} else { 
?>

<table height=100% width=100%><tr><td align=center valign=center>
<h3>Registration</h3>
<form method=POST action="register.php">
<table>
<tr>
<td>first name:</td>
<td><input type='text' name='firstname' size=20 maxlength=20></td>
</tr>
<tr>
<td>last name:</td>
<td><input type='text' name='lastname' size=20 maxlength=20></td>
</tr>
<tr>
<td>username:</td>
<td><input type='text' name='username' size=20 maxlength=20></td>
</tr>
<tr><td colspan=2 align=center>(between 4 and 20 alphanumerics)</td></tr>
<tr>
<td>password:</td>
<td><input type='password' name='password1' size=20 maxlength=20></td>
</tr>
<tr>
<td>confirm password:</td>
<td><input type='password' name='password2' size=20 maxlength=20></td>
</tr>
<tr>
<td colspan=2 align=center>(between 4 and 20 alphanumerics)</td>
</tr>
<tr>
<td>email address:</td>
<td><input type='text' name='email_address' size=20 maxlength=80></td>
</tr>
<tr>
<td colspan=2 align=center>(up to 30 alphanumerics)</td>
</tr>
<tr>
<td colspan=2 align=center>
<input type='checkbox' name='oz_opt' value='oz'> Put me in the original zombie pool
</td>
</tr>
<tr>
<td colspan=2 align=center>
<input type='submit' name='submit' value='Register'>
<input type='reset' value='Reset' onClick="return confirm('Reset?');">
</td>
</tr>
</table>
</form>
</td></tr></table>

<?php
}
include('template_bottom.php');
ob_end_flush();
?>
