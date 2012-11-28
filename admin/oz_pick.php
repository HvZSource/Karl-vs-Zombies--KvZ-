<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("MySQL problem"); 
$table_v = $config['var_table']; 
$table_u = $config['user_table'];
$err = 0; 
?>

<html>
<head>
<title>Pick Original Zombie</title>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<body>
<?php

if($_POST['submit'] == 'Select Original Zombie') {

$oz = mysql_real_escape_string($_POST['oz_pick']);
// users table 
mysql_query("UPDATE $table_u SET state = -2 WHERE id='$oz';") or die(mysql_error());
mysql_query("INSERT INTO $table_u (id, username, password, email, pic_path, fname, lname, state, kills, oz_opt) 
			VALUES ('OriginalZombie','OriginalZombie','" . rand() . "','oz@humansvszombies.org','images/OZ.jpg','Original','Zombie', -3, 0, 0);") or die(mysql_error());


// variables table
mysql_query("UPDATE $table_v SET value = 1 WHERE keyword='oz-selected';") or die(mysql_error());


$ret = mysql_query("SELECT fname, lname, email FROM $table_u WHERE id='$oz';") or die(mysql_error());
$row = mysql_fetch_row($ret);
?>
<table height=100% width=100%><tr><td align=center valign=center>
<div name="oz" id="oz" style="display:none"><?= $row[0] . ' ' . $row[1]; ?> has been selected as the original zombie.</div><br>
The original zombie has been chosen. This has been kept hidden for privacy reasons.<br>
If you need to know, click <a href="javascript:void(0);" onClick="javascript:document.getElementById('oz').style.display='block';">show</a><br>
<br>
<a href='flow.php'>Back to game flow</a>
</td></tr></table>

<?php
$zombie_reasons = array(
	'Well, got some news for you about that scratch on your knee from your freak zip-lining accident. You have a flesh-eating virus that doesn\'t just eat flesh, it makes you want to eat flesh.',
	'Remember that last vaccine you got? Well, it was experimental and seems to have one little side-effect: you become a mindless automaton, incapable of remembering the past, unable to recognise loved ones and doomed to an existence of cannibalistic hunger.',
	'The self-replicating medical nanobots that were implanted to repair your recent brain trauma have shut down the part of the brain that resists (the cortex) making you their proverbial b%+ch. To preserve themselves, they\'re making you hunt down new host-brains in which to setup camp.',
	'You know that weird neighbor you have? The one who always stares, but doesn\'t make eye-contact? Well, he\'s been dabbling in voodoo and has chosen you as his test subject. He slipped you some potion and you now have a raging case of Zombie-itis.',
	'Sorry to have to inform you, but that beef you recently ate had a mutated strain of mad cow disease. It\'s a fast acting virus that leaves you with a swollen brain, a raging fever, makes you hateful and violent and leaves you with a really bad case of the munchies.',
	'A top-secret government experiment "misplaced" some toxic spores and they got into your coffee. The spore has turned your brain to mush, leaving behind only the part that controls basic motor function and primitive instincts. Turns out your primitive instincts mostly involve gorey violence and consumption of raw flesh. Who knew?',
);
$rand_reason = array_rand($zombie_reasons);

//email selected OZ
$header = "From: no-reply@HvZSource.com \r\n";
$body  = "Hi there {$row[0]}.\n\n";
$body .= $zombie_reasons[$rand_reason] . ' ';
$body .= "Oh, and it's contagious. Well, been nice knowing ya. Welcome to HvZ...\n\n";
$body .= 'YOU ARE THE ORIGINAL ZOMBIE! "OZ", for short ;)' . "\n\n";
$body .= "Keep this quiet until the game starts! Remember, a zombie's best weapons are stealth and trickery.\n\n";
$body .= "--HvZSource";
mail($row[2], "HvZSource: Shhhh.....", $body, $header);

//email active players
$ret = mysql_query("SELECT fname, lname, email FROM $table_u WHERE active AND state !=-3;") or die(mysql_error());
$body  = 'URGENT NEWS BULLETIN: We are receiving unconfirmed reports from all over the area of people exhibiting zombie-like behavior. Details are sketchy right now, but everyone is urged to arm themselves until more detailed information is available.' . "\n\n";
$body .= 'THE ORIGINAL ZOMBIE IS AMONG US! "OZ", for short ;)' . "\n\n";
$body .= "Be prepared for the game to start! Remember, zombie's are stealthy, stay on your toes.\n\n";
$body .= "--HvZSource";
while($row = mysql_fetch_row($ret)) {
	//echo $row[2] . ": " . $body . "<br><br>\n\n";
	mail($row[2], "HvZSource: Zombies!!!", $body, $header);
}


} else {
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='reg-closed';") or die(mysql_error());
$reg_closed = mysql_fetch_assoc($ret);
$reg_closed = $reg_closed['value'];
if($reg_closed == 0) {
	$err = 1; 
	print "<table height=100% width=100%><tr><td align=center valign=center>User registration has not been completed.</td></tr></table>"; 
}

if($err == 0) {
	$ret = mysql_query("SELECT fname, lname, id FROM $table_u WHERE oz_opt=1 AND active;") or die(mysql_error());
	if(mysql_num_rows($ret) > 0) {
?>

<form method=POST action="oz_pick.php">
<center>
<table>

<?php
$ozs = array();
for($i = 0; $i < mysql_num_rows($ret); $i++) {
	$row = mysql_fetch_assoc($ret); ?> 
		<tr>
		<td><input type='radio' name='oz_pick' value='<?php print $row['id']; ?>'></td>
		<td><?php print $row['fname'] . " " . $row['lname']; ?></td>
		</tr>
<?php
	$ozs[$row['id']] =  $row['id'];
}
$rand_oz = array_rand($ozs);
?>
		<tr>
		<td><input type='radio' name='oz_pick' value='<?php print $rand_oz; ?>' checked></td>
		<td>Pick a Random OZ!</td>
		</tr>
</table>
<input type='submit' name='submit' value='Select Original Zombie'>
</center>
</form>

<?php
	} else {
		echo "<center>No one has registered to be the Original Zombie!</center>";
	}
}
}
?>
</body>
</html>
<?php
mysql_free_result($ret);
mysql_close($sql); 
ob_end_flush();
?>
