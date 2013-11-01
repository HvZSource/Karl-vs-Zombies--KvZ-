<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/functions.php');
require_once('../functions/quick_con.php'); 
$config = load_config('../settings/config.php');
$table_t = $config['time_table'];
$table_v = $config['var_table'];
$conn = my_quick_con($config) or die("MySQL problem"); 

$gs_ret = mysql_query("SELECT value from $table_v where keyword = 'game-started'");
$game_started = mysql_result($gs_ret, 0);
?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>
<body>
<h2>Game Settings</h2>

<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
	// Grab variables from POST and sanitize for insert
	$tz = mysql_real_escape_string($_POST['timezone']);
	$fl = mysql_real_escape_string($_POST['feed_limit']);
	$st = mysql_real_escape_string($_POST['starve_time']);

	// Update database with new settings
	$sql = "UPDATE $table_t SET zone = '$tz', feed_limit = '$fl', starve_time = '$st'";
	if(!mysql_query($sql,$conn))
		die('Error: ' . mysql_error());
	echo "<br><br>Settings updated. You will be re-directed in 3 seconds...";
	header("refresh: 4; url=time.php");
	echo '<br><br>';
} elseif($game_started && $_GET['edit'] != 'yes') {
	echo "This game of HvZ has already started! Are you sure you want to change settings during the game??";
	echo '<br><br><a href="?edit=yes">Yes, I really do want to screw with the rules mid-game. I\'m a cowboy!</a>';
	echo '<br><br><a href="flow.php">No way! What was I thinking!?</a>';
} else {
?>


<form name="timezone" method=POST action="time.php">
<?php
// Time Zone Setting
$timeZones = DateTimeZone::listIdentifiers();
$ret = mysql_query("SELECT zone, feed_limit, starve_time FROM $table_t");
$row = mysql_fetch_assoc($ret);
$current = $row['zone'];
echo 'Set the time zone of your game: <select name="timezone">';
foreach ( $timeZones as $timeZone ) {
	$selected = $timeZone == $current ? ' selected': '';
	echo '<option value="' . $timeZone . '"' . $selected . '>' . $timeZone . '</option>';
}
echo '</select><br><br>';

// Zombie Feed Share Limit
$current = $row['feed_limit'];
echo 'Number of zombies to share with: <select name="feed_limit">';
for($i = 0; $i <= 10; $i++) {
	$selected = $i == $current ? ' selected': '';
	print "<option value='$i'$selected>$i</option>";
}
echo '</select><br><br>';

// Zombie Starve Time
$current = $row['starve_time'];
echo 'Time until zombies starve (hours): <select name="starve_time">';
for($i = 1; $i <= 100; $i++) {
	$selected = $i == $current ? ' selected': '';
	print "<option value='$i'$selected>$i</option>";
}
echo '</select><br><br>';
?>
<input type="submit" value="Save Settings"/>
</form>
<? } ?>
<br />
<br />

</body>
</html>
