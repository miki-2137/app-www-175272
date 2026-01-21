<?php
class Shop {
    /*
     * ShowCart() - Wyświetla zawartość koszyka
     * Funkcja wyświetla wszystkie produkty dodane do koszyka,
     * umożliwia zmianę ilości produktów, ich usunięcie oraz
     * finalizację zakupu. Pokazuje również sumę całkowitą brutto.
     */
    function ShowCart() {
        global $conn;
        $totalGrossPrice = 0;
        
        // Wyświetl komunikat o sukcesie jeśli zakup został zrealizowany
        if (isset($_GET['status']) && $_GET['status'] === 'success') {
            echo '<div class="success-message">Zakup został zrealizowany pomyślnie!</div>';
            return;
        }
        
        // Obsługa akcji koszyka
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            
            // Obsługa finalizacji zamówienia
            if ($action === 'checkout') {
                // Próba finalizacji zamówienia
                if ($this->Checkout()) {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo '<div class="success-message">Zakup został zrealizowany pomyślnie!</div>';
                        exit();
                    } else {
                        header('Location: index.php?idp=-17&status=success');
                        exit();
                    }
                }
            } 
            // Obsługa czyszczenia koszyka
            elseif ($action === 'clear') {
                // Wyczyszczenie koszyka
                $this->ClearCart();
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo '<div class="cart-empty">Koszyk jest pusty.</div>';
                    exit();
                } else {
                    header('Location: index.php?idp=-17');
                    exit();
                }
            } 
            // Obsługa akcji dla konkretnych produktów
            elseif (isset($_POST['product_id'])) {
                $productId = intval($_POST['product_id']);
                
                // Zwiększenie ilości produktu
                if ($action === 'increase') {
                    $_SESSION['cart'][$productId]++;
                } 
                // Zmniejszenie ilości produktu
                elseif ($action === 'decrease') {
                    if ($_SESSION['cart'][$productId] > 1) {
                        $_SESSION['cart'][$productId]--;
                    } else {
                        // Usunięcie produktu, jeśli ilość spadnie poniżej 1
                        unset($_SESSION['cart'][$productId]);
                    }
                } 
                // Całkowite usunięcie produktu z koszyka
                elseif ($action === 'remove') {
                    unset($_SESSION['cart'][$productId]);
                }
                
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    // Dla żądań AJAX, wyświetl zaktualizowaną zawartość koszyka
                    $this->ShowCart();
                    exit();
                } else {
                    // Dla zwykłych żądań, przekieruj z powrotem do koszyka
                    header('Location: index.php?idp=-17');
                    exit();
                }
            }
        }
        
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            echo '<div class="cart-empty">Koszyk jest pusty.</div>';
            return;
        }

        // Rozpoczęcie sekcji wyświetlającej zawartość koszyka
        echo '<div class="cart-items">';
        // Iteracja przez wszystkie produkty w koszyku
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            // Pobranie informacji o produkcie z bazy danych
            $query = "SELECT * FROM products WHERE id = " . intval($productId);
            $result = $conn->query($query);
            if ($product = $result->fetch_assoc()) {
                // Obliczenie ceny brutto i aktualizacja sumy całkowitej
                $grossPrice = $product['cena_netto'] * (1 + ($product['podatek_vat'] / 100));
                $totalGrossPrice += $grossPrice * $quantity;

                // Wyświetlenie informacji o produkcie w koszyku
                echo '<div class="cart-item">';
                // Wyświetlenie zdjęcia produktu
                echo '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . '" alt="' . htmlspecialchars($product['tytul']) . '" class="cart-product-image">';
                // Wyświetlenie tytułu produktu
                echo '<span>' . htmlspecialchars($product['tytul']) . '</span>';
                // Wyświetlenie ceny brutto produktu
                echo '<span>Cena brutto: ' . number_format($grossPrice, 2) . ' PLN</span>';
                // Wyświetlenie ilości produktu w koszyku
                echo '<span>Ilość: ' . $quantity . '</span>';
                // Formularz do modyfikacji ilości lub usunięcia produktu z koszyka
                echo '<form method="POST" action="index.php?idp=-17">';
                echo '<input type="hidden" name="product_id" value="' . $productId . '">';
                // Przycisk zwiększenia ilości, jeśli dostępna jest większa ilość produktu
                if ($quantity < $product['ilosc_dostepnych']) {
                    echo '<button type="submit" name="action" value="increase">+</button>';
                }
                // Przycisk zmniejszenia ilości, jeśli ilość jest większa niż 1
                if ($quantity > 1) {
                    echo '<button type="submit" name="action" value="decrease">-</button>';
                }
                // Przycisk usunięcia produktu z koszyka
                echo '<button type="submit" name="action" value="remove">Usuń</button>';
                echo '</form>';
                echo '</div>';
            }
        }
        echo '</div>';
        
        // Wyświetlenie sumy całkowitej koszyka
        echo '<div class="cart-total">';
        echo '<h3>Suma całkowita (brutto): ' . number_format($totalGrossPrice, 2) . ' PLN</h3>';
        echo '</div>';
        
        // Dodanie przycisków do finalizacji zakupu i wyczyszczenia koszyka
        echo '<div class="cart-actions">';
        // Formularz do finalizacji zakupu
        echo '<form method="POST" action="index.php?idp=-17" class="checkout-form" style="display: inline-block; margin-right: 10px;">';
        echo '<button type="submit" name="action" value="checkout" class="checkout-button">Wykonaj zakup</button>';
        echo '</form>';
        
        // Formularz do wyczyszczenia koszyka
        echo '<form method="POST" action="index.php?idp=-17" style="display: inline-block;">';
        echo '<button type="submit" name="action" value="clear" class="clear-button">Wyczyść koszyk</button>';
        echo '</form>';
        echo '</div>';
    }

    /*
     * AddToCart($productId) - Dodaje produkt do koszyka
     * Funkcja sprawdza dostępność produktu i dodaje go do koszyka
     * lub zwiększa jego ilość jeśli już się tam znajduje.
     * @param int $productId - ID produktu do dodania
     * @return bool - true jeśli dodano produkt, false w przeciwnym razie
     */
    function AddToCart($productId, $quantity = 1) {
        global $conn;
        
        // Sprawdź czy produkt istnieje i jest dostępny
        $query = "SELECT ilosc_dostepnych FROM products WHERE id = ? AND status_dostepnosci = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($product = $result->fetch_assoc()) {
            // Inicjalizuj koszyk jeśli nie istnieje
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = array();
            }
            
            // Sprawdź czy dodanie kolejnej sztuki nie przekroczy dostępnej ilości
            $currentQuantity = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
            
            if ($currentQuantity + $quantity <= $product['ilosc_dostepnych']) {
                // Dodaj do koszyka lub zwiększ ilość
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] += $quantity;
                } else {
                    $_SESSION['cart'][$productId] = $quantity;
                }
                return true;
            }
        }
        return false;
    }

    /*
     * ShopPage() - Wyświetla stronę sklepu z listą produktów
     * 
     * Funkcja odpowiada za:
     * 1. Obsługę dodawania produktów do koszyka:
     *    - Przetwarza żądania POST z formularza dodawania do koszyka
     *    - Obsługuje zarówno standardowe jak i AJAX-owe żądania
     *    - Dodaje wybraną ilość produktu do koszyka poprzez AddToCart()
     * 
     * 2. Wyświetlanie filtra kategorii:
     *    - Pobiera wszystkie kategorie z bazy danych
     *    - Buduje drzewiastą strukturę kategorii (kategorie i podkategorie)
     *    - Wyświetla rozwijaną listę kategorii z odpowiednimi wcięciami
     * 
     * 3. Wyświetlanie listy produktów:
     *    - Filtruje produkty według wybranej kategorii (jeśli wybrano)
     *    - Pokazuje tylko dostępne produkty (ilosc_dostepnych > 0)
     *    - Dla każdego produktu wyświetla:
     *      * Zdjęcie
     *      * Tytuł
     *      * Opis
     *      * Cenę brutto (z VAT)
     *      * Dostępną ilość
     *      * Formularz dodawania do koszyka z wyborem ilości
     * 
     * 4. Obsługę interfejsu użytkownika:
     *    - Dołącza skrypt JavaScript do obsługi asynchronicznego dodawania do koszyka
     *    - Definiuje style dla powiadomień o sukcesie/błędzie
     *    - Zapewnia responsywny układ produktów
     * 
     * @global mysqli $conn Połączenie z bazą danych
     * @return void
     */
    function ShopPage() {
        global $conn;
        
        // Obsługa dodawania do koszyka
        // Sprawdzenie, czy żądanie jest typu POST i czy zawiera odpowiednie parametry
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['action']) && $_POST['action'] === 'add') {
            // Konwersja ID produktu i ilości na liczby całkowite
            $productId = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            // Próba dodania produktu do koszyka
            $success = $this->AddToCart($productId, $quantity);
            
            // Sprawdzenie, czy żądanie jest asynchroniczne (AJAX)
            if (isset($_POST['ajax'])) {
                if ($success) {
                    // Ustawienie kodu odpowiedzi HTTP na 200 (OK) w przypadku powodzenia
                    http_response_code(200);
                } else {
                    // Ustawienie kodu odpowiedzi HTTP na 400 (Bad Request) w przypadku niepowodzenia
                    http_response_code(400);
                }
                // Zakończenie skryptu dla żądań AJAX
                exit;
            } else {
                // Przekierowanie użytkownika z powrotem do strony sklepu z parametrem statusu
                header('Location: index.php?idp=-16&status=added');
                exit();
            }
        }
        
        // Dodaj skrypt JavaScript
        echo '<script src="js/addtocart.js"></script>';
        
        // Pobierz kategorie z bazy danych
        $categoriesQuery = "SELECT id, nazwa, matka FROM categories ORDER BY matka, id ASC";
        $categoriesResult = $conn->query($categoriesQuery);
        $categories = array();
        $category_tree = array();

        // Budowanie struktury drzewa
        while($row = $categoriesResult->fetch_assoc()) {
            $categories[$row['id']] = $row;
            if (!isset($category_tree[$row['matka']])) {
                $category_tree[$row['matka']] = array();
            }
            $category_tree[$row['matka']][] = $row['id'];
        }

        // Wyświetlanie filtrowania po kategorii
        echo '<div class="category-filter">';
        echo '<form method="GET" action="index.php">';
        echo '<input type="hidden" name="idp" value="-16">';
        echo '<select name="category" onchange="this.form.submit()">';
        echo '<option value="">Wszystkie kategorie</option>';
        
    /**
     * printCategoryOptions - Rekurencyjnie wyświetla opcje kategorii w formie drzewa
     *
     * Funkcja ta generuje opcje wyboru dla formularza HTML, reprezentujące hierarchiczną
     * strukturę kategorii. Każda podkategoria jest wcięta, aby odzwierciedlić jej poziom
     * w drzewie kategorii. Funkcja obsługuje również zaznaczanie wybranej kategorii.
     */
       
    function printCategoryOptions($categories, $category_tree, $parent_id, $depth, $selected_id) {
        // Sprawdza, czy istnieją podkategorie dla danej kategorii nadrzędnej
        if (!isset($category_tree[$parent_id])) return;
        
        // Iteruje przez wszystkie podkategorie danej kategorii nadrzędnej
        foreach ($category_tree[$parent_id] as $category_id) {
            // Pobiera informacje o bieżącej kategorii
            $category = $categories[$category_id];
            // Tworzy wcięcie dla wizualnej hierarchii kategorii
            $indent = str_repeat('&nbsp;&nbsp;', $depth);
            // Sprawdza, czy bieżąca kategoria jest aktualnie wybrana
            $selected = ($selected_id == $category_id) ? 'selected' : '';
            // Generuje opcję HTML dla bieżącej kategorii
            echo '<option value="' . $category_id . '" ' . $selected . '>' . 
                 $indent . htmlspecialchars($category['nazwa']) . '</option>';
            // Rekurencyjnie wywołuje funkcję dla podkategorii bieżącej kategorii
            printCategoryOptions($categories, $category_tree, $category_id, $depth + 1, $selected_id);
        }
    }
        
        $selected_category = isset($_GET['category']) ? intval($_GET['category']) : '';
        printCategoryOptions($categories, $category_tree, 0, 0, $selected_category);
        
        echo '</select>';
        echo '</form>';
        echo '</div>';
        
        // Zmodyfikuj zapytanie o produkty, aby uwzględnić filtrowanie po kategorii
        $query = "SELECT * FROM products WHERE ilosc_dostepnych > 0 AND status_dostepnosci = 1";
        
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $categoryId = intval($_GET['category']);
            
            // Znajdź wszystkie podkategorie wybranej kategorii
            function getAllChildCategories($category_tree, $parent_id, &$children) {
                if (isset($category_tree[$parent_id])) {
                    foreach ($category_tree[$parent_id] as $child_id) {
                        $children[] = $child_id;
                        getAllChildCategories($category_tree, $child_id, $children);
                    }
                }
            }
            
            $category_ids = [$categoryId];
            getAllChildCategories($category_tree, $categoryId, $category_ids);
            $category_ids_str = implode(',', $category_ids);
            
            $query .= " AND kategoria IN ($category_ids_str)";
        }
        
        $query .= " ORDER BY id DESC";
        $result = $conn->query($query);

        // Rozpoczęcie listy produktów
        echo '<div class="product-list">';
        // Sprawdzenie czy są dostępne produkty
        if ($result->num_rows > 0) {
            // Iteracja przez wszystkie produkty
            while ($product = $result->fetch_assoc()) {
                // Obliczenie ceny brutto
                $grossPrice = $product['cena_netto'] * (1 + ($product['podatek_vat'] / 100));
                // Początek kontenera produktu
                echo '<div class="product-item">';
                // Wyświetlenie tytułu produktu
                echo '<h3>' . htmlspecialchars($product['tytul']) . '</h3>';
                // Wyświetlenie zdjęcia produktu
                echo '<img src="data:image/jpeg;base64,' . base64_encode($product['zdjecie']) . '" alt="' . htmlspecialchars($product['tytul']) . '" class="product-image">';
                // Wyświetlenie opisu produktu
                echo '<p>' . htmlspecialchars($product['opis']) . '</p>';
                // Wyświetlenie ceny brutto
                echo '<p>Cena brutto: ' . number_format($grossPrice, 2) . ' PLN</p>';
                // Wyświetlenie dostępnej ilości
                echo '<p>Dostępna ilość: ' . $product['ilosc_dostepnych'] . '</p>';
                // Początek formularza dodawania do koszyka
                echo '<form method="POST" action="index.php?idp=-16" class="add-to-cart-form">';
                // Ukryte pole z ID produktu
                echo '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
                // Kontrolka ilości
                echo '<div class="quantity-control">';
                // Pole wyboru ilości
                echo '<input type="number" name="quantity" value="1" min="1" max="' . $product['ilosc_dostepnych'] . '" class="quantity-input">';
                // Przycisk dodawania do koszyka
                echo '<button type="submit" name="action" value="add" ' . ($product['ilosc_dostepnych'] > 0 ? '' : 'disabled') . '>';
                // Tekst przycisku zależny od dostępności
                echo $product['ilosc_dostepnych'] > 0 ? 'Dodaj do koszyka' : 'Produkt niedostępny';
                echo '</button>';
                // Zamknięcie kontrolki ilości
                echo '</div>';
                // Zamknięcie formularza
                echo '</form>';
                // Zamknięcie kontenera produktu
                echo '</div>';
            }
        } else {
            echo '<p>Brak dostępnych produktów.</p>';
        }
        echo '</div>';
        
        // Style dla powiadomień
        echo '<style>
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                border-radius: 5px;
                color: white;
                z-index: 1000;
                animation: slideIn 0.5s ease-out;
            }
            
            .notification.success {
                background-color: rgba(40, 167, 69, 0.9);
            }
            
            .notification.error {
                background-color: rgba(220, 53, 69, 0.9);
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>';
    }

    /*
     * Checkout() - Finalizuje zakup
     * Funkcja przetwarza zakup, aktualizując ilości produktów w bazie
     * i czyszcząc koszyk po udanym zakupie. Używa transakcji do
     * zapewnienia spójności danych.
     * @return bool - true jeśli zakup się powiódł, false w przeciwnym razie
     */
    function Checkout() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return false;
        }

        global $conn;
        $success = true;

        // Rozpocznij transakcję aby zapewnić spójność wszystkich aktualizacji
        $conn->begin_transaction();

        try {
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                // Pobierz aktualną ilość produktu
                $query = "SELECT ilosc_dostepnych FROM products WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($product = $result->fetch_assoc()) {
                    $newQuantity = $product['ilosc_dostepnych'] - $quantity;
                    
                    // Sprawdź czy mamy wystarczającą ilość
                    if ($newQuantity < 0) {
                        throw new Exception("Niewystarczająca ilość produktu o ID: " . $productId);
                    }

                    // Zaktualizuj ilość produktu i status jeśli potrzeba
                    $updateQuery = "UPDATE products SET 
                        ilosc_dostepnych = ?,
                        status_dostepnosci = CASE WHEN ? = 0 THEN 0 ELSE status_dostepnosci END 
                        WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("iii", $newQuantity, $newQuantity, $productId);
                    
                    if (!$updateStmt->execute()) {
                        throw new Exception("Błąd podczas aktualizacji produktu o ID: " . $productId);
                    }
                    
                    $updateStmt->close();
                }
                $stmt->close();
            }

            // Jeśli dotarliśmy tutaj, wszystkie aktualizacje się powiodły
            $conn->commit();
            // Wyczyść koszyk
            $_SESSION['cart'] = array();
            return true;

        } catch (Exception $e) {
            // Jeśli wystąpił błąd, wycofaj wszystkie zmiany
            $conn->rollback();
            echo '<div class="error-message">Błąd podczas realizacji zakupu: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    /*
     * ClearCart() - Czyści zawartość koszyka
     * Funkcja usuwa wszystkie produkty z koszyka.
     */
    function ClearCart() {
        // Usuń koszyk z sesji
        unset($_SESSION['cart']);
        // Wyświetl komunikat o sukcesie
        echo '<div class="success">Koszyk został wyczyszczony.</div>';
    }
}
?>