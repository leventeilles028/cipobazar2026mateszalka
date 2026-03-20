<?php
// search.php - Keresési eredmények oldal
require_once 'config.php';

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchParam = '%' . $searchTerm . '%';

// Oldalszámozás
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Összes találat számának lekérése
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM shoes WHERE name LIKE ?");
$totalStmt->execute([$searchParam]);
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Találatok lekérése
$stmt = $pdo->prepare("SELECT * FROM shoes WHERE name LIKE :search ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$originalSearchTerm = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
?>
<?php include 'header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Keresési eredmények: "<?= htmlspecialchars($originalSearchTerm) ?>"</h2>
        <p class="text-muted"><?= $total ?> találat</p>
    </div>
</div>

<?php if (empty($shoes)): ?>
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-search fa-4x mb-3"></i>
        <h4>Nincs találat</h4>
        <p>Kérlek próbáld más keresési kifejezéssel.</p>
        <a href="index.php" class="btn btn-primary">Vissza a főoldalra</a>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php foreach ($shoes as $shoe): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <a href="shoe.php?id=<?= $shoe['id'] ?>" class="text-decoration-none">
                        <img src="<?= htmlspecialchars($shoe['image_url']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($shoe['name']) ?>"
                             style="height: 250px; object-fit: cover;">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="shoe.php?id=<?= $shoe['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($shoe['name']) ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars(substr($shoe['description'], 0, 100)) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="h5 text-primary fw-bold"><?= number_format($shoe['price'], 0, ',', ' ') ?> Ft</span>
                            <div>
                                <a href="shoe.php?id=<?= $shoe['id'] ?>" class="btn btn-outline-primary rounded-pill px-3 me-2">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <!-- Kosárba gomb mindenkinek -->
                                <a href="add_to_cart.php?id=<?= $shoe['id'] ?>" class="btn btn-primary rounded-pill px-3">
                                    <i class="fas fa-cart-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Oldalszámozás -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Oldalszámozás" class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?q=<?= urlencode($originalSearchTerm) ?>&page=<?= $page-1 ?>" tabindex="-1">Előző</a>
            </li>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?q=<?= urlencode($originalSearchTerm) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?q=<?= urlencode($originalSearchTerm) ?>&page=<?= $page+1 ?>">Következő</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>