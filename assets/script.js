document.addEventListener('DOMContentLoaded', () => {
    const qtyInputs = document.querySelectorAll('.qty-input');
    const liveTotal = document.querySelector('#liveTotal');
    const showBooksBtn = document.querySelector('#showBooksBtn');
    const showBooksPreview = document.querySelector('#showBooksPreview');
    const showBooksPreviewBody = document.querySelector('#showBooksPreviewBody');

    function updateTotal() {
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

    function buildPreviewRows() {
        if (!showBooksPreviewBody) return;

        const rows = [];
        qtyInputs.forEach((input) => {
            const qty = parseInt(input.value || '0', 10);
            if (qty <= 0) return;

            const tr = input.closest('tr');
            const cells = tr ? tr.querySelectorAll('td') : [];
            const itemName = cells[0] ? cells[0].textContent.trim() : 'Item';
            const priceText = cells.length >= 3 ? cells[cells.length - 2].textContent.trim() : '₹0.00';
            const type = input.name.startsWith('text_qty') ? 'Textbook' : 'Notebook';
            const price = parseFloat(input.dataset.price || '0');
            const lineTotal = (qty * price).toFixed(2);

            rows.push(`<tr>
                <td>${itemName}</td>
                <td>${type}</td>
                <td>${qty}</td>
                <td>${priceText}</td>
                <td>₹${lineTotal}</td>
            </tr>`);
        });

        if (rows.length === 0) {
            showBooksPreviewBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No selected items yet.</td></tr>';
        } else {
            showBooksPreviewBody.innerHTML = rows.join('');
        }
    }

    qtyInputs.forEach((input) => input.addEventListener('input', () => {
        updateTotal();
        if (showBooksPreview && showBooksPreview.style.display !== 'none') {
            buildPreviewRows();
        }
    }));

    if (showBooksBtn && showBooksPreview) {
        showBooksBtn.addEventListener('click', () => {
            const opening = showBooksPreview.style.display === 'none' || showBooksPreview.style.display === '';
            showBooksPreview.style.display = opening ? 'block' : 'none';
            showBooksBtn.textContent = opening ? 'Hide Book' : 'Show Book';
            if (opening) {
                buildPreviewRows();
            }
        });
    }

    updateTotal();
});
