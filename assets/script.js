document.addEventListener('DOMContentLoaded', () => {
    const qtyInputs = document.querySelectorAll('.qty-input');
    const liveTotal = document.querySelector('#liveTotal');

    function calc() {
        let total = 0;
        qtyInputs.forEach((input) => {
            const qty = parseInt(input.value || '0', 10);
            const price = parseFloat(input.dataset.price || '0');
            if (qty > 0) {
                total += qty * price;
            }
        });
        if (liveTotal) {
            liveTotal.textContent = total.toFixed(2);
        }
    }

    qtyInputs.forEach((input) => input.addEventListener('input', calc));
    calc();
});
