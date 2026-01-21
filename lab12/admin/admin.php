<?php
include 'cfg.php';

class Admin {

    /*
     * FormularzLogowania
     * Tworzy formularz logowania do panelu admina
     * return $html - kod html formularza
     */

    function FormularzLogowania() {
        $html = '<div class="logowanie">
            <h2 class="head">Panel Administratora</h2>
            <form method="post" name="LoginForm" action="'.$_SERVER['REQUEST_URI'].'">
                <table class="logowanie">
                    <tr>
                        <td class="log4_t"><label for="login">Login</label></td>
                        <td><input type="text" id="login" name="login" class="logowanie" required /></td>
                    </tr>
                    <tr>
                        <td class="log4_t"><label for="login_pass">Hasło</label></td>
                        <td><input type="password" id="login_pass" name="login_pass" class="logowanie" required /></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td><input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" /></td>
                    </tr>
                </table>
            </form>
        </div>';
    
        return $html;
    }



    /*
     * ListaPodstron
     * Wyświetla listę stron z opcjami edycji, usunięcia i tworzenia nowych stron.
     * Pobiera dane z tabeli `page_list` w bazie danych i wyświetla je w formie tabeli.
     * Dla każdej strony istnieje możliwość edycji lub usunięcia.
     */
    function ListaPodstron() {
        global $conn;        
        $query = "SELECT id, page_title FROM page_list ORDER BY id ASC LIMIT 100" ; // Zwraca id i tytuł strony, sortuje po id ASC i ogranicza ilość wyników do 100
        $result = $conn->query($query);
        echo '<div class="podstrony">
            <h1 class="lista_stron">Panel Administratora</h1>
            <table class="stronki">
                <tr class="column_names">
                    <th>ID Strony</th>
                    <th>Tytuł Strony</th>
                    <th>Edytuj</th>
                    <th>Usuń</th>
                </tr>';
        while($row = $result->fetch_assoc()) {
            echo '<tr class="el_listy">
                <td>' . htmlspecialchars($row['id']) . '</td>
                <td>' . htmlspecialchars($row['page_title']) . '</td>
                <td><a class="edit-button" href="?idp=-2&ide=' . htmlspecialchars($row['id']) . '">Edytuj</a></td>
                <td><a class="delete-button" href="?idp=-3&idd=' . htmlspecialchars($row['id']) . '" onclick="return confirm(\'Czy na pewno chcesz usunąć tę stronę?\');">Usuń</a></td>
            </tr>';
        }
        echo '</table>
            <div class="action-links">
                <a class="create_page" href="?idp=-4">Dodaj Nową Stronę</a>
                <a class="categories" href="?idp=-8">Kategorie w sklepie</a>
                <a class="products" href="?idp=-12">Produkty w sklepie</a>
            </div>
        </div>';
    }
     


