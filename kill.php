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
// Set default time zone
$ret = mysql_query("SELECT zone FROM $table_t");
while($row = mysql_fetch_array($ret))
	   date_default_timezone_set($row['zone']);
$ret = mysql_query("UPDATE $table_u SET state = -4 WHERE now() > feed + INTERVAL 2 day;");
$ret = mysql_query("UPDATE $table_u SET starved = feed + INTERVAL 2 day WHERE state = -4;");
$ret = mysql_query("UPDATE $table_u SET state = 0 WHERE state = -4;");
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
// this is an array fetching bug 
// $reveal_oz = mysql_fetch_assoc($ret);
// $reveal_oz is clearly expecting a scalar return value and not an array
// therefore, you have to look at the query and figure out which single field 
// it was trying to extract (value in this case, based on the prior SELECT).
$reveal_oz = mysql_fetch_assoc($ret);
$reveal_oz = $reveal_oz['value'];
// UCWATIDIDTHAR?
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Humans vs. Zombies :: Source</title>

<?php
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-started';");
$game_started = mysql_fetch_assoc($ret); 
$game_started = $game_started['value'];
if($game_started == 0) {
	mysql_free_result($ret);
	mysql_close($sql); 
	header("location:game_no_start.php");
}
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-over';");
$game_over = mysql_fetch_assoc($ret);
$game_over = $game_over['ret'];
if($game_over == 1) {
        mysql_free_result($ret);
        mysql_close($sql);
        header("location:game_over.php");
}


