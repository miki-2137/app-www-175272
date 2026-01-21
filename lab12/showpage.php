<?php
include('cfg.php');

/*
 * Funkcja PokazStrone
 * 
 * Pobiera i wyświetla zawartość strony o podanym ID z bazy danych.
 * 
 * int $id ID strony do wyświetlenia
 * zwraca string Zawartość strony lub komunikat o błędzie
 */
function PokazStrone($id) {
    global $conn;
    $id_clear = htmlspecialchars($id);

    $query = "SELECT * FROM page_list WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_clear);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    return empty($row['id']) ? '[nie_znaleziono_strony]' : $row['page_content'];
}

// Sprawdza, czy parametr 'idp' jest ustawiony w URL
if (isset($_GET['idp'])) {
    // Tutaj można dodać kod do obsługi parametru 'idp'
} else {
    echo '[nie_znaleziono_strony]';
}

?>