    /*
     * CheckLogin
     * Sprawdza, czy użytkownik jest zalogowany poprzez sesję lub dane logowania z formularza.
     * Użytkownik jest zalogowany, jeśli istnieje sesja 'loggedin' i ma wartość true.
     * Jeśli nie ma sesji, to sprawdza, czy dane logowania z formularza są poprawne.
     */
     function CheckLogin() {
        // Sprawdza, czy sesja 'loggedin' istnieje i jest ustawiona na true
        if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
            return 1; 
        }
        // Sprawdza, czy dane logowania zostały przesłane przez formularz
        if(isset($_POST['login']) && isset($_POST['login_pass'])){
            // Weryfikuje dane logowania i zwraca wynik
            return $this->CheckLoginCred($_POST['login'], $_POST['login_pass']);
        } else {
            // Zwraca 0, jeśli użytkownik nie jest zalogowany
            return 0; 
        }
    }



    /*
     * CheckLoginCred
     * Sprawdza, czy dane logowania z formularza są poprawne.
     * $login - login z formularza
     * $pass - hasło z formularza
     */

     function CheckLoginCred($login, $pass){
        if($login == admin && $pass == pass){
            $_SESSION['loggedin'] = true;
            return 1;
        } else {
            echo "Logowanie się nie powiodło.";
            return 0;
        }
    }


    /*
     * LoginAdmin
     * Wyświetla panel admina z listą stron, jeśli użytkownik jest zalogowany,
     * w przeciwnym razie wyświetla formularz logowania.
     */
    function LoginAdmin() {
        $status_login = $this->CheckLogin();
        if($status_login == 1) {
            return $this->ListaPodstron();
        } else {
            return $this->FormularzLogowania();
        }
    }



    /*
     * EditPage
     * Funkcja umożliwia edytowanie istniejącej strony w serwisie.
     * Sprawdza, czy użytkownik jest zalogowany, a następnie weryfikuje, 
     * czy ID strony do edycji zostało podane.
     * Jeśli formularz edycji został przesłany, aktualizuje dane strony w bazie danych.
     * W przeciwnym wypadku, wyświetla formularz edycji z aktualnymi danymi strony.
     * Jeśli użytkownik nie jest zalogowany, wyświetla formularz logowania.
     */
    function EditPage() {
        // Sprawdza, czy użytkownik jest zalogowany
        $status_login = $this->CheckLogin();
        if($status_login == 1){
            if(isset($_GET['ide'])){
                // Sprawdza, czy formularz edycji został przesłany
                if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'], $_POST['edit_alias'])) {
                    // Aktualizuje dane strony w bazie danych
                    $title = $GLOBALS['conn']->real_escape_string($_POST['edit_title']);
                    $content = $GLOBALS['conn']->real_escape_string($_POST['edit_content']);
                    $active = isset($_POST['edit_active']) ? 1 : 0;
                    $alias = $GLOBALS['conn']->real_escape_string($_POST['edit_alias']);
                    $id = intval($_GET['ide']);

                    // Tworzy zapytanie do bazy danych o aktualizację strony
                    $query = "UPDATE page_list SET page_title='$title', page_content='$content', status='$active', alias='$alias' WHERE id='$id' LIMIT 1";

                    // Wykonuje zapytanie i sprawdza, czy się powiodło
                    if($GLOBALS['conn']->query($query) === TRUE){
                        echo 'Strona zaktualizowana';
                        // Przekierowuje na panel admina
                        header("Location: ?idp=-1");
                        exit;
                    } else {
                        echo "Nie powiodlo się" . $GLOBALS['conn']->error;
                    }
                } else {
                    // Pobiera dane strony z bazy danych
                    $query = "SELECT * FROM page_list WHERE id=" . intval($_GET['ide']) . " LIMIT 1";
                    $result = $GLOBALS['conn']->query($query);

                    // Sprawdza, czy dane strony zostały pobrane
                    if($result && $result->num_rows > 0) {
                        // Pobiera dane strony z bazy danych
                        $row = $result->fetch_assoc();
                        // Wyświetla formularz edycji z aktualnymi danymi strony
                        return '
                            <div class="edit-container">
                                <h3 class="edit-title">Edycja Strony</h3>
                                <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                    <div class="form-group">
                                        <label for="edit_title">Tytuł:</label><br>
                                        <input type="text" id="edit_title" name="edit_title" value="' . htmlspecialchars($row['page_title']) . '" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_content">Zawartość:</label><br>
                                        <textarea id="edit_content" name="edit_content" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_active">Aktywna:</label><br>
                                        <input type="checkbox" id="edit_active" name="edit_active" ' . ($row['status'] ? 'checked' : '') . ' />
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_alias">Alias:</label><br>
                                        <input type="text" id="edit_alias" name="edit_alias" value="' . htmlspecialchars($row['alias']) . '" required />
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="submit-button" value="Zapisz zmiany" />
                                    </div>
                                </form>
                            </div>';
                    } else {
                        return '<div class="error-message">Nie znaleziono strony do edycji</div>';
                    }
                }
            } else {
                return '<div class="error-message">Nie podano ID strony do edycji</div>';
            }
        } else {
            // Wyświetla formularz logowania, jeśli nie jest zalogowany
            return $this->FormularzLogowania();
        }
    }



    /*
     * CreatePage
     * Wyświetla formularz dodawania nowej strony, jeśli użytkownik jest zalogowany.
     * Jeśli formularz został wysłany, dodaje nową stronę do bazy danych i przekierowuje na panel admina.
     * Jeśli nie jest zalogowany, wyświetla formularz logowania.
     */
    function CreatePage(){
        // Sprawdza, czy użytkownik jest zalogowany
        if($this->CheckLogin() == 1){
            if(isset($_POST['create_title'], $_POST['create_content'], $_POST['create_alias'])){
                // Konwertuje dane z formularza do postaci bezpiecznej dla bazy danych
                $title = $GLOBALS['conn']->real_escape_string($_POST['create_title']);
                $content = $GLOBALS['conn']->real_escape_string($_POST['create_content']);
                $active = isset($_POST['create_active']) ? 1 : 0;
                $alias = $GLOBALS['conn']->real_escape_string($_POST['create_alias']);
                // Wykonuje zapytanie do bazy danych o dodanie nowej strony
                if($GLOBALS['conn']->query("INSERT INTO page_list (page_title, page_content, status, alias) VALUES ('$title', '$content', '$active','$alias')") === TRUE){
                    // Przekierowuje na panel admina
                    header("Location: ?idp=-1");
                    exit;
                }
            }
            // Wyświetla formularz dodawania nowej strony
            return '
            <div class="edit-container">
                <h3 class="edit-title">Dodaj nową stronę</h3>
                <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                    <div class="form-group">
                        <label for="create_title">Tytuł:</label><br>
                        <input type="text" id="create_title" name="create_title" required />
                    </div>
                    <div class="form-group">
                        <label for="create_content">Zawartość:</label><br>
                        <textarea id="create_content" name="create_content" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="create_active">Aktywna:</label><br>
                        <input type="checkbox" id="create_active" name="create_active" />
                    </div>
                    <div class="form-group">
                        <label for="create_alias">Alias:</label><br>
                        <input type="text" id="create_alias" name="create_alias" required />
                    </div>
                    <div class="form-group">
                        <input type="submit" class="submit-button" value="Dodaj stronę" />
                    </div>
                </form>
            </div>';
        } else {
            // Wyświetla formularz logowania, jeśli nie jest zalogowany
            return $this->FormularzLogowania(); 
        }
    }





    /*
     * DeletePage
     * Funkcja usuwa stronę z bazy danych na podstawie podanego ID.
     * Jeśli użytkownik nie jest zalogowany, wyświetla formularz logowania.
     * Po usunięciu strony przekierowuje na stronę główną.
     */
    function DeletePage() {
        // Sprawdza, czy użytkownik jest zalogowany
        $status_login = $this->CheckLogin(); 
    
        if ($status_login == 1) { 

            if (isset($_GET['idd'])) {
                $id = intval($_GET['idd']); 
                // Tworzy zapytanie do bazy danych o usunięcie strony
                $query = "DELETE FROM page_list WHERE id='$id' LIMIT 1";

                // Wykonuje zapytanie i sprawdza, czy się powiodło
                if ($GLOBALS['conn']->query($query) === TRUE) {
                    echo "Strona została usunięta pomyślnie.";
                    // Przekierowuje na panel admina
                    header("Location: ?idp=-1"); 
                    exit;
                } else {
                    echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error;
                }
            } else {
                echo "Nie podano ID strony do usunięcia.";
            }
        } else {
            // Wyświetla formularz logowania, jeśli nie jest zalogowany
            return $this->FormularzLogowania(); 
        }
    }







    /*
     * Wyloguj
     * Funkcja wylogowuje zalogowanego użytkownika, usuwając zmienną sesyjną 'loggedin'.
     * Po wylogowaniu przekierowuje na stronę główną.
     */
    function Wyloguj() {
        if(isset($_SESSION['loggedin'])) {
            unset($_SESSION['loggedin']);
        }
        header('Location: ?idp=1'); // przekierowanie na stronę głowną
        exit;
    }

}

?>
