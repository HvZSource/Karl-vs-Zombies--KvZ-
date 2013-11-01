<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/functions.php');
require_once('../functions/quick_con.php'); 
$config = load_config('../settings/config.php');
$table_u = $config['user_table'];
$table_v = $config['var_table'];
$sql = my_quick_con($config) or die("MySQL problem"); 
$ret = mysql_query("UPDATE $table_u SET state = -4 WHERE TIMESTAMP 'now' > feed + INTERVAL '2 days';"); 
$ret = mysql_query("UPDATE $table_u SET starved = feed + INTERVAL '2 days' WHERE state = -4;");
$ret = mysql_query("UPDATE $table_u SET state = 0 WHERE state = -4;");
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
$reveal_oz = mysql_fetch_assoc($ret);
$reveal_oz = $reveal_oz['value'];
?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<?php
if($_POST['submit'] == 'Submit') {
$file = "twitter_conf.txt";
$fh = fopen($file, 'w') or die("Can't open file.");
$start = "<?php \n";
$end = "?> ";
fwrite($fh, $start);
$stringData = "\$username = '" . $_POST['username'] . "';\n";
fwrite($fh, $stringData);
$stringData = "\$password = '" . $_POST['password'] . "';\n";
fwrite($fh, $stringData);
fwrite($fh, $end);
fclose($fh);

echo("Configuration written!");
}
?>

<body>
<h3>Twitter Configuration</h3>
<?php
if ( '' == file_get_contents("twitter_conf.txt" ) ) {
   echo("Here you can configure a Twitter account to be updated by the HvZ site.<br />");
   echo("Such updates include player registration, game state changes, and zombies kills.<br />");
   echo("If you would like to use this feature, but don't have a Twitter account, get one <a href=\"http://www.twitter.com/signup\" target=\"_blank\">here</a>.<br />");
}
else{
    echo("Here you can configure a Twitter account to be updated by the HvZ site.<br />");
    echo("Such updates include player registration, game state changes, and zombies kills.<br />");
    require("twitter_conf.txt");
    echo("Current Twitter account: <a href=\"http://www.twitter.com/" . $username . "\" target=\"_blank\">www.twitter.com/" . $username ."</a><br />");
}
?>
<br />
<form name="twitter" method=POST action="twitter_set.php">
Twitter username:
<input type="text" name="username">
<br />
Twitter password:
<input type="password" name="password">
<br />
<input type="submit" name="submit" value="Submit" onClick='return'>
</form>

</body>
</html>