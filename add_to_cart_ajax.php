<?php
// add_to_cart_ajax.php - Termék kosárba helyezése AJAX-szal
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['size'])) {
    echo json_encode(['success' => false, 'error' => 'Hiányzó paraméterek']);
    exit;
}

$shoe_id = (int)$_POST['id'];
$size = $_POST['size'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Ellenőrizzük, hogy létezik-e a termék
$stmt = $pdo->prepare("SELECT id FROM shoes WHERE id = ?");
$stmt->execute([$shoe_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Nem létező termék']);
    exit;
}

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND shoe_id = ? AND size = ?");
    $stmt->execute([$userId, $shoe_id, $size]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $cartItem['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, shoe_id, size, quantity) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $shoe_id, $size, $quantity]);
    }
} else {
    $sessionId = getSessionId();
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND shoe_id = ? AND size = ?");
    $stmt->execute([$sessionId, $shoe_id, $size]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $cartItem['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (session_id, shoe_id, size, quantity) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sessionId, $shoe_id, $size, $quantity]);
    }
}

// Új kosár darabszám
echo json_encode(['success' => true, 'count' => getCartCount()]);
?>