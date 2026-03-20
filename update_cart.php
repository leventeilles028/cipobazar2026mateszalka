<?php
// update_cart.php - Kosár mennyiség frissítése AJAX
require_once 'config.php';

if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false]);
    exit;
}

$cartId = (int)$_POST['cart_id'];
$quantity = (int)$_POST['quantity'];

if ($quantity < 0) {
    echo json_encode(['success' => false]);
    exit;
}

// Ellenőrizzük, hogy a kosár elem a felhasználóhoz vagy session-höz tartozik-e
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE id = ? AND session_id = ?");
    $stmt->execute([$cartId, getSessionId()]);
}

if (!$stmt->fetch()) {
    echo json_encode(['success' => false]);
    exit;
}

if ($quantity == 0) {
    // Törlés
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartId, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
        $stmt->execute([$cartId, getSessionId()]);
    }
} else {
    // Frissítés
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cartId]);
}

// Új összesített végösszeg számítása
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT SUM(s.price * c.quantity) as total FROM cart c JOIN shoes s ON c.shoe_id = s.id WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT SUM(s.price * c.quantity) as total FROM cart c JOIN shoes s ON c.shoe_id = s.id WHERE c.session_id = ?");
    $stmt->execute([getSessionId()]);
}
$newTotal = $stmt->fetchColumn();

echo json_encode(['success' => true, 'new_total' => (int)$newTotal]);
?>