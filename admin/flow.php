<?php
ob_start();
session_start();
require_once('security.php'); 
require_once('../functions/load_config.php'); 
require_once('../functions/quick_con.php'); 
$box = 0; 
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("Database Problem"); 
$table_v = $config['var_table'];
$table_u = $config['user_table'];
$table_t = $config['time_table'];
// Set default time zone
$ret = mysql_query("SELECT zone FROM $table_t");
while($row = mysql_fetch_array($ret))
	   date_default_timezone_set($row['zone']);
?>

<html> 
<head> 
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<?php
if($_POST['submit'] == 'Advance') {
$step = $_POST['step']; 
if($step == 'oz-selected') {
	header("Location:oz_pick.php");
	$message="The original zombie is being chosen!";
}
elseif($step == 'reg_open'){
	$message = "Registration for HvZ has opened!";
}
elseif($step == 'reg_closed'){
	$message = "Registration for HvZ has closed!";
}
elseif($step == 'game-over'){
	$message = "Humans Vs. Zombies is over!  Thanks for playing!";
}
else {
	$ret = mysql_query("UPDATE $table_v SET value=1 WHERE keyword='$step';");
	if($step == 'game-started') {
		$ret = mysql_query("UPDATE $table_u SET feed = now() WHERE state = -3;");
		$ret = mysql_query("UPDATE $table_u SET killed = now() WHERE state = -3;");
		$message = "Humans Vs. Zombies has started!  Don't get killed!";
	} 
	if($step == 'oz-revealed') {
		$ret = mysql_query("SELECT kills, killed, feed FROM $table_u WHERE state = -3;"); 
		$row = mysql_fetch_row($ret); 
		$ret = mysql_query("UPDATE $table_u SET kills = $row[0] WHERE state = -2;"); 
		$ret = mysql_query("UPDATE $table_u SET killed = TIMESTAMP '$row[1]' WHERE state = -2;");
		$ret = mysql_query("UPDATE $table_u SET feed = TIMESTAMP '$row[2]' WHERE state = -2;");
		$ret = mysql_query("DELETE FROM $table_u WHERE state = -3;");
		$message = "The original zombie has been revealed!";
	}
}	
include("../twitter.php");
}
?>

<body> 
<h3>Game Flow Manager</h3>

<form method=POST action=<?php echo $PHP_SELF; ?>>
<table width=100% border> 
<tr>
<td width=1%><input type='checkbox'></td>
<td>stage</td>
</tr>

<tr>
<td valign=top>
<?php
if($box == 0) {
	$query = "SELECT value FROM $table_v WHERE keyword = 'reg-open';";
	$ret = mysql_query($query);
	$val = mysql_fetch_assoc($ret);
	$val = $val['value'];
	if($val == 0) {
		$box = 1; 
		print "<input type='checkbox' name='step' value='reg-open'>";
	}
}
?>
</td>
<td>
<b>Open Registration</b><p>
Do you want to open registeration?<br>
</td>
</tr>

<tr>
<td valign=top>
<?php
if($box == 0) {
	$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='reg-closed';");
	$val = mysql_fetch_assoc($ret);
	$val = $val['value'];
	if($val == 0) {
		$box = 1; 
		print "<input type='checkbox' name='step' value='reg-closed'>";
	}
}
?>
</td>
<td>
<b>Close Registration</b><p>
Ahh we have enough users! Close regisration!<br>You can always <a href='openreg.php'> reopen registration</a> anytime during your game if there are stragglers.
</td>
</tr>

<tr>
<td valign=top>
<?php
if($box == 0) {
	$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-selected';");
	$val = mysql_fetch_assoc($ret);
	$val = $val['value'];
	if($val == 0) {
		$box = 1; 
		print "<input type='checkbox' name='step' value='oz-selected'>";
	}
}
?>
</td>          
<td>
<b>Pick Original Zombie</b><p>
Who is it going to be?<br>
</td>
</tr>

<tr>
<td valign=top>
<?php
if($box == 0) {
	$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-started';");
	$val = mysql_fetch_assoc($ret);
	$val = $val['value'];
	if($val == 0) {
		$box = 1; 
		print "<input type='checkbox' name='step' value='game-started'>";
	}
}
?>
</td>          
<td>
<b>Start Game</b><p>
The game's afoot!<br>
</td>
</tr>

<tr>
<td valign=top>
<?php
if($box == 0) {
	$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
	$val = mysql_fetch_assoc($ret);
	$val = $val['value'];
	if($val == 0) {
		$box = 1; 
		print "<input type='checkbox' name='step' value='oz-revealed'>";
	}
}
?>
</td>          
<td>
<b>Reveal Original Zombie</b><p>
Look everyone already knows, make it official!<br>
</td>
</tr>

<tr>
<td colspan=2 align=center>
<input type='submit' name='submit' value='Advance' onClick='return confirm("Are you certain that you wish to advance the game?\n  This cannot be reversed.")'>
</td>
</tr>

</table>
</form>
<center>If you need us reset the system entirely, please shoot us off an <a href="mailto:contact@HvZSource.com">email</a>.</center>

</body> 

</html>

<?php
mysql_free_result($ret);
mysql_close($sql);
ob_end_flush();
?>
