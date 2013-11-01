<?php
function load_config($config_file_name) {
	$dot_pos = strpos($_SERVER["HTTP_HOST"], ".");
	if($dot_pos !== false) {
        $game_name = substr($_SERVER["HTTP_HOST"], 0, strpos($_SERVER["HTTP_HOST"], "."));
	} else { 
        $game_name = $_SERVER["HTTP_HOST"];
    }
    
	require_once($config_file_name);
	
	return $config;
}

function game_summary() {

    global $table_u, $table_v;

    $ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-started';"); 
    $game_started = mysql_result($ret, 0);
    $ret = mysql_query("SELECT value FROM $table_v WHERE keyword='reg-open';"); 
    $reg_open = mysql_result($ret, 0);

    $game_sum_array = array();
    $game_summary = '';
    if($reg_open) {
        if($game_started) {
            // GET Game Start Time
            $query = "SELECT unix_timestamp(killed) killed FROM $table_u WHERE killed != '0000-00-00 00:00:00' ORDER BY killed ASC LIMIT 1;";
            $result = mysql_query($query) or die(mysql_error());
            if(mysql_num_rows($result) > 0) {
                $row = mysql_fetch_assoc($result);
                $game_sum_array[$row["killed"]] = '<b>Game started at ' . date('g:i a', $row['killed']) . ' on ' . date('M jS, Y', $row['killed']) . '</b>';
            }
            // GET Starves
            $query = "SELECT CONCAT(fname, ' ', lname) player, unix_timestamp(starved) starved, id AS starved_id FROM $table_u WHERE state = 0 AND active = 1 ORDER BY starved ASC;";
            $result = mysql_query($query) or die(mysql_error());
            if(mysql_num_rows($result) > 0) {
                while($row = mysql_fetch_assoc($result)) {
                    $game_sum_array[$row["starved"] . $row["starved_id"]] = '<b>' . $row['player'] . '</b> <span style="color:darkred">starved</span> at ' . date('g:i a', $row['starved']) . ' on ' . date('M jS', $row['starved']);
                }
            }
            // GET Tags
            $query = "SELECT (SELECT (SELECT CONCAT(k.fname, ' ', k.lname) FROM $table_u k WHERE k.id = u.killed_by)) as tagger, CONCAT(u.fname, ' ', u.lname) as tagged, UNIX_TIMESTAMP(u.killed) killed, u.id AS tagged_id FROM $table_u u WHERE active AND state IN (-1, 0) AND u.killed_by IS NOT NULL AND u.killed_by !='' ORDER BY u.killed;";
            $result = mysql_query($query) or die(mysql_error());
            if(mysql_num_rows($result) > 0) {
                while($row = mysql_fetch_assoc($result)) {
                    $game_sum_array[$row["killed"] . $row["tagged_id"]] = '<b>' . $row['tagger'] . '</b><span style="color:darkgreen"> tagged </span><b>' . $row['tagged'] . '</b> at ' . date('g:i a', $row['killed']) . ' on ' . date('M jS', $row['killed']);
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

    return $game_summary;

}













?>
