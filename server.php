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





?>