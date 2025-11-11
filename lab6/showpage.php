<?php
include('cfg.php');

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

if (isset($_GET['idp'])) {
    $id = $_GET['idp'];
    echo PokazStrone($id);
} else {
    echo '[nie_znaleziono_strony]';
}


?>