<?php
// add_to_cart.php - Termék kosárba helyezése mérettel, AJAX támogatással
require_once 'config.php';

$response = ['success' => false, 'message' => '', 'count' => 0];

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    $response['message'] = 'Hiányzó termék azonosító';
    echo json_encode($response);
    exit;
}

$shoe_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['id'];
$size = isset($_GET['size']) ? $_GET['size'] : (isset($_POST['size']) ? $_POST['size'] : '40');

// Ellenőrizzük, hogy létezik-e a termék
$stmt = $pdo->prepare("SELECT id FROM shoes WHERE id = ?");
$stmt->execute([$shoe_id]);
if (!$stmt->fetch()) {
    $response['message'] = 'A termék nem létezik';
    echo json_encode($response);
    exit;
}

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    // Megnézzük, hogy már van-e ilyen termék ilyen mérettel a kosárban
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND shoe_id = ? AND size = ?");
    $stmt->execute([$userId, $shoe_id, $size]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$cartItem['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, shoe_id, size, quantity) VALUES (?, ?, ?, 1)");
        $stmt->execute([$userId, $shoe_id, $size]);
    }
} else {
    // Vendég kosár
    $sessionId = getSessionId();
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND shoe_id = ? AND size = ?");
    $stmt->execute([$sessionId, $shoe_id, $size]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$cartItem['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (session_id, shoe_id, size, quantity) VALUES (?, ?, ?, 1)");
        $stmt->execute([$sessionId, $shoe_id, $size]);
    }
}

// Sikeres művelet
$response['success'] = true;
$response['message'] = 'Termék kosárba helyezve';
$response['count'] = getCartCount(); // Visszaadjuk az új kosár darabszámot

// Ha AJAX kérés, akkor JSON választ adunk, egyébként átirányítunk
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Hagyományos kérés esetén visszairányítás
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header('Location: ' . $redirect);
    exit;
}
?>