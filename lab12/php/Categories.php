<?php
class Categories {

    /*
     * PokazKategorie - Wyświetla panel kategorii dla zalogowanych użytkowników
     * 
     * Funkcja sprawdza status logowania użytkownika. Jeśli użytkownik jest zalogowany,
     * wyświetla nagłówek panelu kategorii i wywołuje metodę ListaKategorii.
     * W przeciwnym razie wyświetla formularz logowania.
     */
    function PokazKategorie() {
        // Tworzy nowy obiekt klasy Admin i sprawdza status logowania
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        
        // Jeśli użytkownik jest zalogowany, wyświetla panel kategorii
        if ($status_login == 1) {
            // Wyświetla nagłówek panelu kategorii
            echo '<h3 class="h3-admin">Panel Kategorii</h3>';
            echo '<div class="admin-links">';
            echo '</div>';
            
            // Wywołuje metodę ListaKategorii, która wyświetla listę kategorii
            echo $this->ListaKategorii();
        } else {
            // Jeśli użytkownik nie jest zalogowany, wyświetla formularz logowania
            echo $Admin->FormularzLogowania();
        }
    }
	

    /*
     * ListaKategorii() - Generuje i wyświetla hierarchiczną listę kategorii
     * 
     * Funkcja pobiera kategorie z bazy danych, organizuje je w strukturę drzewiastą,
     * i wyświetla je w formie interaktywnej listy z opcjami edycji i usuwania.
     * Zawiera również obsługę komunikatów o statusie operacji na kategoriach.
     */
    function ListaKategorii() {
        global $conn;

        // Wyświetla komunikaty statusu operacji na kategoriach
        if (isset($_GET['deleted'])) {
            echo '<div class="success-message">Kategoria została pomyślnie usunięta.</div>';
        } elseif (isset($_GET['error'])) {
            echo '<div class="error-message">Wystąpił błąd podczas usuwania kategorii.</div>';
        }

        // Pobiera kategorie z bazy danych
        $query = "SELECT id, matka, nazwa FROM categories ORDER BY matka, id ASC LIMIT 100";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = array();
        $category_tree = array();

        // Buduje strukturę drzewiastą kategorii
        while($row = $result->fetch_assoc()) {
            $categories[$row['id']] = $row;
            if (!isset($category_tree[$row['matka']])) {
                $category_tree[$row['matka']] = array();
            }
            $category_tree[$row['matka']][] = $row['id'];
        }
        
        // Wyświetla drzewo kategorii
        echo '<div class="tree-container">';
        $this->printCategoryTreeRecursive($categories, $category_tree, 0, 0);
        echo '</div>';

        // Dodaje przyciski akcji
        echo '<div class="category-actions">';
        echo '<a href="?idp=-1" class="return-btn">Powrót do Panelu Admina</a>';
        echo '<a href="?idp=-9" class="category-btn">Dodaj nową kategorię</a>';
        echo '</div>';
        
        // Dodaje skrypt JavaScript do potwierdzania usunięcia kategorii
        echo '<script>
        function confirmDelete(categoryId) {
            if (confirm("Czy na pewno chcesz usunąć tę kategorię?")) {
                window.location.href = "?idp=-11&category_id=" + categoryId;
            }
        }
        </script>';
    }

    /*
     * printCategoryTreeRecursive() - Rekurencyjna metoda do wyświetlania drzewa kategorii
     * Renderuje hierarchiczną strukturę kategorii, wyświetlając kolejne poziomy zagnieżdżenia
     * z zachowaniem relacji rodzic-dziecko. Używa wcięć do pokazania struktury drzewiastej.
     */
    function printCategoryTreeRecursive($categories, $category_tree, $parent_id, $depth) {
        // Sprawdza, czy istnieją podkategorie dla danego rodzica
        // Jest to warunek zakończenia rekurencji
        if (!isset($category_tree[$parent_id]) || empty($category_tree[$parent_id])) {
            return;
        }

        // Iteruje przez wszystkie podkategorie bieżącego rodzica
        // Każda podkategoria jest wyświetlana i może mieć własne podkategorie
        foreach ($category_tree[$parent_id] as $category_id) {
            $category = $categories[$category_id];
            
            // Tworzy strukturę HTML dla każdej kategorii
            // Używa wcięć (padding) do wizualizacji hierarchii
            echo '<div class="category-item">';
            echo '<div class="category-content" style="padding-left: ' . (($depth * 20) + 10) . 'px;">';
            echo '<div class="category-name">';
            echo htmlspecialchars($category['nazwa']);
            
            // Dodaje przyciski do edycji i usuwania kategorii
            // Każda kategoria ma własne przyciski akcji
            echo '<div class="category-buttons">';
            echo '<a href="?idp=-10&category_id=' . $category_id . '" class="edit-btn">Edytuj</a>';
            echo '<a href="#" onclick="confirmDelete(' . $category_id . ')" class="delete-btn">Usuń</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            // Rekurencyjnie wyświetla podkategorie bieżącej kategorii
            // Zwiększa głębokość o 1 dla każdego poziomu zagnieżdżenia
            $this->printCategoryTreeRecursive($categories, $category_tree, $category_id, $depth + 1);
        }
    }



