<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("MySQL problem");
$result = mysql_query("SELECT value AS rules FROM $config[content_table] WHERE keyword = 'rules'");
$row = mysql_fetch_assoc($result);
?>

<html>
<head><title>Edit Rules</title>
<script type="text/javascript" src="/fckeditor/fckeditor.js"></script>
<script type="text/javascript">
window.onload = function()
{
var oFCKeditor = new FCKeditor('rules' ) ;
oFCKeditor.BasePath = "/fckeditor/" ;
oFCKeditor.ReplaceTextarea() ;
}
</script>
</head>
<body bgcolor="#000000">

<p align="center"><h2><font color="#FFFFFF">Edit Rule Set</font></h2></p>


<form action="insert.php" method="post">
<p>
	<textarea id="rules" name="rules" style="width:700px;height:500px;">
		<?php print $row['rules']; ?>
	</textarea>
</p>
<p><input name="save" type="submit" value="save"></p>
</form>
<?php
mysql_close($sql);
op_end_flush();
?>
</body>
</html>
