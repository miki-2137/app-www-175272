<?php
class Products {
    /**
     * Dodaje nowy produkt do bazy danych
     * 
     * Funkcja wyświetla formularz dodawania produktu i obsługuje jego wysłanie.
     * Zapisuje wszystkie dane produktu wraz ze zdjęciem do bazy danych.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function DodajProdukt() {
        $Admin = new Admin(); // Tworzy nowy obiekt klasy Admin
        $status_login = $Admin->CheckLogin(); // Sprawdza, czy użytkownik jest zalogowany jako administrator
        if($status_login != 1) {
            echo $Admin->FormularzLogowania(); // Wyświetla formularz logowania, jeśli użytkownik nie jest zalogowany
            return;
        }

        if(isset($_POST['submit'])) {
            global $conn;
            
            $tytul = mysqli_real_escape_string($conn, $_POST['tytul']);
            $opis = mysqli_real_escape_string($conn, $_POST['opis']);
            $cena_netto = number_format(floatval($_POST['cena_netto']), 2, '.', '');
            $podatek_vat = intval($_POST['podatek_vat']);
            $ilosc_dostepnych = intval($_POST['ilosc_dostepnych']);
            $status_dostepnosci = intval($_POST['status_dostepnosci']);
            $kategoria = intval($_POST['kategoria']);
            $gabaryt_produktu = intval($_POST['gabaryt_produktu']);
            $data_wygasniecia = date('Y-m-d H:i:s', strtotime($_POST['data_wygasniecia']));
            
            // Obsługa przesyłania zdjęcia
            $zdjecie = null;
            if(isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
                $zdjecie = file_get_contents($_FILES['zdjecie']['tmp_name']);
            }
            
            $query = "INSERT INTO products (tytul, opis, data_utworzenia, data_modyfikacji, data_wygasniecia, 
                     cena_netto, podatek_vat, ilosc_dostepnych, status_dostepnosci, kategoria, 
                     gabaryt_produktu, zdjecie) VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssdiiiiib", $tytul, $opis, $data_wygasniecia, $cena_netto, 
                            $podatek_vat, $ilosc_dostepnych, $status_dostepnosci, $kategoria, 
                            $gabaryt_produktu, $zdjecie);
            
            if($stmt->execute()) {
                echo '<div class="success">Produkt został dodany pomyślnie.</div>';
            } else {
                echo '<div class="error">Błąd podczas dodawania produktu: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }

        // Wyświetlanie formularza
        echo '<div class="product-form-container">';
        echo '<div class="form-header">';
        echo '<h3>Dodaj nowy produkt</h3>';
        echo '<a href="?idp=-12" class="button return">Powrót do listy</a>';
        echo '</div>';
        
        echo '<form method="POST" enctype="multipart/form-data" class="product-form">';
        echo '<div class="form-group"><label>Tytuł:</label><input type="text" name="tytul" maxlength="255" required></div>';
        echo '<div class="form-group"><label>Opis:</label><textarea name="opis" required></textarea></div>';
        echo '<div class="form-group"><label>Cena netto:</label><input type="number" step="0.01" name="cena_netto" required></div>';
        echo '<div class="form-group"><label>VAT (%):</label><input type="number" name="podatek_vat" required></div>';
        echo '<div class="form-group"><label>Ilość:</label><input type="number" name="ilosc_dostepnych" required></div>';
        echo '<div class="form-group"><label>Status dostępności:</label>';
        echo '<select name="status_dostepnosci">';
        echo '<option value="1">Dostępny</option>';
        echo '<option value="0">Niedostępny</option>';
        echo '</select></div>';
        echo '<div class="form-group"><label>Kategoria:</label>';
        echo '<select name="kategoria">';
        $this->WyswietlKategorie(); // Wywołuje metodę do wyświetlenia kategorii
        echo '</select></div>';
        echo '<div class="form-group"><label>Gabaryt:</label>';
        echo '<select name="gabaryt_produktu">';
        echo '<option value="1">Mały</option>';
        echo '<option value="2">Średni</option>';
        echo '<option value="3">Duży</option>';
        echo '</select></div>';
        echo '<div class="form-group"><label>Data wygaśnięcia:</label><input type="datetime-local" name="data_wygasniecia" required></div>';
        echo '<div class="form-group"><label>Zdjęcie:</label><input type="file" name="zdjecie" accept="image/*"></div>';
        echo '<div class="form-actions">';
        echo '<input type="submit" name="submit" value="Dodaj produkt" class="button add">';
        echo '<a href="?idp=-12" class="button cancel">Anuluj</a>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }

    /**
     * Usuwa produkt z bazy danych
     * 
     * Funkcja usuwa wybrany produkt po potwierdzeniu przez użytkownika.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function UsunProdukt() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        if($status_login != 1) {
            echo $Admin->FormularzLogowania();
            return;
        }

        global $conn;
        
        // Pobierz ID produktu z URL
        $id = isset($_GET['id']) ? intval(substr($_GET['id'], 0)) : 0;
        
        if($id > 0) {
            $query = "DELETE FROM products WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if($stmt->execute()) {
                echo '<div class="success">Produkt został usunięty.</div>';
            } else {
                echo '<div class="error">Błąd podczas usuwania produktu: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
        
        // Przekierowanie do listy produktów
        header("Location: index.php?idp=-12");
        exit();
    }

    /*
     * Edytuje istniejący produkt
     * 
     * Funkcja wyświetla formularz edycji produktu i obsługuje jego aktualizację.
     * Zapisuje zmiany w danych produktu do bazy danych.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function EdytujProdukt() {
        $Admin = new Admin(); // Tworzy nowy obiekt klasy Admin
        $status_login = $Admin->CheckLogin(); // Sprawdza, czy użytkownik jest zalogowany jako administrator
        if($status_login != 1) {
            echo $Admin->FormularzLogowania(); // Wyświetla formularz logowania, jeśli użytkownik nie jest zalogowany
            return;
        }

        global $conn;
        
        if(isset($_POST['update'])) {
            $id = intval($_POST['id']);
            $tytul = mysqli_real_escape_string($conn, $_POST['tytul']);
            $opis = mysqli_real_escape_string($conn, $_POST['opis']);
            $cena_netto = number_format(floatval($_POST['cena_netto']), 2, '.', '');
            $podatek_vat = intval($_POST['podatek_vat']);
            $ilosc_dostepnych = intval($_POST['ilosc_dostepnych']);
            $status_dostepnosci = intval($_POST['status_dostepnosci']);
            $kategoria = intval($_POST['kategoria']);
            $gabaryt_produktu = intval($_POST['gabaryt_produktu']);
            $data_wygasniecia = date('Y-m-d H:i:s', strtotime($_POST['data_wygasniecia']));

            $query = "UPDATE products SET tytul=?, opis=?, data_modyfikacji=NOW(), 
                     data_wygasniecia=?, cena_netto=?, podatek_vat=?, ilosc_dostepnych=?, 
                     status_dostepnosci=?, kategoria=?, gabaryt_produktu=?";
            
            $params = [$tytul, $opis, $data_wygasniecia, $cena_netto, $podatek_vat, 
                      $ilosc_dostepnych, $status_dostepnosci, $kategoria, $gabaryt_produktu];
            $types = "sssdiiiii";

            // Obsługa nowego zdjęcia, jeśli zostało przesłane
            if(isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
                $zdjecie = file_get_contents($_FILES['zdjecie']['tmp_name']);
                $query .= ", zdjecie=?";
                $params[] = $zdjecie;
                $types .= "b";
            }

            $query .= " WHERE id=?";
            $params[] = $id;
            $types .= "i";

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if($stmt->execute()) {
                echo '<div class="success">Produkt został zaktualizowany.</div>';
                header("Location: index.php?idp=-12"); // Przekierowanie do listy produktów
                exit();
            } else {
                echo '<div class="error">Błąd podczas aktualizacji produktu: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }

        // Pobierz ID produktu z URL
        $id = isset($_GET['id']) ? intval(substr($_GET['id'], 0)) : 0;
        if($id > 0) {
            $query = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($product = $result->fetch_assoc()) {
                echo '<div class="product-form-container">';
                echo '<div class="form-header">';
                echo '<h3>Edytuj produkt</h3>';
                echo '<a href="?idp=-12" class="button return">Powrót do listy</a>';
                echo '</div>';
                
                echo '<form method="POST" enctype="multipart/form-data" class="product-form">';
                echo '<input type="hidden" name="id" value="' . $product['id'] . '">';
                echo '<div class="form-group"><label>Tytuł:</label>';
                echo '<input type="text" name="tytul" value="' . htmlspecialchars($product['tytul']) . '" maxlength="255" required></div>';
                echo '<div class="form-group"><label>Opis:</label>';
                echo '<textarea name="opis" required>' . htmlspecialchars($product['opis']) . '</textarea></div>';
                echo '<div class="form-group"><label>Cena netto:</label>';
                echo '<input type="number" step="0.01" name="cena_netto" value="' . $product['cena_netto'] . '" required></div>';
                echo '<div class="form-group"><label>VAT (%):</label>';
                echo '<input type="number" name="podatek_vat" value="' . $product['podatek_vat'] . '" required></div>';
                echo '<div class="form-group"><label>Ilość:</label>';
                echo '<input type="number" name="ilosc_dostepnych" value="' . $product['ilosc_dostepnych'] . '" required></div>';
                echo '<div class="form-group"><label>Status dostępności:</label>';
                echo '<select name="status_dostepnosci">';
                echo '<option value="1"' . ($product['status_dostepnosci'] == 1 ? ' selected' : '') . '>Dostępny</option>';
                echo '<option value="0"' . ($product['status_dostepnosci'] == 0 ? ' selected' : '') . '>Niedostępny</option>';
                echo '</select></div>';
                echo '<div class="form-group"><label>Kategoria:</label>';
                echo '<select name="kategoria">';
                $this->WyswietlKategorie($product['kategoria']); // Wywołuje metodę do wyświetlenia kategorii
                echo '</select></div>';
                echo '<div class="form-group"><label>Gabaryt:</label>';
                echo '<select name="gabaryt_produktu">';
                echo '<option value="1"' . ($product['gabaryt_produktu'] == 1 ? ' selected' : '') . '>Mały</option>';
                echo '<option value="2"' . ($product['gabaryt_produktu'] == 2 ? ' selected' : '') . '>Średni</option>';
                echo '<option value="3"' . ($product['gabaryt_produktu'] == 3 ? ' selected' : '') . '>Duży</option>';
                echo '</select></div>';
                echo '<div class="form-group"><label>Data wygaśnięcia:</label>';
                echo '<input type="datetime-local" name="data_wygasniecia" value="' . 
                     date('Y-m-d\TH:i', strtotime($product['data_wygasniecia'])) . '" required></div>';
                echo '<div class="form-group"><label>Aktualne zdjęcie:</label>';
                if($product['zdjecie']) {
                    echo '<div class="current-image">';
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . '" style="max-width:200px;">';
                    echo '</div>';
                }
                echo '</div>';
                echo '<div class="form-group"><label>Nowe zdjęcie:</label><input type="file" name="zdjecie" accept="image/*"></div>';
                echo '<div class="form-actions">';
                echo '<input type="submit" name="update" value="Aktualizuj produkt" class="button edit">';
                echo '<a href="?idp=-12" class="button cancel">Anuluj</a>';
                echo '</div>';
                echo '</form>';
                echo '</div>';
            } else {
                echo '<div class="error">Nie znaleziono produktu.</div>';
            }
            $stmt->close();
        } else {
            echo '<div class="error">Nieprawidłowe ID produktu.</div>';
        }
    }

    /*
     * Wyświetla listę kategorii w formie opcji dla selecta
     */
    private function WyswietlKategorie($selected_id = null) {
        global $conn;
        $query = "SELECT id, nazwa FROM categories ORDER BY nazwa";
        $result = $conn->query($query);
        while($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '"' . 
                 ($selected_id == $row['id'] ? ' selected' : '') . '>' . 
                 htmlspecialchars($row['nazwa']) . '</option>';
        }
    }

