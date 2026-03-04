<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// cart.php - Kosár oldal AJAX mennyiség frissítéssel
require_once 'config.php';
require_once 'paypal_config.php';

// Kosár tartalmának lekérése
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT c.*, s.name, s.price, s.image_url 
        FROM cart c 
        JOIN shoes s ON c.shoe_id = s.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("
        SELECT c.*, s.name, s.price, s.image_url 
        FROM cart c 
        JOIN shoes s ON c.shoe_id = s.id 
        WHERE c.session_id = ?
    ");
    $stmt->execute([getSessionId()]);
}
$cartItems = $stmt->fetchAll();

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Tétel törlése (GET-es hagyományos)
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$removeId, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
        $stmt->execute([$removeId, getSessionId()]);
    }
    header('Location: cart.php');
    exit;
}

// A kosár tételeket elmentjük a session-be, hogy a checkout.php elérje
$_SESSION['checkout_items'] = $cartItems;
$_SESSION['checkout_total'] = $total;

?>
<?php include 'header.php'; ?>

<h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Kosár</h2>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-cart-arrow-down fa-4x mb-3 text-muted"></i>
        <h4>A kosarad üres</h4>
        <p>Nézz szét a <a href="index.php" class="alert-link">főoldalon</a>, és válogass a termékek közül!</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle cart-table" id="cart-table">
            <thead class="table-dark">
                <tr>
                    <th>Termék</th>
                    <th>Méret</th>
                    <th>Egységár</th>
                    <th>Mennyiség</th>
                    <th>Összeg</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                <tr data-cart-id="<?= $item['id'] ?>" data-price="<?= $item['price'] ?>">
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded me-3">
                            <span><?= htmlspecialchars($item['name']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td class="unit-price"><?= number_format($item['price'], 0, ',', ' ') ?> Ft</td>
                    <td style="width: 150px;">
                        <input type="number" class="form-control quantity-input" value="<?= $item['quantity'] ?>" min="0" style="width: 80px;" data-cart-id="<?= $item['id'] ?>">
                    </td>
                    <td class="item-total"><?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> Ft</td>
                    <td>
                        <a href="?remove=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan eltávolítod?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Összesen:</td>
                    <td class="fw-bold text-primary fs-5" id="cart-total"><?= number_format($total, 0, ',', ' ') ?> Ft</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Tovább a pénztárhoz gomb -->
    <div class="d-flex justify-content-end mt-3">
        <a href="checkout.php" class="btn btn-success btn-lg rounded-pill px-5">
            <i class="fas fa-credit-card me-2"></i>Tovább a pénztárhoz
        </a>
    </div>

<?php endif; ?>

<!-- AJAX frissítés JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const cartId = this.dataset.cartId;
            const newQuantity = parseInt(this.value);
            const row = this.closest('tr');
            
            if (newQuantity === 0) {
                if (confirm('Biztosan eltávolítod a terméket a kosárból?')) {
                    window.location.href = '?remove=' + cartId;
                } else {
                    location.reload();
                }
                return;
            }
            
            fetch('update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cart_id=' + encodeURIComponent(cartId) + '&quantity=' + encodeURIComponent(newQuantity)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const unitPrice = parseFloat(row.dataset.price);
                    const newTotal = unitPrice * newQuantity;
                    row.querySelector('.item-total').textContent = newTotal.toLocaleString('hu-HU') + ' Ft';
                    document.getElementById('cart-total').textContent = data.new_total.toLocaleString('hu-HU') + ' Ft';
                } else {
                    alert('Hiba történt a frissítés során!');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Hiba:', error);
                alert('Hálózati hiba!');
                location.reload();
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>