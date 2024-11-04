<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'clintsys';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('sorry for this pls visit it in just a min'. $conn->connect_error);

}
// else{

//     echo "all good";
// }
    