if($_POST['submit'] == 'Report Tag') {
$victim = strtoupper(ereg_replace("[^A-Za-z0-9]","",$_POST['victim_id']));
$feed[0] = $_SESSION['id'];
$feed[1] = ereg_replace("[^A-Za-z0-9]","",$_POST['feed1']);
$feed[2] = ereg_replace("[^A-Za-z0-9]","",$_POST['feed2']);
$hour = ereg_replace("[^0-9]","",$_POST['hour']);
$minute = ereg_replace("[^0-9]","",$_POST['minute']);
$today = ereg_replace("[^0-1]","",$_POST['day']);
$err = 0; 
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
$revealed = mysql_fetch_assoc($ret);
$revealed = $revealed['value'];

print "<table height=100% width=100%><tr><td align=center valign=center><font color='white'>";

$ret = mysql_query("SELECT state FROM $table_u WHERE id='$victim';");
$vstate = mysql_fetch_assoc($ret); 
$vstate = $vstate['state'];
if(mysql_num_rows($ret) == 0) {
	print "<body bgcolor='#0000'><font color='#ffff'>The ID number you entered could not be found.<br>"; 
	$err = 1; 
} else if($vstate <= 0) {
	print "<body bgcolor='#0000'><font color='#ffff'>Eating that person won't help you.<br>"; 
	$err = 1; 
} else {
	$current_hour = date('H');
	$current_minute = date('i');
	if($today == 1 && (($hour > $current_hour) || ($hour == $current_hour && $minute > $current_minute))) {
		print "<body bgcolor='#0000'><font color='#ffff'>You can't eat people from the future.<br>";
		$err = 1;
	}
}
for($i = 0; $i < sizeof($feed) && $err == 0; $i++) { if(strlen($feed[$i]) > 0) {
	if(!$revealed) {
		$f = $feed[$i];
                $ret = mysql_query("SELECT state FROM $table_u WHERE id='$f';");
                $temp = mysql_fetch_assoc($ret);
		$temp = $temp['state'];
                if($temp == '-2') $feed[$i] = 'OriginalZombie';
        }

	$ret = mysql_query("SELECT state, fname, lname, feed FROM $table_u WHERE id='$feed[$i]';"); 
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
$kill_time = date('Y-m-d') . " $hour:$minute:00";
$ret = mysql_query("UPDATE $table_u SET kills = kills + 1 WHERE id='$of';");
$ret = mysql_query("UPDATE $table_u SET state = -1 WHERE id='$victim';");
$ret = mysql_query("UPDATE $table_u SET feed = TIMESTAMP '$kill_time' + INTERVAL 1 hour WHERE id='$victim';");
if($today == 0) $ret = mysql_query("UPDATE $table_u SET feed = feed - INTERVAL 1 day WHERE id='$victim';");
$ret = mysql_query("UPDATE $table_u SET killed_by = '$of' WHERE id='$victim';");
$ret = mysql_query("UPDATE $table_u SET killed = TIMESTAMP '$kill_time' WHERE id='$victim';");
if($today == 0) $ret = mysql_query("UPDATE $table_u SET killed = killed - INTERVAL 1 day WHERE id='$victim';");

for($i = 0; $i < sizeof($feed); $i++) { if(strlen($feed[$i]) > 0) {
	$f = $feed[$i];
	$kill_time = date('Y-m-d') . " $hour:$minute:00";
	        if(is_resource($ret)) {
     mysql_free_result($ret);
}
$ret = mysql_query("UPDATE $table_u SET feed = TIMESTAMP '$kill_time' WHERE id = '$f' and timediff(feed + INTERVAL 2 day,now());") >= 0;

        if($ret && $today == 0) $ret = mysql_query("UPDATE $table_u SET feed = feed - INTERVAL 1 day WHERE id='$f';");
}}

	//TWITTER API
	// Get victim name
	$ret = mysql_query("SELECT fname, lname FROM $table_u WHERE id='$victim';");
	$vic_row = mysql_fetch_array($ret);
	// Get zombie name and state
	$ret = mysql_query("SELECT fname, lname, state FROM $table_u WHERE id='$of';");
	$zom_row = mysql_fetch_array($ret);
	// The message you want to send
	// OZ is not revealed, OZ makes kill
	if(!$revealed && $zom_row[2] < -1){
		$message = "The original zombie has tagged " . $vic_row[0] . " " . $vic_row[1] . ".";
	}
	// Non-OZ or OZ after reveal makes a kill
	else{
		$message = $zom_row[0] . " " . $zom_row[1] . " has tagged " . $vic_row[0] . " " . $vic_row[1] . ".";	
	}
	include("twitter.php");	

	print "<body bgcolor='#0000'><font color='#ffff'>Tag Reported.</font><br><a href=$PHP_SELF>Go Back</a>";


} else {
	print "<a href=$PHP_SELF><body bgcolor='#0000'><font color='#ffff'>Go Back</a>";
}
print "</td></tr></table>";
} else {

include('template_top.php');

?>



<form method=POST action=<?php echo $PHP_SELF; ?>>
<center><h1>Report Tags:</h1>
<table border>
<tr>
<td>Victim</td>
<td><input type='text' name='victim_id' size=20></td>
</tr>

<tr>
<td colspan=2>
<select name="day">
	<option value='0'>Yesterday</option>
	<option value='1' selected>Today</option>
</select>
<select name="hour">
<?php
for($i = 0; $i < 24; $i++) {
	print "<option value='$i'>$i</option>";
}
?>
</select>
<select name="minute">
<?php
for($i = 0; $i < 60; $i++) {
	print "<option value='$i'>$i</option>";
}
?>
</select>
</td>
</tr>

<tr>
<td>Feed 1</td>
<td>
<select name='feed1'>
	<option></option>
<?php
$pid = $_SESSION['id']; 
if($reveal_oz) $ret = mysql_query("SELECT id, fname, lname, timediff(feed + INTERVAL 2 day, now()) FROM $table_u WHERE state < 0 AND id != '$pid' ORDER BY feed ASC;"); 
else $ret = mysql_query("SELECT id, fname, lname, timediff(feed + INTERVAL 2 day, now()) FROM $table_u WHERE state < 0 AND state != -2 AND id != '$pid' ORDER BY feed ASC;");
for($i = 0; $i < mysql_num_rows($ret); $i++) {
	$row = mysql_fetch_row($ret); 
	$till_starve = $row[3];
	print "<option value='$row[0]'>$row[1] $row[2] ($till_starve)</option>"; 
}
?>
</select>
</td>
</tr>

<tr>
<td>Feed 2</td>
<td>
<select name='feed2'>
        <option></option>
<?php
mysql_data_seek($ret, 0);
for($i = 0; $i < mysql_num_rows($ret); $i++) {
        $row = mysql_fetch_row($ret); 
	$till_starve = $row[3];
	print "<option value='$row[0]'>$row[1] $row[2] ($till_starve)</option>";
}
?>
</select>
</td>
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

