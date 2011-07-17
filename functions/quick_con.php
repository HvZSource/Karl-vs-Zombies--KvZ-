<?php
function my_quick_con($config){
        $u = $config['mysql_user'];
        $p = $config['mysql_pass'];
        $d = $config['mysql_db'];
        $h = $config['mysql_db_host'];
        $con = mysql_connect($h, $u, $p);
	mysql_select_db($d, $con);
        return $con;
}
?>

