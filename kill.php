<?php
ob_start();
session_start();
require_once('security.php');
require_once('functions/load_config.php');
require_once('functions/quick_con.php'); 
$config = load_config('settings/config.dat'); 
$sql = my_quick_con($config) or die("MySQL problem"); 
$table_v = $config['var_table'];
$table_u = $config['user_table'];
$table_t = $config['time_table'];
// Get game settings
$ret = mysql_query("SELECT zone, feed_limit, starve_time FROM $table_t") or die(mysql_error());
$row = mysql_fetch_assoc($ret);
date_default_timezone_set($row['zone']);
$feed_limit = $row['feed_limit'];
$starve_time = $row['starve_time'];
// Kill starved zombies
mysql_query("UPDATE $table_u SET state = 0, starved = feed + INTERVAL $starve_time hour
			WHERE state < 0 AND now() > feed + INTERVAL $starve_time hour AND starved = '0000-00-00 00:00:00' AND active;") or die(mysql_error());
// Get OZ Revealed setting
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';") or die(mysql_error());
$reveal_oz = mysql_result($ret, 0);
// UCWATIDIDTHAR?
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Humans vs. Zombies :: Source</title>

<?php

$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-started';") or die(mysql_error());
$game_started = mysql_fetch_assoc($ret); 
$game_started = $game_started['value'];
if($game_started == 0) {
	mysql_free_result($ret);
	mysql_close($sql); 
	header("location:game_no_start.php");
}
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-over';") or die(mysql_error());
$game_over = mysql_fetch_assoc($ret);
$game_over = $game_over['ret'];
if($game_over == 1) {
        mysql_free_result($ret);
        mysql_close($sql);
        header("location:game_over.php");
}


if($_POST['submit'] == 'Report Tag') {
$victim = strtoupper(preg_replace("/[^A-Za-z0-9]/","",$_POST['victim_id']));
$feed = is_array($_POST['feed']) ? $_POST['feed']: array();
array_unshift($feed, $_SESSION['id']);//preg_replace("/[^A-Za-z0-9]/","",)
$hour = preg_replace("/[^0-9]/","",$_POST['hour'] + $_POST['am_pm']);
$minute = preg_replace("/[^0-9]/","",$_POST['minute']);
$days_ago = preg_replace("/[^0-1]/","",$_POST['days_ago']);
$err = 0; 
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';") or die(mysql_error());
$revealed = mysql_fetch_assoc($ret);
$revealed = $revealed['value'];

print "<table height=100% width=100%><tr><td align=center valign=center><font color='white'>";

// Get victim info
$ret = mysql_query("SELECT state, fname, lname FROM $table_u WHERE id='$victim' AND active;") or die(mysql_error());
$vrow = mysql_fetch_assoc($ret); 
$vstate = $vrow['state'];
if(mysql_num_rows($ret) == 0) {
	print "<body bgcolor='#0000'><font color='#ffff'>The ID number you entered could not be found.<br>"; 
	$err = 1; 
} else if($vstate <= 0) {
	print "<body bgcolor='#0000'><font color='#ffff'>Eating that person won't help you.<br>"; 
	$err = 1; 
} else {
	$current_hour = date('H');
	$current_minute = date('i');
	if($days_ago == 0 && (($hour > $current_hour) || ($hour == $current_hour && $minute > $current_minute))) {
		print "<body bgcolor='#0000'><font color='#ffff'>You can't eat people from the future.<br>";
		$err = 1;
	}
}
for($i = 0; $i < sizeof($feed) && $err == 0; $i++) { if(strlen($feed[$i]) > 0) {
	if(!$revealed) {
		$f = $feed[$i];
                $ret = mysql_query("SELECT state FROM $table_u WHERE id='$f' AND active;") or die(mysql_error());
                $temp = mysql_fetch_assoc($ret);
		$temp = $temp['state'];
                if($temp == '-2') $feed[$i] = 'OriginalZombie';
        }

	$ret = mysql_query("SELECT state, fname, lname, feed FROM $table_u WHERE id='$feed[$i]' AND active;") or die(mysql_error()); 
	if(mysql_num_rows($ret) == 0) { $err = 1; break; }

	$row = mysql_fetch_row($ret); 

	$f_state = $row[0]; 
	$feed_name = "$row[1] $row[2]";

//	$day = date('d');
//	if($today == 0) $day--;
//	$last_day = substr($row[3], 8, 2);
//	$last_hour = substr($row[3], 11, 2);
//	$last_minute = substr($row[3], 14, 2);

	if($f_state == 0) {		print "<body bgcolor='#0000'><font color='#ffff'>$feed_name is dead."; $err = 1; break; }
	else if($f_state > 0) {		print "<body bgcolor='#0000'><font color='#ffff'>$feed_name is not (yet?) a zombie."; $err = 1; break; }
//	else if(($day == $last_day && (($hour < $last_hour) || ($hour == $last_hour && $minute < $last_minute))) || $day < $last_day) {
//					print "$feed_name has fed more recently than this kill."; $err = 1; break;
//	}
}}
if($err == 0) {



$of = $feed[0];
$kill_time = date('Y-m-d', strtotime('-' . $days_ago . ' days')) . " $hour:$minute:00";
// increment kills for tagging zombie
mysql_query("UPDATE $table_u SET kills = kills + 1 WHERE id='$of';") or die(mysql_error());
// update values for tagged user
mysql_query("UPDATE $table_u SET state = -1, feed = TIMESTAMP '$kill_time' + INTERVAL 1 hour, killed_by = '$of',
	killed = TIMESTAMP '$kill_time' WHERE id='$victim';") or die(mysql_error());

for($i = 0; $i < sizeof($feed); $i++) { if(strlen($feed[$i]) > 0) {
	$f = $feed[$i];
	if(is_resource($ret)) { mysql_free_result($ret); }
	mysql_query("UPDATE $table_u SET feed = TIMESTAMP '$kill_time' WHERE id = '$f' and timediff(feed + INTERVAL $starve_time hour,now()) >= 0 AND active;") or die(mysql_error());
}}

	//TWITTER API
	// Get zombie name and state
	$ret = mysql_query("SELECT fname, lname, state FROM $table_u WHERE id='$of';") or die(mysql_error());
	$zom_row = mysql_fetch_array($ret);
	// The message you want to send
	// OZ is not revealed, OZ makes kill
	if(!$revealed && $zom_row[2] < -1){
		$message = "The original zombie has tagged " . $vrow['fname'] . " " . $vrow['lname'] . ".";
	}
	// Non-OZ or OZ after reveal makes a kill
	else{
		$message = $zom_row[0] . " " . $zom_row[1] . " has tagged " . $vrow['fname'] . " " . $vrow['lname'] . ".";
	}
	include("twitter.php");	

	print '<body bgcolor="#0000"><font color="#ffff">Tag Reported.</font><br><a href="kill.php">Go Back</a>';


} else {
	print '<a href="kill.php"><body bgcolor="#0000"><font color="#ffff">Go Back</a>';
}
print "</td></tr></table>";
} else { ?>
<script type="text/javascript">
<!-- 
function chkcontrol(j) {
	var total=0;
	var feed_zs = document.getElementsByName('feed[]');
	for(var i=0; i < feed_zs.length; i++){
		if(feed_zs[i].checked){
			total =total +1;
		}
		if(total > <?= $feed_limit; ?>){
			alert("Please Select only <?= $feed_limit; ?>") 
			feed_zs[j].checked = false ;
			return false;
		}
	}
} 
-->
</script>
<?php
include('template_top.php');
?>



<form name="kill_form" method=POST action="kill.php">
<center><h1>Report Tags:</h1>
<table border>
<tr>
<td>Victim's ID</td>
<td><input type='text' name='victim_id' size=20></td>
</tr>

<tr>
<td colspan=2>
<select name="days_ago">
	<option value='1'>Yesterday</option>
	<option value='0' selected>Today</option>
</select>
<select name="hour">
<?php
for($i = 1; $i <= 12; $i++) {
	print "<option value='$i'>$i</option>";
}
?>
</select>
<select name="minute">
<?php
for($i = 0; $i < 60; $i++) {
	$z = $i<10 ? '0' : '';
	print "<option value='$i'>$z$i</option>";
}
?>
</select>
<select name="am_pm">
	<option value='0'>AM</option>
	<option value='12'>PM</option>
</select>
</td>
</tr>

<tr>
<?php if($feed_limit > 0) { 
	$show = $feed_limit > 20 ? $feed_limit : 20;
?>
<td>Feed Up To <?= $feed_limit; ?> Other Zombie<?= $feed_limit > 1? 's': ''; ?><br>
(<?= $show; ?> hungriest shown)
</td>
<td>
<?php
$pid = $_SESSION['id']; 
if($reveal_oz) $ret = mysql_query("SELECT id, fname, lname, timediff(feed + INTERVAL $starve_time hour, now()) FROM $table_u WHERE state < 0 AND id != '$pid' AND active ORDER BY feed ASC limit $show;") or die(mysql_error()); 
else $ret = mysql_query("SELECT id, fname, lname, timediff(feed + INTERVAL $starve_time hour, now()) FROM $table_u WHERE state < 0 AND state != -2 AND id != '$pid' AND active ORDER BY feed ASC limit $show;") or die(mysql_error());
for($i = 0; $i < mysql_num_rows($ret); $i++) {
	$row = mysql_fetch_row($ret); 
	$till_starve = $row[3];
	$checked = $i < $feed_limit ? 'checked ' : '';
	print "\n\t<input name='feed[]' id='feed_me' type='checkbox' value='$row[0]' onClick='javascript:chkcontrol($i)' $checked/>$row[1] $row[2] ($till_starve)<br>"; 
}
?>&nbsp;
</td>
<?php } else { ?>
<td colspan="2">Feed sharing has been disabled for this game.
</td>
<?php } ?>
</tr>
<tr>
<td colspan=2 align=center>
<input type='submit' name='submit' value='Report Tag'>
</td>
</tr>
</table>
<table width=50%>
<tr><td align=left valign=top>
<ul>
<li>Victim ID is not case sensitive</li>
<li>Please report tag in 24 hour time!</li>
</ul>

</table>
<?php include('template_bottom.php');
 
}
?>

