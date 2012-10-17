<?php
ob_start();
session_start();
require_once('functions/load_config.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.dat');
$sql = my_quick_con($config) or die("MySQL problem");
$table_u = $config['user_table'];
$table_v = $config['var_table'];
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-started';"); 
$game_started = mysql_result($ret, 0);
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='reg-open';"); 
$reg_open = mysql_result($ret, 0);

// get the front content and save it into the session
if(!isset($_SESSION['content']['front'])) {
$result = mysql_query("SELECT value FROM $config[content_table] WHERE keyword='front'");
$row = mysql_fetch_assoc($result);
$_SESSION['content']['front'] = $row['value'];
}
$front = $_SESSION['content']['front'];

// Get current game summary
$game_sum_array = array();
$game_summary = '';
if($reg_open) {
	if($game_started) {
		// GET Game Start Time
		$query = "SELECT unix_timestamp(killed) killed FROM $table_u WHERE killed != '0000-00-00 00:00:00' ORDER BY killed ASC LIMIT 1;";
		$result = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$game_sum_array[$row["killed"]] = '<b>Game started at ' . date('g:i a', $row['killed']) . ' on ' . date('M jS', $row['killed']) . '</b>';
		}
		// GET Starves
		$query = "SELECT CONCAT(fname, ' ', lname) player, unix_timestamp(starved) starved FROM $table_u WHERE state = 0 AND active = 1 ORDER BY starved ASC;";
		$result = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$game_sum_array[$row["starved"]] = '<b>' . $row['player'] . '</b> starved at ' . date('g:i a', $row['starved']) . ' on ' . date('M jS', $row['starved']);
			}
		}
		// GET Tags
		$query = "SELECT (SELECT (SELECT CONCAT(k.fname, ' ', k.lname) FROM $table_u k WHERE k.id = u.killed_by)) as tagger, CONCAT(u.fname, ' ', u.lname) as tagged, UNIX_TIMESTAMP(u.killed) killed FROM $table_u u WHERE active AND state IN (-1, 0) AND u.killed_by IS NOT NULL ORDER BY u.killed;";
		$result = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$game_sum_array[$row["killed"]] = '<b>' . $row['tagger'] . '</b> tagged <b>' . $row['tagged'] . '</b> at ' . date('g:i a', $row['killed']) . ' on ' . date('M jS', $row['killed']);
			}
		}
		if(count($game_sum_array) > 0) {
			ksort($game_sum_array);
			$game_summary = implode("<br>\n", $game_sum_array);
		} else {
			$game_summary = 'No activity in this game yet';
		}
	 } else {
		$game_summary = 'Registration is OPEN! Hurry and get registered for the next round of the Zombie driven Apocalypse! ';
	 }
} else {
	$game_summary = 'Sorry, there\'s no game active right now. Please check back soon!';
}
?>
 
<?php include('template_top.php'); ?>
 
<!--<h3>The Humans vs. Zombies database has crashed, and we lost several days of game data. Please work with your moderators as they rebuild your game.</h3>
<p>-->

<?php print $front; ?>

<p><h2>Game Summary</h2><?= $game_summary; ?></p>
<br>
<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like href="http://www.facebook.com/pages/Humans-Vs-Zombies/103123970670?ref=ts" width="300" font="verdana"></fb:like>
<div style="padding-right:15px;">
<br>
<h3>HvZ Gear</h3>
<p><img class="aligncenter" src="http://humansvszombies.org/images/merch/graybrain.jpg" alt="" /></p>
<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="TAREM7UVNDU2W">
<table>
<tr><td><input type="hidden" name="on0" value="Sizes">Sizes</td></tr><tr><td><select name="os0">
	<option value="Men's Small">Men's Small $15.00</option>
	<option value="Men's Medium">Men's Medium $15.00</option>
	<option value="Men's Large">Men's Large $15.00</option>
	<option value="Men's XL">Men's XL $15.00</option>
	<option value="Men's XXL">Men's XXL $15.00</option>
	<option value="Women's Small">Women's Small $15.00</option>
	<option value="Women's Medium">Women's Medium $15.00</option>
	<option value="Women's Large">Women's Large $15.00</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_cart_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>


We still have some of our older designs left.  If you would rather purchase one of those, <a href="oldermerch">check 'em out</a>.

<br><br>
<img class="aligncenter" src="http://humansvszombies.org/images/merch/sm-hoodie.jpg" alt="" />
<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="ZZEDXY2D4PXMA">
<table>
<tr><td><input type="hidden" name="on0" value="Sizes"></td></tr><tr><td><select name="os0">
	<option value="Small">Small $35.00</option>
	<option value="Medium">Medium $35.00</option>
	<option value="Large">Large $35.00</option>
	<option value="XL">XL $35.00</option>
	<option value="XXL">XXL $35.00</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<br>
<b>We just received </b><b>heavy duty</b>,<b> Gildan</b>,<b> zip up</b>, <b>black</b> hoodies for the first time.  Get one NOW!  It is effin' cold!


<br><br><br><br>
<div style="padding-right:15px;">
<img src="http://humansvszombies.org/images/merch/bandana2k9.jpg">
<br><br>Check out these super sweet bandanas!  Order one here to look <b>OFFICIAL</b>!  They are just <b>$5</b>.<br><br>

<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="AAG7552ZPHR5N">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_cart_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<br>
</p>
</div>
</p>
 
<?php include('template_bottom.php'); ?>
 
