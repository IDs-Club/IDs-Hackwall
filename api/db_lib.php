
<?php
// db_lib.php

class db
{
  public $dbh;

  // Create a database connection for use by all functions in this class
  function __construct() {

    require_once('db_config.php');
    
    if($this->dbh = mysqli_connect($db_host, 
      $db_user, $db_password, $db_name)) { 
	} else {
	  exit('Unable to connect to DB');
    }
	// Set every possible option to utf-8
     mysqli_query($this->dbh, 'SET NAMES "utf8"');
     mysqli_query($this->dbh, 'SET CHARACTER SET "utf8"');
     mysqli_query($this->dbh, 'SET character_set_results = "utf8",' .
        'character_set_client = "utf8", character_set_connection = "utf8_general_ci",' .
        'character_set_database = "utf8", character_set_server = "utf8"');
  }
  
  // Create a standard data format for insertion of PHP dates into MySQL
  public function date($php_date) {
    return date('Y-m-d H:i:s', strtotime($php_date));	
  }
  
  // All text added to the DB should be cleaned with mysqli_real_escape_string
  // to block attempted SQL insertion exploits
  public function escape($str) {
    return mysqli_real_escape_string($this->dbh,$str);
  }
    
  // Test to see if a specific field value is already in the DB
  // Return false if no, true if yes
  public function in_table($table,$where) {
    $query = 'SELECT * FROM ' . $table . 
      ' WHERE ' . $where;
    $result = mysqli_query($this->dbh,$query);
    return mysqli_num_rows($result) > 0;
  }

  // Perform a generic select and return a pointer to the result
  public function select($query) {
    $result = mysqli_query( $this->dbh, $query );
    return $result;
  }
  
  public function delete($query) {
	 //echo '11111111111111' . $query;
      mysqli_query($this->dbh,$query);
  }
    
  // Add a row to any table
  public function insert($table,$fields,$values) {
    $query = 'INSERT INTO ' . $table . ' ( ' . $fields . ') VALUES (' . $values . ')';
    //echo $query;
    mysqli_query($this->dbh,$query);
  }
  
  // Update any row that matches a WHERE clause
  public function update($table,$where,$field_values) {
    $query = 'UPDATE ' . $table . ' SET ' . $field_values . 
      ' WHERE ' . $where;
	//echo $query;
    mysqli_query($this->dbh,$query);
  } 
 
}  
?>