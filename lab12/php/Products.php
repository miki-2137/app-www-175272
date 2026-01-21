<?php
class Products {
    /**
     * DodajProdukt - Dodaje nowy produkt do bazy danych
     * 
     * Funkcja wyświetla formularz dodawania produktu i obsługuje jego wysłanie.
     * Zapisuje wszystkie dane produktu wraz ze zdjęciem do bazy danych.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function DodajProdukt() {
        // Tworzy nowy obiekt klasy Admin do zarządzania uprawnieniami
        $Admin = new Admin(); 
        // Sprawdza, czy użytkownik jest zalogowany jako administrator
        $status_login = $Admin->CheckLogin(); 
        if($status_login != 1) {
            // Wyświetla formularz logowania, jeśli użytkownik nie jest zalogowany
            echo $Admin->FormularzLogowania(); 
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
                try {
                    // Sprawdzenie typu pliku
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($_FILES['zdjecie']['tmp_name']);
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception('Niedozwolony typ pliku. Dozwolone są tylko obrazy JPEG, PNG i GIF.');
                    }

                    // Sprawdzenie rozmiaru pliku (max 40MB)
                    if ($_FILES['zdjecie']['size'] > 40000 * 1024) {
                        throw new Exception('Plik jest zbyt duży. Maksymalny rozmiar to 60KB.');
                    }

                    // Konwersja obrazu do formatu JPEG z kompresją
                    $sourceImage = imagecreatefromstring(file_get_contents($_FILES['zdjecie']['tmp_name']));
                    if ($sourceImage === false) {
                        throw new Exception('Nie udało się przetworzyć obrazu.');
                    }

                    // Tworzenie bufora dla JPEG
                    ob_start();
                    imagejpeg($sourceImage, null, 75); // 75 to jakość kompresji
                    $zdjecie = ob_get_contents();
                    ob_end_clean();
                    
                    imagedestroy($sourceImage);

                } catch (Exception $e) {
                    echo '<div class="error">Błąd podczas przetwarzania zdjęcia: ' . $e->getMessage() . '</div>';
                    return;
                }
            }
            
            $query = "INSERT INTO products (tytul, opis, data_utworzenia, data_modyfikacji, data_wygasniecia, 
                     cena_netto, podatek_vat, ilosc_dostepnych, status_dostepnosci, kategoria, 
                     gabaryt_produktu, zdjecie) VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssdiiiiis", $tytul, $opis, $data_wygasniecia, $cena_netto, 
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
        echo '<div class="product-form-container">
    <div class="form-header">
        <h3>Dodaj nowy produkt</h3>
    </div>
    <form method="POST" enctype="multipart/form-data" class="product-form">
        <div class="form-group"><label>Tytuł:</label><input type="text" name="tytul" maxlength="255" required></div>
        <div class="form-group"><label>Opis:</label><textarea name="opis" required></textarea></div>
        <div class="form-group"><label>Cena netto:</label><input type="number" step="0.01" name="cena_netto" required></div>
        <div class="form-group"><label>VAT (%):</label><input type="number" name="podatek_vat" required></div>
        <div class="form-group"><label>Ilość:</label><input type="number" name="ilosc_dostepnych" required></div>
        <div class="form-group"><label>Status dostępności:</label>
            <select name="status_dostepnosci">
                <option value="1">Dostępny</option>
                <option value="0">Niedostępny</option>
            </select>
        </div>
        <div class="form-group"><label>Kategoria:</label>
            <select name="kategoria">';
        $this->WyswietlKategorie(); // Wywołuje metodę do wyświetlenia kategorii
        echo '
        </select></div>
        <div class="form-group"><label>Gabaryt:</label>
        <select name="gabaryt_produktu">
            <option value="1">Mały</option>
            <option value="2">Średni</option>
            <option value="3">Duży</option>
        </select></div>
        <div class="form-group"><label>Data wygaśnięcia:</label><input type="datetime-local" name="data_wygasniecia" required></div>
        <div class="form-group"><label>Zdjęcie:</label><input type="file" name="zdjecie" accept="image/*"></div>
        <div class="form-actions">
            <input type="submit" name="submit" value="Dodaj produkt" class="button add">
            <a href="?idp=-12" class="button cancel">Anuluj</a>
        </div>
        </form>
        </div>';
    }

    /**
     * UsunProdukt - Usuwa produkt z bazy danych
     * 
     * Funkcja usuwa wybrany produkt po potwierdzeniu przez użytkownika.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function UsunProdukt() {
        // Tworzy nowy obiekt klasy Admin do weryfikacji uprawnień
        $Admin = new Admin();
        // Sprawdza uprawnienia administratora
        $status_login = $Admin->CheckLogin();
        if($status_login != 1) {
            // Wyświetla formularz logowania dla niezalogowanych użytkowników
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

    /**
     * EdytujProdukt - Edytuje istniejący produkt w bazie danych
     * 
     * Ta funkcja pozwala na edycję istniejącego produktu. Sprawdza uprawnienia użytkownika,
     * obsługuje aktualizację danych produktu, w tym przesyłanie nowego zdjęcia,
     * oraz wyświetla formularz edycji z aktualnymi danymi produktu.
     */
    function EdytujProdukt() {
        // Sprawdzenie uprawnień administratora
        $Admin = new Admin();
        if($Admin->CheckLogin() != 1) {
            echo $Admin->FormularzLogowania();
            return;
        }

        global $conn;
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Obsługa formularza aktualizacji produktu
        if(isset($_POST['update'])) {
            try {
                // Przygotowanie podstawowych danych produktu
                $params = [
                    mysqli_real_escape_string($conn, $_POST['tytul']),
                    mysqli_real_escape_string($conn, $_POST['opis']),
                    date('Y-m-d H:i:s', strtotime($_POST['data_wygasniecia'])),
                    floatval($_POST['cena_netto']),
                    intval($_POST['podatek_vat']),
                    intval($_POST['ilosc_dostepnych']),
                    intval($_POST['status_dostepnosci']),
                    intval($_POST['kategoria']),
                    intval($_POST['gabaryt_produktu'])
                ];
                
                // Przygotowanie zapytania SQL do aktualizacji
                $query = "UPDATE products SET 
                         tytul=?, opis=?, data_modyfikacji=NOW(), data_wygasniecia=?,
                         cena_netto=?, podatek_vat=?, ilosc_dostepnych=?,
                         status_dostepnosci=?, kategoria=?, gabaryt_produktu=?";
                $types = "sssdiiiii";

                // Obsługa przesyłania nowego zdjęcia
                if(isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
                    // Sprawdzenie typu pliku
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($_FILES['zdjecie']['tmp_name']);
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        throw new Exception('Niedozwolony typ pliku. Dozwolone są tylko obrazy JPEG, PNG i GIF.');
                    }

                    // Sprawdzenie rozmiaru pliku nie moze przekraczac 60KB
                    if ($_FILES['zdjecie']['size'] > 60 * 1024) {
                        throw new Exception('Plik jest zbyt duży. Maksymalny rozmiar to 60KB.');
                    }

                    // Przetwarzanie obrazu
                    $sourceImage = imagecreatefromstring(file_get_contents($_FILES['zdjecie']['tmp_name']));
                    if (!$sourceImage) {
                        throw new Exception('Nie udało się przetworzyć obrazu.');
                    }

                    // Kompresja i zapisanie obrazu
                    ob_start();
                    imagejpeg($sourceImage, null, 75);
                    $imageData = ob_get_contents();
                    ob_end_clean();
                    imagedestroy($sourceImage);

                    // Dodanie zdjęcia do zapytania SQL
                    $query .= ", zdjecie=?";
                    $types .= "s";
                    $params[] = $imageData;
                }

                // Dodanie ID produktu do zapytania
                $query .= " WHERE id=?";
                $types .= "i";
                $params[] = $id;

                // Wykonanie zapytania SQL
                $stmt = $conn->prepare($query);
                $stmt->bind_param($types, ...$params);
                
                if($stmt->execute()) {
                    echo '<div class="success">Produkt został zaktualizowany pomyślnie.</div>';
                    header("refresh:2;url=index.php?idp=-12");
                }
                $stmt->close();

            } catch (Exception $e) {
                echo '<div class="error">' . $e->getMessage() . '</div>';
            }
        }

