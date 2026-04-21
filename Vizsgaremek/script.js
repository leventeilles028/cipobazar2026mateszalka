// script.js - Modern interaktív funkciók (hero alapból rejtve, JS jeleníti meg első látogatáskor)

// AOS inicializálása
AOS.init({
    duration: 800,
    once: true,
    offset: 100
});

// Back to top gomb
const backToTop = document.getElementById('backToTop');
if (backToTop) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Hero megjelenítése CSAK első látogatáskor
(function() {
    const heroSection = document.getElementById('heroSection');
    if (!heroSection) return;

    try {
        // Ha NINCS visited flag -> első látogatás, megjelenítjük
        if (localStorage.getItem('visited') !== 'true') {
            heroSection.style.display = 'block';
            localStorage.setItem('visited', 'true');
        }
        // Ha már van flag, a hero marad rejtve (a CSS display: none miatt)
    } catch (e) {
        // Ha localStorage nem elérhető, akkor is megjelenítjük (biztonság kedvéért)
        heroSection.style.display = 'block';
        console.warn('localStorage nem elérhető, a hero minden alkalommal megjelenik');
    }
})();

// Élő keresés
function setupLiveSearch(inputId, resultsId) {
    const searchInput = document.getElementById(inputId);
    const resultsDiv = document.getElementById(resultsId);
    if (!searchInput || !resultsDiv) return;

    let timeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsDiv.style.display = 'none';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`live_search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(html => {
                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'block';
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });
}

setupLiveSearch('searchInput', 'searchResults');
setupLiveSearch('mobileSearchInput', 'mobileSearchResults');

// Kosár mennyiség frissítés (a cart.php oldalon)
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        const cartId = this.dataset.cartId;
        const newQuantity = parseInt(this.value);
        const row = this.closest('tr');
        
        if (newQuantity === 0) {
            if (confirm('Biztosan eltávolítod a terméket?')) {
                window.location.href = `cart.php?remove=${cartId}`;
            } else {
                this.value = 1;
            }
            return;
        }
        
        fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `cart_id=${cartId}&quantity=${newQuantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const unitPrice = parseFloat(row.dataset.price);
                const newTotal = unitPrice * newQuantity;
                row.querySelector('.item-total').textContent = newTotal.toLocaleString('hu-HU') + ' Ft';
                const cartTotal = document.getElementById('cart-total');
                if (cartTotal) {
                    cartTotal.textContent = data.new_total.toLocaleString('hu-HU') + ' Ft';
                }
                updateCartBadge();
            } else {
                alert('Hiba történt!');
                location.reload();
            }
        })
        .catch(() => location.reload());
    });
});

// AJAX kosárba rakás
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const form = this.closest('form');
        let id, size, quantity = 1;
        let originalHref = this.href;

        if (form) {
            id = form.querySelector('input[name="id"]')?.value;
            size = form.querySelector('select[name="size"]')?.value;
        } else {
            const url = new URL(this.href);
            id = url.searchParams.get('id');
            size = url.searchParams.get('size') || '40';
        }

        if (!id) return;

        const originalHtml = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kosárba';
        this.disabled = true;

        fetch('add_to_cart_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&size=${encodeURIComponent(size)}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartBadge();
                showToast('Termék a kosárba helyezve!', 'success');
            } else {
                showToast('Hiba: ' + (data.error || 'Ismeretlen'), 'error');
            }
        })
        .catch(error => {
            console.error('AJAX hiba:', error);
            showToast('Hálózati hiba, átirányítás...', 'error');
            window.location.href = originalHref;
        })
        .finally(() => {
            this.innerHTML = originalHtml;
            this.disabled = false;
        });
    });
});

// Kosár badge frissítése
function updateCartBadge() {
    fetch('get_cart_count.php')
        .then(response => response.text())
        .then(count => {
            const badge = document.getElementById('cart-badge');
            if (badge) {
                badge.textContent = count;
                badge.style.display = (parseInt(count) === 0) ? 'none' : 'inline-block';
            }
        });
}

// Toast értesítés
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
        ${message}
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Kezdeti badge frissítés
document.addEventListener('DOMContentLoaded', updateCartBadge);