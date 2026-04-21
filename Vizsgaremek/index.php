<?php
// index.php - Modern főoldal ár szűrővel és rendezéssel
require_once 'config.php';

// Oldalszámozás
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Szűrő paraméterek
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';

// WHERE feltételek
$conditions = [];
$params = [];
if (!empty($category)) { $conditions[] = "category = :category"; $params['category'] = $category; }
if ($min_price !== null) { $conditions[] = "price >= :min_price"; $params['min_price'] = $min_price; }
if ($max_price !== null) { $conditions[] = "price <= :max_price"; $params['max_price'] = $max_price; }
$whereSql = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

// Rendezés
switch ($sort) {
    case 'price_asc': $orderBy = "price ASC"; break;
    case 'price_desc': $orderBy = "price DESC"; break;
    case 'name_asc': $orderBy = "name ASC"; break;
    case 'name_desc': $orderBy = "name DESC"; break;
    default: $orderBy = "price ASC";
}

// Termékek száma
$countQuery = "SELECT COUNT(*) FROM shoes $whereSql";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Termékek lekérése
$query = "SELECT * FROM shoes $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) { $stmt->bindValue($key, $value); }
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$shoes = $stmt->fetchAll();

$categories = getCategories();

function buildQueryString($exclude = [], $extra = []) {
    $params = $_GET;
    foreach ($exclude as $key) unset($params[$key]);
    foreach ($extra as $key => $value) $params[$key] = $value;
    return http_build_query($params);
}
?>
<?php include 'header.php'; ?>

<!-- Szűrő és rendező panel -->
<div class="row mb-4 align-items-end" data-aos="fade-up">
    <div class="col-lg-8">
        <form method="GET" action="index.php" class="row g-2">
            <?php if (!empty($category)): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <?php endif; ?>
            <div class="col-md-3">
                <label for="min_price" class="form-label fw-semibold">Min ár (Ft)</label>
                <input type="number" class="form-control" id="min_price" name="min_price" value="<?= htmlspecialchars($min_price ?? '') ?>" min="0" step="1000" placeholder="pl. 10000">
            </div>
            <div class="col-md-3">
                <label for="max_price" class="form-label fw-semibold">Max ár (Ft)</label>
                <input type="number" class="form-control" id="max_price" name="max_price" value="<?= htmlspecialchars($max_price ?? '') ?>" min="0" step="1000" placeholder="pl. 50000">
            </div>
            <div class="col-md-4">
                <label for="sort" class="form-label fw-semibold">Rendezés</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Ár szerint növekvő</option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Ár szerint csökkenő</option>
                    <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Név szerint A-Z</option>
                    <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Név szerint Z-A</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary mt-4">Szűrés</button>
            </div>
        </form>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="index.php" class="btn btn-outline-secondary">Szűrők törlése</a>
    </div>
</div>

<!-- Kategória szűrők -->
<?php if (!empty($categories)): ?>
<div class="category-filters" data-aos="fade-up">
    <a href="?<?= buildQueryString(['category', 'page'], ['category' => '']) ?>" class="category-filter <?= !$category ? 'active' : '' ?>">Összes</a>
    <?php foreach ($categories as $cat): ?>
        <a href="?<?= buildQueryString(['category', 'page'], ['category' => $cat['category']]) ?>" class="category-filter <?= $category === $cat['category'] ? 'active' : '' ?>">
            <?= htmlspecialchars($cat['category']) ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Hero Section (alapból rejtve, JS jeleníti meg első látogatáskor) -->
<section class="hero-section" id="heroSection" data-aos="fade-up">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="hero-title">Lépj stílusosan a <span>világba</span></h1>
            <p class="hero-subtitle">Fedezd fel prémium minőségű cipőinket, amelyekkel minden lépés élmény. Új kollekciók hetente!</p>
        </div>
        <div class="col-lg-6 d-none d-lg-block">
            <img src="https://hypedfam.com/cdn/shop/files/air-force-1-low-pixel-white-158741.png?v=1712371751&width=700" alt="Hero shoe" class="img-fluid" data-aos="fade-left">
        </div>
    </div>
</section>

<!-- Termékek grid -->
<?php if (empty($shoes)): ?>
    <div class="alert alert-info text-center py-5" data-aos="fade-up">
        <i class="fas fa-search fa-4x mb-3"></i>
        <h4>Nincs találat</h4>
        <p>Próbáld meg más szűrési feltételekkel.</p>
    </div>
<?php else: ?>
    <div class="row g-4" id="product-grid">
        <?php foreach ($shoes as $index => $shoe): ?>
            <div class="col-md-6 col-lg-4 col-xl-3" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
                <div class="product-card">
                    <?php if ($shoe['is_new'] ?? false): ?><div class="product-badge">Új</div><?php endif; ?>
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($shoe['image_url']) ?>" alt="<?= htmlspecialchars($shoe['name']) ?>">
                    </div>
                    <div class="product-content">
                        <a href="shoe.php?id=<?= $shoe['id'] ?>" class="product-title"><?= htmlspecialchars($shoe['name']) ?></a>
                        <div class="product-price"><?= number_format($shoe['price'], 0, ',', ' ') ?> <small>Ft</small></div>
                        <div class="product-actions">
                            <a href="shoe.php?id=<?= $shoe['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-info-circle me-1"></i>Részletek</a>
                            <a href="add_to_cart.php?id=<?= $shoe['id'] ?>" class="btn btn-primary add-to-cart-btn"><i class="fas fa-cart-plus me-1"></i>Kosárba</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Oldalszámozás -->
<?php if ($totalPages > 1): ?>
<nav class="mt-5" data-aos="fade-up">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?<?= buildQueryString(['page'], ['page' => $page-1]) ?>"><i class="fas fa-chevron-left"></i></a></li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?<?= buildQueryString(['page'], ['page' => $i]) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="?<?= buildQueryString(['page'], ['page' => $page+1]) ?>"><i class="fas fa-chevron-right"></i></a></li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'footer.php'; ?>