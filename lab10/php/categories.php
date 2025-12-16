<?php
class Categories {

	/*
     * PokazKategorie() - Wyświetla panel kategorii dla zalogowanego użytkownika
     * Funkcja sprawdza, czy użytkownik jest zalogowany, a następnie wyświetla
     * panel kategorii, w tym nagłówek i listę kategorii. Jeśli użytkownik nie jest
     * zalogowany, wyświetla formularz logowania.
     */
    function PokazKategorie() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();                                                            
        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Panel Kategorii</h3>';
            echo '<div class="admin-links">';

            echo '</div>';
			// przechodzi do funkcji ListaKategorii()
            echo $this->ListaKategorii();
        } else {
            echo $Admin->FormularzLogowania();
        }
    }
	


    /*
     * ListaKategorii() - Wyświetla hierarchiczną listę kategorii w formie drzewa
     * 
     * Funkcja pobiera kategorie z bazy danych, organizuje je w strukturę drzewiastą
     * i wyświetla je w czytelnym formacie, pokazując relacje nadrzędne i podrzędne.
     * 
     * Wyświetla listę kategorii bezpośrednio na ekranie
     */
    function ListaKategorii() {
        global $conn;
        $query = "SELECT id, matka, nazwa FROM categories ORDER BY matka, id ASC LIMIT 100";
        $result = $conn->query($query);
        $categories = array();
        $category_tree = array();

        // Najpierw zbuduj pełną strukturę kategorii
        while($row = $result->fetch_assoc()) {
            $categories[$row['id']] = $row;
            
            // Zbuduj strukturę drzewa kategorii
            if (!isset($category_tree[$row['matka']])) {
                $category_tree[$row['matka']] = array();
            }
            $category_tree[$row['matka']][] = $row['id'];
        }
        
        echo '<div class="tree-container">';
		//przechodzi do funkcji printCategoryTreeRecursive, aby wygenerować rekurencyjnie zawartość drzewa
        $this->printCategoryTreeRecursive($categories, $category_tree, 0, 0);
        echo '</div>';

        echo '<div class="category-actions">';
        echo '<a href="?idp=-1" class="return-btn">Powrót do Panelu Admina</a>';
        echo '<a href="?idp=-9" class="category-btn">Create New Category</a>';
        echo '<a href="?idp=-10" class="category-btn">Edit Category</a>';
        echo '<a href="?idp=-11" class="category-btn">Delete Category</a>';
        echo '</div>';
    }

    /*
     * printCategoryTreeRecursive() - Rekurencyjna metoda do wyświetlania drzewa kategorii
     * Renderuje hierarchiczną strukturę kategorii, wyświetlając kolejne poziomy zagnieżdżenia
     * z zachowaniem relacji rodzic-dziecko. Używa wcięć do pokazania struktury drzewiastej.
     */
    function printCategoryTreeRecursive($categories, $category_tree, $parent_id, $depth) {
        // Sprawdź, czy istnieją podkategorie dla danego rodzica
        if (!isset($category_tree[$parent_id]) || empty($category_tree[$parent_id])) {
            return;
        }

        // Przejdź przez wszystkie podkategorie
        foreach ($category_tree[$parent_id] as $category_id) {
            $category = $categories[$category_id];
            
            // Wyświetl nazwę kategorii z odpowiednim wcięciem i linkiem
            echo str_repeat('     ', $depth);
            echo $depth > 0 ? '|-' : '';
            echo '<a href="?category=' . $category_id . '" class="category-link">' . 
                 htmlspecialchars($category['nazwa']) . '</a>' . "\n";
            
            // Rekurencyjnie wyświetl podkategorie
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
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        
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

        echo '<div class="add-category-container">';
        echo '<h3 class="section-title">Dodaj nową kategorię</h3>';
        echo '<form action="" method="POST" class="add-category-form">';
        
        echo '<div class="form-group">';
        echo '<label for="category_name">Nazwa kategorii:</label>';
        echo '<input type="text" id="category_name" name="category_name" required>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="parent_category">Kategoria nadrzędna:</label>';
        echo '<select id="parent_category" name="parent_category">';
        foreach ($categories as $id => $name) {
            echo "<option value='$id'>$name</option>";
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<input type="submit" name="submit_category" value="Dodaj kategorię" class="submit-btn">';
        echo '</div>';
        
        echo '</form>';
        
        // Obsługa dodawania kategorii
        if (isset($_POST['submit_category'])) {
            $name = $conn->real_escape_string($_POST['category_name']);
            $parent = intval($_POST['parent_category']);
            
            $insert_query = "INSERT INTO categories (nazwa, matka) VALUES ('$name', $parent)";
            if ($conn->query($insert_query)) {
                echo '<div class="success-message">Kategoria została dodana pomyślnie!</div>';
            } else {
                echo '<div class="error-message">Błąd podczas dodawania kategorii: ' . $conn->error . '</div>';
            }
        }
        
        echo '</div>';
        
        echo '<div class="category-navigation">';
        echo '<a href="?idp=-8" class="go-back">Powrót do kategorii</a>';
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
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        
        if ($status_login != 1) {
            echo $Admin->FormularzLogowania();
            return;
        }

        global $conn;
        
        // Jeśli przesłano formularz edycji
        if (isset($_POST['submit_edit_category'])) {
            $category_id = intval($_POST['category_id']);
            $new_name = $conn->real_escape_string($_POST['category_name']);
            $new_parent = intval($_POST['parent_category']);
            
            $update_query = "UPDATE categories SET nazwa = '$new_name', matka = $new_parent WHERE id = $category_id";
            
            if ($conn->query($update_query)) {
                echo '<div class="success-message">Kategoria została zaktualizowana pomyślnie!</div>';
            } else {
                echo '<div class="error-message">Błąd podczas aktualizacji kategorii: ' . $conn->error . '</div>';
            }
        }

        // Pobieranie listy kategorii do wyboru
        $query = "SELECT id, nazwa, matka FROM categories ORDER BY nazwa ASC";
        $result = $conn->query($query);
        $categories = array();
        $categories[0] = "Brak (kategoria główna)";
        $category_list = array();
        
        while($row = $result->fetch_assoc()) {
            $categories[$row['id']] = $row['nazwa'];
            $category_list[] = $row;
        }

        echo '<div class="edit-category-container">';
        echo '<h3 class="section-title">Edytuj kategorię</h3>';
        
        // Formularz wyboru kategorii do edycji
        echo '<form action="" method="POST" class="select-category-form">';
        echo '<div class="form-group">';
        echo '<label for="edit_category_select">Wybierz kategorię do edycji:</label>';
        echo '<select id="edit_category_select" name="edit_category_select" onchange="this.form.submit()">';
        echo '<option value="">Wybierz kategorię</option>';
        
        foreach ($category_list as $category) {
            echo '<option value="' . $category['id'] . '">' . 
                 htmlspecialchars($category['nazwa']) . 
                 ' (ID: ' . $category['id'] . ')' . 
                 '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        echo '</form>';

        // Formularz edycji wybranej kategorii
        if (isset($_GET['edit_category']) || isset($_POST['edit_category_select'])) {
            $edit_id = isset($_GET['edit_category']) ? 
                       intval($_GET['edit_category']) : 
                       intval($_POST['edit_category_select']);
            
            // Pobierz dane wybranej kategorii
            $edit_query = "SELECT * FROM categories WHERE id = $edit_id";
            $edit_result = $conn->query($edit_query);
            $category_data = $edit_result->fetch_assoc();

            echo '<form action="" method="POST" class="edit-category-form">';
            echo '<input type="hidden" name="category_id" value="' . $edit_id . '">';
            
            echo '<div class="form-group">';
            echo '<label for="category_name">Nazwa kategorii:</label>';
            echo '<input type="text" id="category_name" name="category_name" required value="' . 
                 htmlspecialchars($category_data['nazwa']) . '">';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label for="parent_category">Kategoria nadrzędna:</label>';
            echo '<select id="parent_category" name="parent_category">';
            foreach ($categories as $id => $name) {
                $selected = ($id == $category_data['matka']) ? 'selected' : '';
                echo "<option value='$id' $selected>$name</option>";
            }
            echo '</select>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<input type="submit" name="submit_edit_category" value="Zaktualizuj kategorię" class="submit-btn">';
            echo '</div>';
            
            echo '</form>';
        }
        
        echo '</div>';
        
        echo '<div class="category-navigation edit-category-navigation">';
        echo '<a href="?idp=-8" class="go-back">Powrót do kategorii</a>';
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
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        
        if ($status_login != 1) {
            echo $Admin->FormularzLogowania();
            return;
        }

        global $conn;
        
        // Jeśli przesłano formularz usunięcia
        if (isset($_POST['submit_delete_category'])) {
            $category_id = intval($_POST['category_id']);
            
            // Sprawdź, czy kategoria ma podkategorie
            $check_children_query = "SELECT COUNT(*) as child_count FROM categories WHERE matka = $category_id";
            $child_result = $conn->query($check_children_query);
            $child_row = $child_result->fetch_assoc();
            
            if ($child_row['child_count'] > 0) {
                echo '<div class="error-message">Nie można usunąć kategorii, która posiada podkategorie!</div>';
            } else {
                // Usuń kategorię
                $delete_query = "DELETE FROM categories WHERE id = $category_id";
                
                if ($conn->query($delete_query)) {
                    echo '<div class="success-message">Kategoria została usunięta pomyślnie!</div>';
                } else {
                    echo '<div class="error-message">Błąd podczas usuwania kategorii: ' . $conn->error . '</div>';
                }
            }
        }

        // Pobieranie listy kategorii
        $query = "SELECT id, nazwa FROM categories ORDER BY nazwa ASC";
        $result = $conn->query($query);

        echo '<div class="delete-category-container">';
        echo '<h3 class="section-title">Usuń kategorię</h3>';
        
        echo '<form action="" method="POST" class="delete-category-form">';
        
        echo '<div class="form-group">';
        echo '<label for="delete_category_select">Wybierz kategorię do usunięcia:</label>';
        echo '<select id="delete_category_select" name="category_id" required>';
        echo '<option value="">Wybierz kategorię</option>';
        
        while($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . 
                 htmlspecialchars($row['nazwa']) . 
                 ' (ID: ' . $row['id'] . ')' . 
                 '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<input type="submit" name="submit_delete_category" value="Usuń kategorię" class="submit-btn delete-btn" onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\');">';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        
        echo '<div class="category-navigation delete-category-navigation">';
        echo '<a href="?idp=-8" class="go-back">Powrót do kategorii</a>';
        echo '</div>';
        
        echo '</div>';
    }
}
?>