        // Pobieranie danych produktu do edycji
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Wyświetlanie formularza edycji produktu
        if($product = $result->fetch_assoc()) {
            echo '
            <div class="edit-container">
                <h3>Edytuj produkt</h3>
                <form method="POST" action="index.php?idp=-14&id=' . $id . '" enctype="multipart/form-data" class="product-form">
                    <input type="hidden" name="id" value="' . $id . '">
                    <input type="hidden" name="update" value="1">
                    
                    <div class="form-group">
                        <label>Tytuł:</label>
                        <input type="text" name="tytul" value="' . htmlspecialchars($product['tytul']) . '" required>
                    </div>
                    <div class="form-group">
                        <label>Opis:</label>
                        <textarea name="opis" required>' . htmlspecialchars($product['opis']) . '</textarea>
                    </div>
                    <div class="form-group">
                        <label>Cena netto:</label>
                        <input type="number" step="0.01" name="cena_netto" value="' . $product['cena_netto'] . '" required>
                    </div>
                    <div class="form-group">
                        <label>VAT (%):</label>
                        <input type="number" name="podatek_vat" value="' . $product['podatek_vat'] . '" required>
                    </div>
                    <div class="form-group">
                        <label>Ilość:</label>
                        <input type="number" name="ilosc_dostepnych" value="' . $product['ilosc_dostepnych'] . '" required>
                    </div>
                    <div class="form-group">
                        <label>Status dostępności:</label>
                        <select name="status_dostepnosci">
                            <option value="1"' . ($product['status_dostepnosci'] == 1 ? ' selected' : '') . '>Dostępny</option>
                            <option value="0"' . ($product['status_dostepnosci'] == 0 ? ' selected' : '') . '>Niedostępny</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kategoria:</label>
                        <select name="kategoria">';
            // Wyświetlenie listy kategorii
            $this->WyswietlKategorie($product['kategoria']);
            echo '</select>
                    </div>
                    <div class="form-group">
                        <label>Gabaryt:</label>
                        <select name="gabaryt_produktu">
                            <option value="1"' . ($product['gabaryt_produktu'] == 1 ? ' selected' : '') . '>Mały</option>
                            <option value="2"' . ($product['gabaryt_produktu'] == 2 ? ' selected' : '') . '>Średni</option>
                            <option value="3"' . ($product['gabaryt_produktu'] == 3 ? ' selected' : '') . '>Duży</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Data wygaśnięcia:</label>
                        <input type="datetime-local" name="data_wygasniecia" value="' . 
                        date('Y-m-d\TH:i', strtotime($product['data_wygasniecia'])) . '" required>
                    </div>';
            
            // Wyświetlenie aktualnego zdjęcia produktu
            if($product['zdjecie']) {
                echo '<div class="form-group">
                        <label>Aktualne zdjęcie:</label>
                        <div class="current-image">
                            <img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . '" style="max-width:200px;">
                        </div>
                    </div>';
            }
            
            // Pole do przesłania nowego zdjęcia
            echo '<div class="form-group">
                        <label>Nowe zdjęcie:</label>
                        <input type="file" name="zdjecie" accept="image/*">
                    </div>
                    <div class="form-actions">
                        <input type="submit" name="update" value="Aktualizuj produkt" class="button edit">
                        <a href="?idp=-12" class="button cancel">Anuluj</a>
                    </div>
                </form>
            </div>';
        } else {
            echo '<div class="error">Nie znaleziono produktu.</div>';
        }
        $stmt->close();
    }

    /**
     * WyswietlKategorie - Wyświetla listę kategorii w formie opcji dla selecta
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

    /**
     * ListaProduktow - Generuje i zwraca listę wszystkich produktów
     * 
     * Wyświetla produkty w formie tabeli z możliwością edycji i usuwania.
     * Pokazuje zdjęcia, szczegóły produktów oraz przyciski akcji.
     */
    function ListaProduktow() {
        global $conn;
        
        // Sprawdzanie czy użytkownik chce dostosować ilość produktu
        if(isset($_POST['adjust_quantity'])) {
            // Pobieranie i konwertowanie danych z formularza
            $id = intval($_POST['product_id']);
            $adjustment = intval($_POST['adjustment']);
            
            // Przygotowywanie zapytania SQL do aktualizacji ilości produktu
            $query = "UPDATE products SET ilosc_dostepnych = ilosc_dostepnych + ?, data_modyfikacji = NOW() 
                     WHERE id = ? AND (ilosc_dostepnych + ?) >= 0";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iii", $adjustment, $id, $adjustment);
            
            // Wykonywanie zapytania i obsługa przekierowania
            if($stmt->execute()) {
                if(!isset($_POST['ajax'])) {
                    // Przekierowywanie użytkownika po pomyślnej aktualizacji
                    header("Location: index.php?idp=-12");
                    exit;
                }
            }
            $stmt->close();
            
            // Kończenie skryptu dla żądań AJAX
            if(isset($_POST['ajax'])) {
                exit;
            }
        }
        
        // Dodawanie skryptu JavaScript i rozpoczynanie kontenera listy produktów
        $output = '<script src="js/cart.js"></script>';
        $output .= '<div class="product-list-container">';
        
        // Przygotowywanie zapytania SQL do pobrania produktów wraz z nazwami kategorii
        $query = "SELECT p.*, c.nazwa as nazwa_kategorii 
                 FROM products p 
                 LEFT JOIN categories c ON p.kategoria = c.id 
                 ORDER BY p.data_utworzenia DESC";
        $result = $conn->query($query);

        // Tworzenie nagłówka tabeli produktów
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

        // Iterowanie przez wszystkie produkty i tworzenie wierszy tabeli
        while($product = $result->fetch_assoc()) {
            // Sprawdzanie dostępności produktu
            $dostepnosc = $this->SprawdzDostepnosc($product);
            
            // Rozpoczynanie nowego wiersza tabeli
            $output .= '<tr>';
            // Dodawanie komórki ze zdjęciem produktu
            $output .= '<td class="product-image-cell">';
            if($product['zdjecie']) {
                $output .= '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . 
                          '" alt="' . htmlspecialchars($product['tytul']) . '" class="product-image-small">';
            }
            $output .= '</td>';
            
            // Dodawanie pozostałych danych produktu
            $output .= '<td>' . htmlspecialchars($product['tytul']) . '</td>';
            $output .= '<td class="product-description">' . htmlspecialchars($product['opis']) . '</td>';
            $output .= '<td>' . number_format($product['cena_netto'], 2) . ' PLN</td>';
            $output .= '<td>' . $product['podatek_vat'] . '%</td>';
            
            // Dodawanie formularza do zmiany ilości produktu
            $output .= '<td class="quantity-cell">';
            $output .= '<form method="POST" class="quantity-form" onsubmit="return false;">';
            $output .= '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
            $output .= '<button type="button" class="quantity-btn minus">-</button>';
            $output .= '<span class="quantity-value">' . $product['ilosc_dostepnych'] . '</span>';
            $output .= '<button type="button" class="quantity-btn plus">+</button>';
            $output .= '<input type="hidden" name="adjustment" value="0" class="quantity-adjustment">';
            $output .= '</form>';
            $output .= '</td>';
            
            // Dodawanie komórki z nazwą kategorii
            $output .= '<td class="category-cell">';
            $output .= '<span class="category-name">' . 
                      (empty($product['nazwa_kategorii']) ? 'Brak kategorii' : htmlspecialchars($product['nazwa_kategorii'])) . 
                      '</span>';
            $output .= '</td>';
            
            // Dodawanie komórki ze statusem dostępności
            $output .= '<td><span class="status-' . ($dostepnosc ? 'available' : 'unavailable') . 
                      '">' . ($product['status_dostepnosci'] == 1 ? 'Dostępny' : 'Niedostępny') . '</span></td>';
            
            // Dodawanie przycisków akcji (edycja, usunięcie)
            $output .= '<td class="action-buttons">';
            $output .= '<a href="?idp=-14&id=' . $product['id'] . '" class="button edit">Edytuj</a> ';
            $output .= '<a href="?idp=-15&id=' . $product['id'] . '" class="button delete" onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">Usuń</a>';
            $output .= '</td>';
            
            // Kończenie wiersza tabeli
            $output .= '</tr>';
        }
        
        // Kończenie tabeli
        $output .= '</tbody>';
        $output .= '</table>';
        
        // Dodawanie przycisku do tworzenia nowego produktu
        $output .= '<div class="add-product-button">';
        $output .= '<a href="?idp=-13" class="button add">Dodaj nowy produkt</a>';
        $output .= '</div>';
        
        // Dodawanie skryptu JavaScript do obsługi przycisków zmiany ilości
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
        
        // Kończenie kontenera listy produktów
        $output .= '</div>';
        return $output;
    }

    /**
     * SprawdzDostepnosc - Sprawdza dostępność produktu
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

    /**
     * PokazProdukty - Wyświetla panel zarządzania produktami
     * 
     * Główna funkcja wyświetlająca panel produktów.
     * Obsługuje różne akcje (dodawanie, edycja, usuwanie)
     * i wyświetla odpowiednie formularze lub listę produktów.
     * Dostępna tylko dla zalogowanych administratorów.
     */
    function PokazProdukty() {
        // Tworzy nowy obiekt klasy Admin do zarządzania uprawnieniami
        $Admin = new Admin();
        // Sprawdza czy użytkownik jest zalogowany jako administrator
        $status_login = $Admin->CheckLogin();
        if($status_login != 1) {
            // Wyświetla formularz logowania dla niezalogowanych użytkowników
            echo $Admin->FormularzLogowania();
            return;
        }
        
        echo '<h3 class="h3-admin">Panel Produktów</h3>';
        
        echo '<a class="return-btn" href="?idp=-1">Powrót do Panelu Admina</a>';
        
        
        // Obsługa różnych akcji
        // Sprawdza, czy została określona akcja w parametrach GET
        if(isset($_GET['action'])) {
            // Wybiera odpowiednią operację na podstawie wartości akcji
            switch($_GET['action']) {
                case 'add':
                    // Wywołuje metodę dodawania nowego produktu
                    $this->DodajProdukt();
                    break;
                case 'edit':
                    // Wywołuje metodę edycji istniejącego produktu
                    $this->EdytujProdukt();
                    break;
                case 'delete':
                    // Wywołuje metodę usuwania produktu
                    $this->UsunProdukt();
                    break;
                default:
                    // W przypadku nieznanej akcji wyświetla listę produktów
                    echo $this->ListaProduktow();
            }
        } else {
            // Jeśli nie określono akcji, wyświetla domyślną listę produktów
            echo $this->ListaProduktow();
        }
    }
}
?>