    /*
     * DodajKategorie() - Dodaje nową kategorię do systemu
     * 
     * Funkcja obsługuje formularz dodawania nowej kategorii, weryfikuje dane wejściowe,
     * sprawdza uprawnienia użytkownika i dokonuje zapisu do bazy danych.
     * Wyświetla formularz dodawania kategorii lub komunikat o wyniku operacji
     */
    function DodajKategorie() {
        // Tworzenie obiektu Admin i sprawdzanie statusu logowania
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        
        // Wyświetlenie formularza logowania, jeśli użytkownik nie jest zalogowany
        if ($status_login != 1) {
            echo $Admin->FormularzLogowania();
            return;
        }

        // Pobranie listy kategorii do wyboru rodzica
        global $conn;
        $query = "SELECT id, nazwa FROM categories ORDER BY nazwa ASC";
        $result = $conn->query($query);
        $categories = array();
        $categories[0] = "Brak (kategoria główna)";
        while($row = $result->fetch_assoc()) {
            $categories[$row['id']] = $row['nazwa'];
        }

        // Początek kontenera formularza
        echo '<div class="add-category-container">';
        echo '<h3 class="section-title">Dodaj nową kategorię</h3>';
        echo '<form action="" method="POST" class="add-category-form">';
        
        // Pole do wprowadzenia nazwy kategorii
        echo '<div class="form-group">';
        echo '<label for="category_name">Nazwa kategorii:</label>';
        echo '<input type="text" id="category_name" name="category_name" required>';
        echo '</div>';
        
        // Pole wyboru kategorii nadrzędnej
        echo '<div class="form-group">';
        echo '<label for="parent_category">Kategoria nadrzędna:</label>';
        echo '<select id="parent_category" name="parent_category">';
        foreach ($categories as $id => $name) {
            echo "<option value='$id'>$name</option>";
        }
        echo '</select>';
        echo '</div>';
        
        // Przycisk submit
        echo '<div class="form-group">';
        echo '<input type="submit" name="submit_category" value="Dodaj kategorię" class="submit-btn">';
        echo '</div>';
        
        echo '</form>';
        
        // Obsługa dodawania kategorii po wysłaniu formularza
        if (isset($_POST['submit_category'])) {
            $name = $conn->real_escape_string($_POST['category_name']);
            $parent = intval($_POST['parent_category']);
            
            // Wykonanie zapytania INSERT
            $insert_query = "INSERT INTO categories (nazwa, matka) VALUES ('$name', $parent)";
            if ($conn->query($insert_query)) {
                echo '<div class="success-message">Kategoria została dodana pomyślnie!</div>';
            } else {
                echo '<div class="error-message">Błąd podczas dodawania kategorii: ' . $conn->error . '</div>';
            }
        }
        
        echo '</div>';
        
        // Przycisk powrotu do listy kategorii
        echo '<div class="category-navigation">';
        echo '<a href="?idp=-8" class="return-btn">Powrót do kategorii</a>';
        echo '</div>';
        
        echo '</div>';
    }


    /*
     * EdytujKategorie() - Umożliwia edycję istniejącej kategorii
     * 
     * Funkcja pozwala na modyfikację nazwy i kategorii nadrzędnej dla wybranej kategorii.
     * Weryfikuje poprawność danych i zapobiega tworzeniu cyklicznych zależności.
     * 
     * Wyświetla formularz edycji kategorii lub komunikat o wyniku operacji
     */
    function EdytujKategorie() {
        // Inicjalizacja obiektu administratora i sprawdzenie uprawnień
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        
        // Przekierowanie niezalogowanego użytkownika do formularza logowania
        if ($status_login != 1) {
            echo $Admin->FormularzLogowania();
            return;
        }

        global $conn;
        
        // Pobranie ID kategorii z parametru URL
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        
        // Sprawdzenie czy kategoria istnieje
        $query = "SELECT * FROM categories WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        
        if (!$category) {
            echo '<div class="error-message">Kategoria nie została znaleziona.</div>';
            echo '<div class="category-navigation">';
            echo '<a href="?idp=-8" class="return-btn">Powrót do kategorii</a>';
            echo '</div>';
            return;
        }

        // Obsługa formularza edycji kategorii
        if (isset($_POST['submit_edit_category'])) {
            $category_id = intval($_POST['category_id']);
            $new_name = $conn->real_escape_string($_POST['category_name']);
            $new_parent = intval($_POST['parent_category']);
            
            // Zapobieganie tworzeniu cyklicznych zależności
            if ($category_id == $new_parent) {
                echo '<div class="error-message">Kategoria nie może być swoim własnym rodzicem.</div>';
                return;
            }
            
            // Przygotowanie zapytania SQL do aktualizacji kategorii
            $update_query = "UPDATE categories SET nazwa = ?, matka = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sii", $new_name, $new_parent, $category_id);
            
            // Wykonanie zapytania i obsługa rezultatu
            if ($stmt->execute()) {
                // Wyświetlenie komunikatu o sukcesie i przekierowanie
                header("Location: ?idp=-8&updated=1");
                exit();
            } else {
                // Wyświetlenie komunikatu o błędzie w przypadku niepowodzenia
                echo '<div class="error-message">Błąd podczas aktualizacji kategorii: ' . $conn->error . '</div>';
            }
        }

