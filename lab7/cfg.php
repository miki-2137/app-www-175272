<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';

$login = 'admin';
$pass = 'haslo';

if(!defined('admin')) {
    define('admin', $login);
}
if(!defined('pass')){
    define('pass', $pass);
}

$conn = new mysqli($dbhost, $dbuser, $dbpass, $baza);

if($conn->connect_error) {
    die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error);
}
?>