<?php
// register.php - Regisztráció e-mail címmel, vezetéknév és keresztnév
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Minden kötelező mezőt ki kell tölteni!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Érvénytelen e-mail cím!';
    } elseif ($password !== $confirm) {
        $error = 'A két jelszó nem egyezik!';
    } elseif (strlen($password) < 6) {
        $error = 'A jelszó legalább 6 karakter legyen!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Ez a felhasználónév vagy e-mail cím már foglalt!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, email, phone, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, 0)");
            if ($stmt->execute([$username, $first_name, $last_name, $email, $phone, $hash])) {
                $success = 'Sikeres regisztráció! Most már bejelentkezhetsz.';
            } else {
                $error = 'Hiba történt a regisztráció során.';
            }
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Regisztráció</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Keresztnév *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Vezetéknév *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Felhasználónév *</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail cím *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefonszám (opcionális)</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Jelszó *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Jelszó megerősítés *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 py-2 rounded-pill">Regisztráció</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="login.php" class="text-decoration-none">Már van fiókod? Jelentkezz be!</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>