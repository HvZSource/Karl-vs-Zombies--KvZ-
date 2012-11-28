<?php
ob_start();
session_start();
require_once('security.php'); 
require_once('../functions/load_config.php'); 
require_once('../functions/quick_con.php'); 
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("Database Problem"); 
$table_v = $config['var_table'];
$table_u = $config['user_table'];
$table_t = $config['time_table'];

// Set default time zone
$ret = mysql_query("SELECT zone FROM $table_t");
date_default_timezone_set(mysql_result($ret, 0));

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
	} elseif($step == 'game-over'){
		$message = "Humans Vs. Zombies is over! Thanks for playing!";
		// Clean up phantom Original Zombie user, copy stats to user's account
		$query = "SELECT kills, killed, feed FROM $table_u WHERE state = -3;"; 
		$ret = mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
		if(mysql_num_rows($ret) > 0) { 
			$row = mysql_fetch_row($ret); 
			$query = "UPDATE $table_u SET kills = $row[0], killed = TIMESTAMP '$row[1]', feed = TIMESTAMP '$row[2]' WHERE state = -2;";
			mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
			$query = "DELETE FROM $table_u WHERE id = 'OriginalZombie';";
			mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
		}
		// Reset all users to fresh, saving stats
		$query = "UPDATE $table_u SET 
				lifetime_kills = lifetime_kills + kills, 
				games_completed = games_completed + 1,
				active = 0, state = 1, kills = 0, killed_by = null,
				killed = '0000-00-00 00:00:00', feed = '0000-00-00 00:00:00', starved = '0000-00-00 00:00:00';";
		mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
		// Reset game to fresh
		$query = "UPDATE $table_v SET value = 0;";
		mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
	} else {
		$query = "UPDATE $table_v SET value=1 WHERE keyword='$step';";
		mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
		if($step == 'game-started') {
			mysql_query("UPDATE $table_u SET feed = now(), killed = now() WHERE state = -3;");
			$message = "Humans Vs. Zombies has started!  Don't get killed!";
		} elseif($step == 'reg-open'){
			$message = "Registration for HvZ has opened!";
		} elseif($step == 'reg-closed'){
			$message = "Registration for HvZ has closed!";
		} elseif($step == 'oz-revealed') {
			// Clean up phantom Original Zombie user, copy stats to user's account
			$query = "SELECT kills, killed, feed FROM $table_u WHERE state = -3;"; 
			$ret = mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
			$row = mysql_fetch_row($ret); 
			$query = "UPDATE $table_u SET kills = $row[0], killed = TIMESTAMP '$row[1]', feed = TIMESTAMP '$row[2]' WHERE state = -2;";
			mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
			$query = "DELETE FROM $table_u WHERE id = 'OriginalZombie';";
			mysql_query($query) or die("SQL Error (line " . __LINE__ . "): " . mysql_error() . '---' . $query);
			mysql_query("UPDATE $table_u SET killed_by = (SELECT oz FROM (SELECT id AS oz FROM $table_u WHERE state = -2) sub_query) WHERE killed_by = 'OriginalZombie';");
			mysql_query("DELETE FROM $table_u WHERE state = -3;");
			$message = "The original zombie has been revealed!";
		}
	}	
	include("../twitter.php");
}

// Grab game flow variables
$query = "SELECT * FROM $table_v;";
$ret = mysql_query($query);
while($row = mysql_fetch_assoc($ret)) {
	$game_vars[$row['keyword']] = $row['value'];
}
$reg_open = $game_vars['reg-open'];
$reg_closed = $game_vars['reg-closed'];
$oz_selected = $game_vars['oz-selected'];
$game_started = $game_vars['game-started'];
$oz_revealed = $game_vars['oz-revealed'];
?>

<body> 
<h2>Game Flow Manager</h2>

<form method=POST action="flow.php">
<table width=100% border> 
<tr>
<td width=1%>&nbsp;</td>
<td><h2>Game Stage</h2></td>
</tr>

<tr>
<td valign=top>
<?php
// 
if($reg_open == 1) {
	$fini = ' disabled="true" checked'; 
} else {
	$fini = '';
}
print "<input type='checkbox' name='step' value='reg-open'$fini>";
?><br>&nbsp;
</td>
<td><b>Open Registration</b><p>
Do you want to open registration?<br>
</td>
</tr>

