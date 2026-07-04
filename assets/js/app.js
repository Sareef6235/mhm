/** Premium interaction layer for SmartID Pro UI. */
window.addEventListener('load', () => document.getElementById('loader')?.classList.add('hide'));

function toast(message, type = 'success') {
    const el = document.getElementById('appToast');
    if (!el) return;
    const icon = el.querySelector('.toast-icon i');
    el.querySelector('.toast-body').textContent = message;
    el.dataset.type = type;
    icon.className = type === 'error' ? 'bi bi-x-circle' : type === 'warning' ? 'bi bi-exclamation-triangle' : type === 'info' ? 'bi bi-info-circle' : 'bi bi-check2-circle';
    bootstrap.Toast.getOrCreateInstance(el, { delay: 3200 }).show();
}

function confirmDelete(message = 'Delete this record?') { return confirm(message); }

document.addEventListener('click', event => {
    const sidebarToggle = event.target.closest('[data-toggle-sidebar]');
    if (sidebarToggle) document.getElementById('sidebar')?.classList.toggle('show');

    const themeToggle = event.target.closest('[data-theme-toggle]');
    if (themeToggle) {
        const html = document.documentElement;
        html.dataset.bsTheme = html.dataset.bsTheme === 'dark' ? 'light' : 'dark';
        toast(`${html.dataset.bsTheme === 'dark' ? 'Dark' : 'Light'} mode enabled`, 'info');
    }

    const premiumButton = event.target.closest('.btn-premium');
    if (premiumButton) {
        const ripple = document.createElement('span');
        const rect = premiumButton.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
        ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
        premiumButton.appendChild(ripple);
        setTimeout(() => ripple.remove(), 620);
    }
});

document.querySelector('[data-menu-search]')?.addEventListener('input', event => {
    const query = event.target.value.toLowerCase();
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.style.display = link.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
    });
});

document.querySelectorAll('[data-counter]').forEach(el => {
    const target = Number(el.dataset.counter || 0);
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 70));
    const timer = setInterval(() => {
        current += step;
        if (current >= target) { current = target; clearInterval(timer); }
        el.textContent = current.toLocaleString();
    }, 16);
});

document.querySelectorAll('[data-preview]').forEach(input => input.addEventListener('change', () => {
    const img = document.querySelector(input.dataset.preview);
    if (input.files?.[0] && img) {
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('d-none');
        toast('Image preview ready', 'success');
    }
}));

if (document.getElementById('analyticsChart')) {
    new Chart(document.getElementById('analyticsChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Verified members',
                data: [18, 32, 44, 61, 86, 112, 148],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,.14)',
                pointBackgroundColor: '#06b6d4',
                pointRadius: 4,
                fill: true,
                tension: .42
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: 'rgba(148,163,184,.18)' } } }
        }
    });
}
