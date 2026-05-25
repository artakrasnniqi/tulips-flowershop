document.addEventListener('DOMContentLoaded', () => {
    const cartRows = document.querySelectorAll('[data-price]');

    function updateTotals() {
        let total = 0;
        cartRows.forEach((row) => {
            const price = parseFloat(row.dataset.price || '0');
            const qtyInput = row.querySelector('.js-qty');
            let qty = parseInt(qtyInput?.value || '1', 10);
            const max = parseInt(qtyInput?.max || '0', 10);
            if (max > 0 && qty > max) {
                qty = max;
                qtyInput.value = max;
            }
            const subtotal = price * qty;
            const subtotalCell = row.querySelector('.js-subtotal');
            if (subtotalCell) subtotalCell.textContent = `€${subtotal.toFixed(2)}`;
            total += subtotal;
        });

        document.querySelectorAll('.js-cart-total').forEach((el) => {
            el.textContent = `€${total.toFixed(2)}`;
        });
    }

    cartRows.forEach((row) => {
        const qtyInput = row.querySelector('.js-qty');
        if (qtyInput) {
            qtyInput.addEventListener('input', updateTotals);
        }
    });

    updateTotals();
});
