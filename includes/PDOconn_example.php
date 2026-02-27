<?php
$dbname = 'databasename';
$dbuser = 'username';
$dbpass = 'urpassword';
$dbhost = 'localhost';

try{
    $pdo = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpass);
}catch(PDOException $err){
    echo "Database connection problem: " . $err->getMessage();
    exit();
}
?>