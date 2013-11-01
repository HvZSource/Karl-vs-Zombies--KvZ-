<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/functions.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.php');
$sql = my_quick_con($config) or die("MySQL problem");
$result = mysql_query("SELECT value AS front FROM $config[content_table] WHERE keyword = 'front'");
$row = mysql_fetch_assoc($result);
?>

<html>
<head><title>Edit Front Page</title>
<script type="text/javascript" src="/fckeditor/fckeditor.js"></script>
<script type="text/javascript">
window.onload = function()
{
var oFCKeditor = new FCKeditor( 'front' ) ;
oFCKeditor.BasePath = "/fckeditor/" ;
oFCKeditor.ReplaceTextarea() ;
}
</script>
</head><body bgcolor="#000">
<p align="center"><h2><font color="#FFFFFF">Edit Front Page</font></h2></p>

<form action="insertfront.php" method="post">
<p>
	<textarea id="front" name="front" style="width:700px;height:500px;">
		<?php print $row['front']; ?>
	</textarea>
</p>
<p><input name="save" type="submit" value="save"></p>
</form>
<?php
mysql_close($sql);
?>
</body>
</html>
