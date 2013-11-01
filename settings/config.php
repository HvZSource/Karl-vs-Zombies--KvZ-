<?

# Back end operation information 

$config = array(
# Game Name / Subdomain 
    "game_name" => $game_name,

# mySQL Settings 
    "mysql_user" => "DBUser ",
    "mysql_pass" => "DBPassword",
    "mysql_db" => "DBname",
    "mysql_db_host" => "localhost",

# Table names 
    "user_table" => "{$game_name}_users",
    "admin_table" => "{$game_name}_admin",
    "var_table" => "{$game_name}_variables",
    "time_table" => "{$game_name}_timezone",
    "content_table" => "{$game_name}_content",
# Web page location settings 
    "login_goto" => "hub.php",
    "logout_goto" => "index.php",
    "admin_login_goto" => "index.php",

# Setup information
#
# You can change id_length to a more suitable number if 
# your game is appropriately large or small. However, 
# and this is just my personal opinion, if you change it 
# DURING registration, someone should beat you bloody.  
    "reg_admin" => "ChangemePlease",
    "id_length" => "6",

# Pagination records
    "page_records" => "2",
);

?>