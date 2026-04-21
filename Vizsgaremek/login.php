<?php
// login.php - Bejelentkezés maxlength korlátokkal
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];

            // Vendég kosár átmozgatása a felhasználóhoz
            $guestId = getSessionId();
            $stmt = $pdo->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ? AND user_id IS NULL");
            $stmt->execute([$user['id'], $guestId]);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Hibás felhasználónév vagy jelszó!';
        }
    } else {
        $error = 'Minden mező kitöltése kötelező!';
    }
}
?>
<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Belépés</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Felhasználónév</label>
                        <input type="text" class="form-control" id="username" name="username" maxlength="30" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Jelszó</label>
                        <input type="password" class="form-control" id="password" name="password" maxlength="100" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill">Belépés</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="register.php" class="text-decoration-none">Még nincs fiókod? Regisztrálj!</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>