    /*
     * Generuje i zwraca listę wszystkich produktów
     * 
     * Wyświetla produkty w formie tabeli z możliwością edycji i usuwania.
     * Pokazuje zdjęcia, szczegóły produktów oraz przyciski akcji.
     */
    function ListaProduktow() {
        global $conn;
        
        // Obsługa zmiany ilości produktu
        if(isset($_POST['adjust_quantity'])) {
            $id = intval($_POST['product_id']);
            $adjustment = intval($_POST['adjustment']);
            
            $query = "UPDATE products SET ilosc_dostepnych = ilosc_dostepnych + ?, data_modyfikacji = NOW() 
                     WHERE id = ? AND (ilosc_dostepnych + ?) >= 0";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iii", $adjustment, $id, $adjustment);
            $stmt->execute();
            $stmt->close();
        }
        
        $output = '<div class="product-list-container">';
        
        // Modyfikacja zapytania aby pobrać nazwę kategorii
        $query = "SELECT p.*, c.nazwa as nazwa_kategorii 
                 FROM products p 
                 LEFT JOIN categories c ON p.kategoria = c.id 
                 ORDER BY p.data_utworzenia DESC";
        $result = $conn->query($query);

        $output .= '<table class="product-table">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>Zdjęcie</th>';
        $output .= '<th>Tytuł</th>';
        $output .= '<th>Opis</th>';
        $output .= '<th>Cena netto</th>';
        $output .= '<th>VAT</th>';
        $output .= '<th>Ilość</th>';
        $output .= '<th>Kategoria</th>';
        $output .= '<th>Status</th>';
        $output .= '<th>Akcje</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        while($product = $result->fetch_assoc()) {
            $dostepnosc = $this->SprawdzDostepnosc($product);
            