<tr>
<td valign=top>
<?php
if($reg_open) {
	if($reg_closed == 1) {
		$fini = ' disabled="true" checked'; 
	} else {
		$fini = '';
	}
	print "<input type='checkbox' name='step' value='reg-closed'$fini>";
}
?><br>&nbsp;
</td>
<td><b>Close Registration</b><p>
Ahh we have enough users! Close registration!<br>You can always <a href='openreg.php'> reopen registration</a> anytime during your game if there are stragglers.
</td>
</tr>

<tr>
<td valign=top>
<?php
if($reg_closed || $game_started) {
	if($oz_selected == 1) {
		$fini = ' disabled="true" checked'; 
	} else {
		$fini = '';
	}
	print "<input type='checkbox' name='step' value='oz-selected'$fini>";
}
?><br>&nbsp;
</td>          
<td><b>Pick Original Zombie</b><p>
Who is it going to be?<br>
</td>
</tr>

<tr>
<td valign=top>
<?php
if($oz_selected) {
	if($game_started == 1) {
		$fini = ' disabled="true" checked'; 
	} else {
		$fini = '';
	}
	print "<input type='checkbox' name='step' value='game-started'$fini>";
}
?><br>&nbsp;
</td>          
<td><b>Start Game</b><p>
The game's afoot!<br>
</td>
</tr>

<tr>
<td valign=top>
<?php
if($game_started) {
	if($oz_revealed == 1) {
		$fini = ' disabled="true" checked'; 
	} else {
		$fini = '';
	}
	print "<input type='checkbox' name='step' value='oz-revealed'$fini>";
}
?><br>&nbsp;
</td>          
<td><b>Reveal Original Zombie</b><p>
Look, everyone already knows. Make it official!<br>
</td>
</tr>

<tr>
<td valign=top>
<?php
if($game_started) { ?>
<input type='checkbox' name='step' value='game-over'>
<?php } ?><br>&nbsp;
<td><b>Reset Game</b><p>
Let's put this game in the ground and raise everyone from the dead so we can start a whole new game!<br>
</td>
</tr>
<tr>
<td colspan=2 align=center>
<input type='submit' name='submit' value='Advance' onClick='return confirm("Are you certain that you wish to advance the game?\n  This cannot be reversed.")'>
</td>
</tr>
<tr>
<td colspan=2>
---- WISH LIST ----<br>
BUGS:<br>
-Fix bug with underscore in email address<br>
-Game Summary messes up when OZ starves<br>
PLAYERS HOME:<br>
-Stats on home page (top taggers, player counts)<br>
PLAYERS LIST:<br>
-Add color coding (admin controllable?) to each group, mostly on Player List page<br>
-Add lifetime kills/stats to players list?<br>
PLAYER ACCOUNT:<br>
-Support two profile pics, 1 human, 1 zombie (eliminate spaces in image names)<br>
-Show if player isn't registered for current game on account page<br>
PLAYER TAG REPORT:<br>
-Add email to tagged player (and fed zombies?) with incubation time/new starve time<br>
ADMIN PLAYER EDITOR:<br>
-Add OZ pool setting and Killed By dropdown to admin player editor<br>
ADMIN GAME SETTINGS:<br>
-Add incubation time to game settings<br>
-Add stun time to game settings<br>
-Add game start day/time (use in OZ emails, tag qualifications, etc.)<br>
-Control ID length through game settings rather than config file<br>
-Add support for game hours (work hours, weekends, etc.) to prevent starvation at non-playing times<br>
-Support multiple OZ's at beginning of game<br>
-Add G-rated (stun, tag, blaster) or R-rated (shoot, kill, gun) option to game settings with disclaimer<br>
OTHER FEATURES:<br>
-Admin controllable messages: OZ reason, zombie report, 
-Make settings/config.dat into config.php to prevent snoopers<br>
-Update style HTML for modern browsers (see <a href="http://www.osundead.com/index.php">OSU HvZ</a>)<br>
-Add human stats (time alive, zombie stuns)<br>
-Replace mail() with notify() function that supports alternate user-controlled methods (SMS, Facebook, etc.)<br>
-Make game creation process easier (created through admin system)<br>
-Save Game History (using summary?)<br>
-Make site mobile friendly<br>
-Maybe support QR code tag reports?<br>
</td>
</tr>

</table>
</form>
<center>If you need us to reset the system entirely, please shoot us off an <a href="mailto:contact@HvZSource.com">email</a>.</center>

</body> 

</html>

<?php
mysql_close($sql);
ob_end_flush();
?>
