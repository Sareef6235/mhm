document.addEventListener('DOMContentLoaded', () => {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const totalDisplay = document.querySelector('#grandTotal');
    const totalInput = document.querySelector('#grandTotalInput');

    function calculateGrandTotal() {
        let total = 0;

        quantityInputs.forEach((input) => {
            const price = parseFloat(input.dataset.price || '0');
            const quantity = parseInt(input.value || '0', 10);
            const rowTotal = price * quantity;

            const rowTotalCell = input.closest('tr')?.querySelector('.row-total');
            if (rowTotalCell) {
                rowTotalCell.textContent = rowTotal.toFixed(2);
            }

            total += rowTotal;
        });

        if (totalDisplay) {
            totalDisplay.textContent = total.toFixed(2);
        }

        if (totalInput) {
            totalInput.value = total.toFixed(2);
        }
    }

    quantityInputs.forEach((input) => {
        input.addEventListener('input', calculateGrandTotal);
    });

    calculateGrandTotal();
});
