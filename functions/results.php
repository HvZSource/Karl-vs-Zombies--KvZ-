<?php
if (!function_exists('mysql_fetch_results')) {

   function mysql_fetch_results($resource, $results = Array()) {

      while($row = @mysql_fetch_assoc($resource)) { $results[] = $row; }

      return $results;

   }

}

?>