            $output .= '<tr>';
            // Komórka ze zdjęciem
            $output .= '<td class="product-image-cell">';
            if($product['zdjecie']) {
                $output .= '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . 
                          '" alt="' . htmlspecialchars($product['tytul']) . '" class="product-image-small">';
            }
            $output .= '</td>';
            
            // Pozostałe dane produktu
            $output .= '<td>' . htmlspecialchars($product['tytul']) . '</td>';
            $output .= '<td class="product-description">' . htmlspecialchars($product['opis']) . '</td>';
            $output .= '<td>' . number_format($product['cena_netto'], 2) . ' PLN</td>';
            $output .= '<td>' . $product['podatek_vat'] . '%</td>';
            
            // Dodanie przycisków +/- do zmiany ilości
            $output .= '<td class="quantity-cell">';
            $output .= '<form method="POST" class="quantity-form">';
            $output .= '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
            $output .= '<button type="submit" name="adjust_quantity" value="-1" class="quantity-btn minus">-</button>';
            $output .= '<span class="quantity-value">' . $product['ilosc_dostepnych'] . '</span>';
            $output .= '<button type="submit" name="adjust_quantity" value="1" class="quantity-btn plus">+</button>';
            $output .= '<input type="hidden" name="adjustment" value="0" class="quantity-adjustment">';
            $output .= '</form>';
            $output .= '</td>';
            
