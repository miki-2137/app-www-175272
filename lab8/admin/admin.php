<?php
class Admin{
    function FormularzLogowania() {
        $wynik = '
            <div class="logowanie">
                <h2 class="head">Zaloguj do panelu admina:</h2>
                    <table>
                    <form method="post" name="LoginForm" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].'">
                        <tr><td class="log4_t">Login</td><td><input type="text" name="login" class="logowanie"></td></tr>
                        <tr><td class="log4_t">Hasło</td><td><input type="password" name="login_pass" class="logowanie"></td></tr>
                        <tr><td></td><td><input type="submit" name="x1_submit" class="logowanie" value="zaloguj"></td></tr>
                    </form>
                    <tr><td><a href="index.php?idp=-7"><button>Odzyskaj hasło</button></a></td></tr>
                    </table>
                    
            </div>
            ';
    return $wynik;
    }

    function ListaPodstron() {
        global $conn;
        $query = "SELECT id, page_title FROM page_list ORDER BY id ASC LIMIT 100" ;
        $result = $conn->query($query);

        echo '<div class="podstrony">
        <h2 class="lista_podstron">Lista podstron</h2>
        <table class="stronki">
            <tr class="column_names">
                <th>ID Strony</th>
                <th>Tytuł Strony</th>
                <th>Edytuj</th>
                <th>Usuń</th>
            </tr>';
        while($row = $result->fetch_assoc()) {
            echo '<tr class="el_listy">
                <td>' . $row['id'] . '</td>
                <td>' . $row['page_title'] . '</td>
                <td><a href="?idp=-2&ide=' . $row['id'] . '"><button class="button">Edit</button></a></td>
                <td><a href="?idp=-3&idd=' . $row['id'] . '" onclick="return confirm(\'Czy jesteś pewien?\');"><button class="button">Delete</button></a></td>
            </tr>';
        }
        echo '<tr><td colspan="4"><a class="create_page" href="?idp=-4">Stwórz nową stronę</a></td></tr>';
        echo '<tr><td colspan="4"><button><a href="index.php?idp=-6">Wyloguj</button></a></td></tr>';
        echo '</table></div>';
    }

    function CheckLogin() {
        if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
            return 1;
        }

        if(isset($_POST['login']) && isset($_POST['login_pass'])){
            return $this->CheckLoginCred($_POST['login'], $_POST['login_pass']);
        }
        else {
            return 0;
        }
    }

    function CheckLoginCred($login, $pass){
        if($login == admin && $pass == pass){
            $_SESSION['loggedin'] = true;
            return 1;
        } else {
            echo "Logowanie się nie powiodło.";
            return 0;
        }
    }

    function LoginAdmin() {
        $status_login = $this->CheckLogin();

        if($status_login == 1){
            echo $this->ListaPodstron();
        } else {
            echo $this->FormularzLogowania();
        }
    }

    function EditPage() {
        $status_login = $this->CheckLogin();
        if($status_login == 1){
			if(isset($_GET['ide'])){
                
                if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'], $_POST['edit_active'], $_POST['edit_alias'])) {
                    $title = $GLOBALS['conn']->real_escape_string($_POST['edit_title']);
                    $content = $GLOBALS['conn']->real_escape_string($_POST['edit_content']);
                    $active = isset($_POST['edit_active']) ? 1 : 0;
                    $alias = $GLOBALS['conn']->real_escape_string($_POST['edit_alias']);
                    $id = intval($_GET['ide']);

                    $query = "UPDATE page_list SET page_title='$title', page_content='$content', status='$active', alias='$alias' WHERE id='$id' LIMIT 1";

                    if($GLOBALS['conn']->query($query) === TRUE){
                        echo 'Strona zaktualizowana';
                        header("Location: ?idp=-1");
                        exit;
                    } else {
                        echo "Nie" . $GLOBALS['conn']->error;
                    }
                } else {
                    $query = "SELECT * FROM page_list WHERE id='" . intval($_GET['ide']) . "' LIMIT 1";
                    $result = $GLOBALS['conn']->query($query);

                    if($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        return '<h3 class="edit-title">Edytuj stronę</h3>
                                <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                    <div class="form-group">
                                        <label for="edit_title">Tytuł:</label>
                                        <input type="text" id="edit_title" name="edit_title" value="' . htmlspecialchars($row['page_title']) . '" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_content">Zawartość:</label>
                                            <textarea id="edit_content" name="edit_content" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_active">Aktywna:</label>
                                            <input type="checkbox" id="edit_active" name="edit_active"' . ($row['status'] ? ' checked' : '') . ' />
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_alias">Alias:</label>
                                            <input type="text" id="edit_alias" name="edit_alias" value="' . htmlspecialchars($row['alias']) . '" required />
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="button" value="Zapisz zmiany">Zapisz zmiany</button>
                                        </div>
                                    </form>';
                            
                    } else {
                        return "nie ma strony edycja";
                    }
                }
            } else {
                return "Nie znaleziono id";
            }
        } else {
            return $this->FormularzLogowania();
        }
    }
    function CreatePage(){
        $status_login = $this->CheckLogin();
        if($status_login == 1){
            echo '<h3 class="create_page"> Nowa strona </h3>';
			
                if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_title'], $_POST['create_content'], $_POST['create_active'], $_POST['create_alias'])) {
                    $title = $GLOBALS['conn']->real_escape_string($_POST['create_title']);
                    $content = $GLOBALS['conn']->real_escape_string($_POST['create_content']);
                    $active = isset($_POST['create_active']) ? 1 : 0;
                    $alias = $GLOBALS['conn']->real_escape_string($_POST['create_alias']);

                    $query = "INSERT INTO page_list (page_title, page_content, status, alias) VALUES ('$title', '$content', '$active','$alias')";

                    if($GLOBALS['conn']->query($query) === TRUE){
                        echo 'Strona Stworzona';
                        header("Location: ?idp=-1");
                        exit;
                    } else {
                        echo "Nie" . $GLOBALS['conn']->error;
                    }
                } else {
                            return '
                    <div class="create-container">
                    <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                        <div class="form-group">
                            <label for="create_title">Tytuł:</label>
                            <input type="text" id="create_title" name="create_title" required />
                        </div>
                        <div class="form-group">
                            <label for="create_content">Zawartość:</label>
                            <textarea id="create_content" name="create_content" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="create_active">Aktywna:</label>
                            <input type="checkbox" id="create_active" name="create_active" />
                        </div>
                        <div class="form-group">
                            <label for="create_alias">Alias:</label>
                            <input type="text" id="create_alias" name="create_alias" required />
                        </div>
                        <div class="form-group">
                            <button type="submit" class="button" value="Dodaj stronę">Dodaj stronę</button>
                        </div>
                    </form>
                </div>';
                } 
            } else {
                return $this->FormularzLogowania();
            } 
        }

    function DeletePage() {
        $status_login = $this->CheckLogin(); 
    
        if ($status_login == 1) { 

            if (isset($_GET['idd'])) {
                $id = intval($_GET['idd']); 
    
                $query = "DELETE FROM page_list WHERE id='$id' LIMIT 1";

                if ($GLOBALS['conn']->query($query) === TRUE) {
                    echo "Strona została usunięta pomyślnie.";
                    header("Location: ?idp=-1"); 
                    exit;
                } else {
                    echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error;
                }
            } else {
                echo "Nie podano ID strony do usunięcia.";
            }
        } else {
            return $this->FormularzLogowania(); 
        }
    }

    function Wyloguj() {
        if(isset($_SESSION['loggedin'])) {
            unset($_SESSION['loggedin']);
        }
        header('Location: ?idp=-1');
        exit;
    }
}
?>