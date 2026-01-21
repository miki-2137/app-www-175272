/*
 * Obsługuje funkcjonalność dodawania produktów do koszyka.
 * Inicjalizuje obsługę formularzy, walidację ilości i wysyłanie żądań AJAX.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Skrypt znajduje wszystkie formularze dodawania do koszyka
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');

    addToCartForms.forEach(form => {
        const quantityInput = form.querySelector('.quantity-input');
        const maxQuantity = parseInt(quantityInput.getAttribute('max'));

        // Skrypt dodaje walidację wprowadzonej ilości
        quantityInput.addEventListener('input', function() {
            let value = parseInt(this.value);
            // Skrypt sprawdza, czy wartość jest prawidłowa i mieści się w dozwolonym zakresie
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > maxQuantity) {
                this.value = maxQuantity;
            }
        });

        // Skrypt obsługuje wysyłanie formularza
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = form.querySelector('input[name="product_id"]').value;
            const quantity = parseInt(form.querySelector('.quantity-input').value);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            
            // Skrypt przygotowuje dane do wysłania
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('action', 'add');
            formData.append('ajax', 'true');

            // Skrypt tymczasowo dezaktywuje przycisk
            submitButton.disabled = true;
            submitButton.textContent = 'Dodawanie...';

            // Skrypt wysyła żądanie AJAX
            fetch('index.php?idp=-16', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Błąd podczas dodawania do koszyka');
                }
                return response.text();
            })
            .then(() => {
                // Skrypt aktualizuje tekst przycisku na potwierdzenie
                submitButton.textContent = 'Dodano!';
                
                // Skrypt aktualizuje licznik w koszyku jeśli istnieje
                updateCartCounter();
                
                // Skrypt resetuje wartość inputu do 1
                quantityInput.value = 1;
                
                // Skrypt po 2 sekundach przywraca oryginalny tekst i aktywuje przycisk
                setTimeout(() => {
                    submitButton.textContent = originalButtonText;
                    submitButton.disabled = false;
                }, 2000);
                
                // Skrypt pokazuje komunikat o sukcesie
                showNotification(`Dodano ${quantity} szt. do koszyka!`);
            })
            .catch(error => {
                console.error('Błąd podczas dodawania do koszyka:', error);
                submitButton.textContent = 'Błąd!';
                
                // Skrypt po 2 sekundach przywraca oryginalny tekst i aktywuje przycisk
                setTimeout(() => {
                    submitButton.textContent = originalButtonText;
                    submitButton.disabled = false;
                }, 2000);
                
                showNotification('Wystąpił błąd podczas dodawania do koszyka', 'error');
            });
        });
    });

    /**
     * Pokazuje powiadomienie na stronie.
     * @param {string} message - Treść powiadomienia.
     * @param {string} type - Typ powiadomienia ('success' lub 'error').
     */
    function showNotification(message, type = 'success') {
        // Skrypt usuwa poprzednie powiadomienie jeśli istnieje
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Skrypt tworzy nowe powiadomienie
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Skrypt dodaje powiadomienie do strony
        document.body.appendChild(notification);
        
        // Skrypt usuwa powiadomienie po 3 sekundach
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    /*
     * Aktualizuje licznik produktów w koszyku.
     */
    function updateCartCounter() {
        fetch('index.php?idp=-17&ajax=true')
            .then(response => response.text())
            .then(data => {
                // Skrypt aktualizuje licznik koszyka, jeśli element istnieje
                const cartCounter = document.querySelector('.cart-counter');
                if (cartCounter) {
                    cartCounter.textContent = data;
                }
            })
            .catch(error => console.error('Błąd podczas aktualizacji licznika koszyka:', error));
    }
});
