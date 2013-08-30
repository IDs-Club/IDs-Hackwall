<?php
// db_config.php
$services_json = json_decode(getenv('VCAP_SERVICES'),true);
$mysql_config = $services_json['mysql-5.1'][0]['credentials'];

$db_host = $mysql_config['hostname'];
$db_user = $mysql_config['user'];
$db_password = $mysql_config['password'];
$db_name = $mysql_config['name'];
 
?> 