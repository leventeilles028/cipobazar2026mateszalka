<?php
// config.php - Adatbázis kapcsolat és session kezelés
session_start();

$host = 'localhost';
$dbname = 'cipobazar';
$username = 'cipobazar';
$password = 'Terminator809070';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Adatbázis kapcsolódási hiba: " . $e->getMessage());
}

// Vendég kosárhoz session ID kezelés
function getSessionId() {
    if (!isset($_SESSION['guest_cart_id'])) {
        $_SESSION['guest_cart_id'] = session_id();
    }
    return $_SESSION['guest_cart_id'];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['is_admin']);
}

function getCartCount() {
    global $pdo;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE session_id = ?");
        $stmt->execute([getSessionId()]);
    }
    return (int)$stmt->fetchColumn();
}

function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT category FROM shoes WHERE category IS NOT NULL ORDER BY category");
    return $stmt->fetchAll();
}

// Felhasználó adatainak lekérése (ha be van jelentkezve)
function getUserData() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>