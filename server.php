<?php

$servername = "localhost";

$username = "dasgroup_das_db_user";

$password = "Das@123#";

$dbname = "dasgroup_das_db";

// Create connection

$conn = new mysqli($servername, $username, $password, $dbname);



// Check connection

if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

}else{

    //echo "Connected successfully";

}
// Log file location
define('SQL_LOG_FILE', __DIR__ . '/sql_queries.log');

// Function to log SQL
function logSQL($query) {
    $logLine = "[" . date("Y-m-d H:i:s") . "] " . $query . PHP_EOL;
    file_put_contents(SQL_LOG_FILE, $logLine, FILE_APPEND);
}

// Wrapper function for executing SQL
function runQuery($conn, $sql) {
    logSQL($sql); // Log the query
    return $conn->query($sql);
}




?>