            // Dodanie komórki z kategorią
            $output .= '<td class="category-cell">';
            $output .= '<span class="category-name">' . 
                      (empty($product['nazwa_kategorii']) ? 'Brak kategorii' : htmlspecialchars($product['nazwa_kategorii'])) . 
                      '</span>';
            $output .= '</td>';
            
            $output .= '<td><span class="status-' . ($dostepnosc ? 'available' : 'unavailable') . 
                      '">' . ($product['status_dostepnosci'] == 1 ? 'Dostępny' : 'Niedostępny') . '</span></td>';
            
            $output .= '<td class="action-buttons">';
            $output .= '<a href="?idp=-14&id=' . $product['id'] . '" class="button edit">Edytuj</a> ';
            $output .= '<a href="?idp=-15&id=' . $product['id'] . '" class="button delete" onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">Usuń</a>';
            $output .= '</td>';
            
            $output .= '</tr>';
        }
        
        $output .= '</tbody>';
        $output .= '</table>';
        
        $output .= '<div class="add-product-button">';
        $output .= '<a href="?idp=-13" class="button add">Dodaj nowy produkt</a>';
        $output .= '</div>';
        
        // Dodanie skryptu JavaScript do obsługi przycisków +/-
        $output .= '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const quantityForms = document.querySelectorAll(".quantity-form");
                quantityForms.forEach(form => {
                    const minusBtn = form.querySelector(".minus");
                    const plusBtn = form.querySelector(".plus");
                    const adjustmentInput = form.querySelector(".quantity-adjustment");
                    
                    minusBtn.addEventListener("click", function(e) {
                        adjustmentInput.value = -1;
                    });
                    
                    plusBtn.addEventListener("click", function(e) {
                        adjustmentInput.value = 1;
                    });
                });
            });
        </script>';
        
        $output .= '</div>';
        return $output;
    }

    /*
     * Sprawdza dostępność produktu
     * 
     * Sprawdza czy produkt jest dostępny na podstawie:
     * - statusu dostępności
     * - ilości dostępnych sztuk
     * - daty wygaśnięcia
     */
    private function SprawdzDostepnosc($product) {
        $current_date = date('Y-m-d H:i:s');
        return ($product['status_dostepnosci'] == 1 && 
                $product['ilosc_dostepnych'] > 0 && 
                $product['data_wygasniecia'] > $current_date);
    }

    /*
     * Wyświetla panel zarządzania produktami
     * Główna funkcja wyświetlająca panel produktów.
     * Obsługuje różne akcje (dodawanie, edycja, usuwanie)
     * i wyświetla odpowiednie formularze lub listę produktów.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function PokazProdukty() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();
        if($status_login == 1) {
            echo '<h3 class="h3-admin">Panel Produktów</h3>';
            echo '<div class="return-btn">';
            echo '<a href="?idp=-1">Powrót do Panelu Admina</a>';
            echo '</div>';
            
            // Obsługa różnych akcji
            if(isset($_GET['action'])) {
                switch($_GET['action']) {
                    case 'add':
                        $this->DodajProdukt();
                        break;
                    case 'edit':
                        $this->EdytujProdukt();
                        break;
                    case 'delete':
                        $this->UsunProdukt();
                        break;
                    default:
                        echo $this->ListaProduktow();
                }
            } else {
                echo $this->ListaProduktow();
            }
        } else {
            echo $Admin->FormularzLogowania();
        }
    }
}
?>