        // Pobranie wszystkich kategorii do listy rozwijanej
        $categories_query = "SELECT id, nazwa FROM categories WHERE id != ? ORDER BY nazwa";
        $stmt = $conn->prepare($categories_query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $categories_result = $stmt->get_result();
        $categories = array();
        while ($row = $categories_result->fetch_assoc()) {
            $categories[$row['id']] = $row['nazwa'];
        }

        // Generowanie formularza edycji
        echo '<div class="edit-category-container">';
        echo '<h3 class="section-title">Edytuj kategorię</h3>';
        
        echo '<form action="" method="POST" class="edit-category-form">';
        echo '<input type="hidden" name="category_id" value="' . $category_id . '">';
        
        echo '<div class="form-group">';
        echo '<label for="category_name">Nazwa kategorii:</label>';
        echo '<input type="text" id="category_name" name="category_name" value="' . htmlspecialchars($category['nazwa']) . '" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="parent_category">Kategoria nadrzędna:</label>';
        echo '<select id="parent_category" name="parent_category">';
        echo '<option value="0">Brak kategorii nadrzędnej</option>';
        foreach ($categories as $id => $name) {
            $selected = ($id == $category['matka']) ? ' selected' : '';
            echo '<option value="' . $id . '"' . $selected . '>' . htmlspecialchars($name) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<input type="submit" name="submit_edit_category" value="Zapisz zmiany" class="submit-btn">';
        echo '</div>';
        echo '</form>';
        
        echo '<div class="category-navigation">';
        echo '<a href="?idp=-8" class="return-btn">Powrót do kategorii</a>';
        echo '</div>';
        echo '</div>';
    }

    /*
     * UsunKategorie() - Usuwa kategorię z systemu
     * 
     * Funkcja odpowiada za usuwanie kategorii, z zabezpieczeniami przed usunięciem
     * kategorii posiadającej podkategorie. Wymaga potwierdzenia przez użytkownika.
     * 
     * Wyświetla formularz usuwania kategorii lub komunikat o wyniku operacji
     */
    function UsunKategorie() {
        // Tworzy nowy obiekt klasy Admin
        $Admin = new Admin();
        // Sprawdza status logowania użytkownika
        $status_login = $Admin->CheckLogin();
        
        // Jeśli użytkownik nie jest zalogowany, wyświetla formularz logowania i kończy funkcję
        if ($status_login != 1) {
            echo $Admin->FormularzLogowania();
            return;
        }

        // Uzyskuje dostęp do globalnego obiektu połączenia z bazą danych
        global $conn;
        
        // Pobiera ID kategorii z parametru URL, konwertując go na liczbę całkowitą
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        
        // Sprawdza, czy ID kategorii jest prawidłowe
        if ($category_id === 0) {
            echo '<div class="error-message">Nie wybrano kategorii do usunięcia.</div>';
            header("Location: index.php?idp=-8");
            exit();
        }

        // Przygotowuje i wykonuje zapytanie sprawdzające istnienie kategorii
        $check_query = "SELECT * FROM categories WHERE id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Jeśli kategoria nie istnieje, wyświetla komunikat i przekierowuje
        if (!$result->fetch_assoc()) {
            echo '<div class="error-message">Kategoria nie istnieje.</div>';
            header("Location: index.php?idp=-8");
            exit();
        }

        // Sprawdza, czy kategoria ma podkategorie
        $check_children = "SELECT COUNT(*) as count FROM categories WHERE matka = ?";
        $stmt = $conn->prepare($check_children);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Jeśli kategoria ma podkategorie, wyświetla komunikat i przekierowuje
        if ($row['count'] > 0) {
            echo '<div class="error-message">Nie można usunąć kategorii, która posiada podkategorie!</div>';
            header("Location: index.php?idp=-8");
            exit();
        }

        // Przygotowuje i wykonuje zapytanie usuwające kategorię
        $delete_query = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $category_id);
        
        // Sprawdza wynik usuwania i przekierowuje z odpowiednim komunikatem
        if ($stmt->execute()) {
            header("Location: index.php?idp=-8&deleted=1");
            exit();
        } else {
            header("Location: index.php?idp=-8&error=1");
            exit();
        }
    }
}
?>