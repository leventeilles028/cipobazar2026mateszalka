<?php
// payment_success.php - Sikeres fizetés utáni oldal (mérettel)
require_once 'config.php';

$paymentId = $_GET['paymentId'] ?? '';
$token = $_GET['token'] ?? '';
$payerId = $_GET['PayerID'] ?? '';

if (!isset($_SESSION['checkout_items']) || !isset($_SESSION['checkout_total']) || !isset($_SESSION['shipping_data'])) {
    header('Location: cart.php');
    exit;
}

$items = $_SESSION['checkout_items'];
$total = $_SESSION['checkout_total'];
$shipping = $_SESSION['shipping_data'];

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;

$shipping_cost = ($shipping['shipping_method'] === 'express') ? 1990 : 990;
$grand_total = $total + $shipping_cost;

try {
    $pdo->beginTransaction();

    // Rendelés fő rekord a szállítási adatokkal
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_id, status, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_zip, shipping_method, payment_method, created_at) VALUES (?, ?, ?, 'completed', ?, ?, ?, ?, ?, ?, ?, NOW())");
    $shipping_name = $shipping['last_name'] . ' ' . $shipping['first_name'];
    $stmt->execute([$userId, $grand_total, $paymentId, $shipping_name, $shipping['phone'], $shipping['address'], $shipping['city'], $shipping['zip'], $shipping['shipping_method'], $shipping['payment_method']]);
    $orderId = $pdo->lastInsertId();

    // Tételek beszúrása mérettel együtt
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, shoe_id, size, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([$orderId, $item['shoe_id'], $item['size'], $item['quantity'], $item['price']]);
    }

    // Kosár ürítése
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
        $stmt->execute([getSessionId()]);
    }

    $pdo->commit();

    // Session adatok törlése
    unset($_SESSION['checkout_items']);
    unset($_SESSION['checkout_total']);
    unset($_SESSION['shipping_data']);

    $success = true;
    $orderIdDisplay = $orderId;
} catch (Exception $e) {
    $pdo->rollBack();
    $success = false;
    $errorMsg = $e->getMessage();
}
?>
<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card shadow-lg border-0 rounded-4 mt-5">
            <div class="card-header bg-success text-white py-3">
                <h3 class="mb-0"><i class="fas fa-check-circle me-2"></i>Sikeres fizetés</h3>
            </div>
            <div class="card-body p-5">
                <?php if ($success): ?>
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Köszönjük a vásárlást!</h4>
                    <p class="lead mb-3">Rendelésed rögzítettük. Az összeg: <strong><?= number_format($grand_total, 0, ',', ' ') ?> Ft</strong></p>
                    <p class="text-muted small">Rendelés azonosító: <code><?= $orderIdDisplay ?></code><br>Fizetés azonosító: <code><?= htmlspecialchars($paymentId) ?></code></p>
                    <p class="mt-3">A rendelés részleteit e-mailben elküldtük.</p>
                    <a href="index.php" class="btn btn-primary rounded-pill px-5 mt-3">
                        <i class="fas fa-home me-1"></i>Vissza a főoldalra
                    </a>
                <?php else: ?>
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Hiba történt a rendelés rögzítésekor</h4>
                    <p class="lead mb-3">Kérlek, vedd fel velünk a kapcsolatot, és add meg a fizetési azonosítót: <code><?= htmlspecialchars($paymentId) ?></code></p>
                    <p class="text-danger">Hiba részlete: <?= htmlspecialchars($errorMsg) ?></p>
                    <a href="index.php" class="btn btn-primary rounded-pill px-5 mt-3">
                        <i class="fas fa-home me-1"></i>Vissza a főoldalra
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>