<?php



require_once('functions/load_config.php');

require_once('functions/quick_con.php');

$config = load_config('settings/config.dat'); 

$sql = my_quick_con($config) or die("SQL problem"); 

$table_u = $config['user_table'];

$table_v = $config['var_table'];



$ret = mysql_query("SELECT value FROM $table_v WHERE zkey='oz-revealed';");

// this is an array fetching bug 

// $reveal_oz = mysql_fetch_assoc($ret);

// $reveal_oz is clearly expecting a scalar return value and not an array

// therefore, you have to look at the query and figure out which single field 

// it was trying to extract (value in this case, based on the prior SELECT).

$reveal_oz = mysql_fetch_assoc($ret);

$reveal_oz = $reveal_oz['value'];



$pid = $_SESSION['id']; 

if($reveal_oz) $ret = mysql_query("SELECT id, fname, lname, timediff(feed + INTERVAL 2 day, now()) FROM $table_u WHERE state < 0 AND id != '$pid' ORDER BY feed ASC;");

else $ret = mysql_query("SELECT id, fname, lname, timediff(feed + INTERVAL 2 day, now()) FROM $table_u WHERE state < 0 AND state != -2 AND id != '$pid' ORDER BY feed ASC;");



// give JSON output - "ID":"Display"

echo('{');

for($i = 0; $i < mysql_num_rows($ret); $i++)

{

	$row = mysql_fetch_row($ret); 

	echo(sprintf('{"%s":"%s %s (%s)"}', $row[0], $row[1], $row[2], $row[3])); 

	if($i < mysql_num_rows($ret) - 1)

		echo(',');

}

echo('}')



?>
