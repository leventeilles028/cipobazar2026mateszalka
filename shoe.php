<?php
// shoe.php - Részletes termékoldal képgalériával, kommentekkel és kosárba rakás gombbal
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM shoes WHERE id = ?");
$stmt->execute([$id]);
$shoe = $stmt->fetch();

if (!$shoe) {
    header('Location: index.php');
    exit;
}

$message = '';

// Komment hozzáadás csak bejelentkezett felhasználóknak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment']) && isLoggedIn()) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (shoe_id, name, comment) VALUES (?, ?, ?)");
        if ($stmt->execute([$id, $_SESSION['username'], $comment])) {
            header('Location: shoe.php?id=' . $id . '#comments');
            exit;
        } else {
            $message = '<div class="alert alert-danger">Hiba történt a mentés során.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">A komment mező nem lehet üres!</div>';
    }
}

$stmt = $pdo->prepare("SELECT * FROM comments WHERE shoe_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

// Képek tömbbe rendezése
$images = [$shoe['image_url'], $shoe['image2'], $shoe['image3']];
?>
<?php include 'header.php'; ?>

<div class="row g-5">
    <div class="col-md-6">
        <?php if (count(array_filter($images)) > 1): ?>
            <!-- Bootstrap Carousel ha több kép van -->
            <div id="shoeCarousel" class="carousel slide carousel-dark" data-bs-ride="carousel">
                <div class="carousel-inner rounded-4 shadow-lg">
                    <?php foreach ($images as $index => $img): ?>
                        <?php if (!empty($img) && $img !== 'assets/placeholder.svg'): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100" alt="<?= htmlspecialchars($shoe['name']) ?>" style="max-height: 500px; object-fit: contain; background: #1a1a1a;">
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#shoeCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Előző</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#shoeCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Következő</span>
                </button>
            </div>
        <?php else: ?>
            <!-- Ha csak egy kép van -->
            <img src="<?= htmlspecialchars($shoe['image_url']) ?>" class="img-fluid rounded-4 shadow-lg" alt="<?= htmlspecialchars($shoe['name']) ?>">
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h1 class="fw-bold mb-3"><?= htmlspecialchars($shoe['name']) ?></h1>
        <p class="lead"><?= htmlspecialchars($shoe['description']) ?></p>
        <h3 class="text-primary fw-bold mb-4"><?= number_format($shoe['price'], 0, ',', ' ') ?> Ft</h3>
        
        <!-- Kosárba rakás űrlap – mindenkinek elérhető -->
        <form method="GET" action="add_to_cart.php" class="mb-4">
            <input type="hidden" name="id" value="<?= $shoe['id'] ?>">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="size" class="form-label fw-semibold">Méret:</label>
                    <select name="size" id="size" class="form-select" style="width: 100px;">
                        <?php for ($i = 35; $i <= 45; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-cart-plus me-1"></i>Kosárba
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Vissza gomb mindig a főoldalra -->
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-1"></i>Vissza a főoldalra
        </a>
    </div>
</div>

<hr class="my-5">

<div class="row" id="comments">
    <div class="col-12">
        <h3 class="mb-4"><i class="fas fa-comments me-2"></i>Kommentek</h3>
        <?= $message ?>
        
        <?php if (isLoggedIn()): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="comment" class="form-label fw-semibold">Komment írása (<?= htmlspecialchars($_SESSION['username']) ?>)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Írd meg véleményed..." required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-paper-plane me-1"></i>Küldés
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info d-flex align-items-center gap-2">
                <i class="fas fa-info-circle fs-4"></i>
                <span>Csak bejelentkezett felhasználók írhatnak kommentet. <a href="login.php" class="alert-link">Jelentkezz be</a> vagy <a href="register.php" class="alert-link">regisztrálj</a>!</span>
            </div>
        <?php endif; ?>
        
        <?php if (count($comments) > 0): ?>
            <div class="comment-list">
                <?php foreach ($comments as $c): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-semibold mb-0">
                                    <i class="fas fa-user-circle text-primary me-2"></i><?= htmlspecialchars($c['name']) ?>
                                </h6>
                                <small class="text-muted"><i class="far fa-clock me-1"></i><?= date('Y-m-d H:i', strtotime($c['created_at'])) ?></small>
                            </div>
                            <p class="card-text mb-0"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted text-center py-4">Még nincs egy komment sem. Légy te az első!</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>