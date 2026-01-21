document.addEventListener('DOMContentLoaded', function() {
    // ===== Funkcje obsługi koszyka =====
    function updateCart(productId, action) {
        // Tworzenie obiektu FormData do przesłania danych
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', action);

        // Wysyłanie żądania AJAX do serwera
        fetch('index.php?idp=-17', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Znajdowanie kontenera koszyka
            const cartContainer = document.querySelector('.cart-items');
            if (cartContainer) {
                // Tworzenie tymczasowego elementu div do parsowania HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Sprawdzanie czy zakup został zrealizowany
                if (tempDiv.querySelector('.success-message')) {
                    cartContainer.innerHTML = tempDiv.querySelector('.success-message').outerHTML;
                    // Usuwanie przycisków po udanym zakupie
                    const cartButtons = document.querySelectorAll('button[value="checkout"], button[value="clear"]');
                    cartButtons.forEach(button => button.remove());
                    return;
                }
                
                // Aktualizacja zawartości koszyka
                const newCartItems = tempDiv.querySelector('.cart-items');
                if (newCartItems) {
                    // Zastępowanie zawartości koszyka nową zawartością
                    cartContainer.innerHTML = newCartItems.innerHTML;
                } else if (tempDiv.querySelector('.cart-empty')) {
                    // Wyświetlanie komunikatu o pustym koszyku
                    cartContainer.innerHTML = tempDiv.querySelector('.cart-empty').outerHTML;
                }
                
                // Ponowne dodawanie event listenerów do zaktualizowanych przycisków
                attachCartEventListeners();
            }
        })
        .catch(error => {
            // Logowanie błędu w przypadku niepowodzenia aktualizacji koszyka
            console.error('Błąd podczas aktualizacji koszyka:', error);
        });
    }

    // Funkcja do dodawania event listenerów do przycisków w koszyku
    function attachCartEventListeners() {
        // Dodawanie obsługi dla przycisków zwiększania ilości
        document.querySelectorAll('button[value="increase"]').forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const productId = this.closest('form').querySelector('input[name="product_id"]').value;
                updateCart(productId, 'increase');
            };
        });

        // Dodawanie obsługi dla przycisków zmniejszania ilości
        document.querySelectorAll('button[value="decrease"]').forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const productId = this.closest('form').querySelector('input[name="product_id"]').value;
                updateCart(productId, 'decrease');
            };
        });

        // Dodawanie obsługi dla przycisków usuwania produktu
        document.querySelectorAll('button[value="remove"]').forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const productId = this.closest('form').querySelector('input[name="product_id"]').value;
                updateCart(productId, 'remove');
            };
        });

        // Dodawanie obsługi dla przycisku czyszczenia koszyka
        const clearCartButton = document.querySelector('button[value="clear"]');
        if (clearCartButton) {
            clearCartButton.onclick = function(e) {
                e.preventDefault();
                updateCart(null, 'clear');
            };
        }

        // Dodawanie obsługi dla przycisku finalizacji zamówienia
        const checkoutButton = document.querySelector('button[value="checkout"]');
        if (checkoutButton) {
            checkoutButton.onclick = function(e) {
                e.preventDefault();
                updateCart(null, 'checkout');
            };
        }
    }

    // ===== Funkcje obsługi ilości produktów =====
    function initializeQuantityControls() {
        // Znajdowanie wszystkich formularzy zmiany ilości
        const quantityForms = document.querySelectorAll('.quantity-form');

        quantityForms.forEach(form => {
            const minusBtn = form.querySelector('.minus');
            const plusBtn = form.querySelector('.plus');
            const quantitySpan = form.querySelector('.quantity-value');
            const productId = form.querySelector('input[name="product_id"]').value;

            // Dodawanie obsługi dla przycisku minus
            minusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleQuantityChange(-1, productId, quantitySpan);
            });

            // Dodawanie obsługi dla przycisku plus
            plusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleQuantityChange(1, productId, quantitySpan);
            });
        });
    }

    function handleQuantityChange(adjustment, productId, quantitySpan) {
        const currentQuantity = parseInt(quantitySpan.textContent);
        
        // Sprawdzanie, czy nie próbuje się zejść poniżej 0
        if (currentQuantity + adjustment < 0) {
            return;
        }

        // Przygotowywanie danych do wysłania
        const formData = new FormData();
        formData.append('adjust_quantity', 'true');
        formData.append('product_id', productId);
        formData.append('adjustment', adjustment);

        // Wysyłanie żądania AJAX
        fetch('index.php?idp=-12', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            // Aktualizowanie wyświetlanej ilości
            quantitySpan.textContent = currentQuantity + adjustment;
            
            // Aktualizowanie klasy statusu dostępności, jeśli potrzeba
            const row = quantitySpan.closest('tr');
            const statusSpan = row.querySelector('.status-available, .status-unavailable');
            if (statusSpan) {
                if (currentQuantity + adjustment > 0) {
                    statusSpan.className = 'status-available';
                    statusSpan.textContent = 'Dostępny';
                } else {
                    statusSpan.className = 'status-unavailable';
                    statusSpan.textContent = 'Niedostępny';
                }
            }
        })
        .catch(error => {
            console.error('Błąd podczas aktualizacji ilości:', error);
            quantitySpan.textContent = currentQuantity;
        });
    }

    // Inicjalizacja obu funkcjonalności
    attachCartEventListeners();
    initializeQuantityControls();
});
