<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';

$login = 'admin';
$pass = 'haslo';

// Definiujemy stałe dla danych logowania do panelu admina,
// jeśli one nie są zdefiniowane, to je definiujemy
if(!defined('admin')) {
    define('admin', $login);
}
if(!defined('pass')){
    define('pass', $pass);
}

//Tworzy nowe polaczenie i sprawdza poczenie z baz  danych
//Jeśli połaczenie się  nie powiedzie, to wyświetlamy komunikat o błędzie
$conn = new mysqli($dbhost, $dbuser, $dbpass, $baza);
if($conn->connect_error) {
    die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error);
}
?>