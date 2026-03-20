<?php
// filter_products.php - Élő kereséshez vagy szűréshez: visszaadja a termékek HTML-jét
require_once 'config.php';

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (int)$_GET['max_price'] : null;

// SQL feltételek összeállítása
$conditions = [];
$params = [];

if (!empty($searchTerm)) {
    $conditions[] = "name LIKE :search";
    $params['search'] = '%' . $searchTerm . '%';
}
if (!empty($category)) {
    $conditions[] = "category = :category";
    $params['category'] = $category;
}
if ($min_price !== null) {
    $conditions[] = "price >= :min_price";
    $params['min_price'] = $min_price;
}
if ($max_price !== null) {
    $conditions[] = "price <= :max_price";
    $params['max_price'] = $max_price;
}

$whereSql = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

$stmt = $pdo->prepare("SELECT * FROM shoes $whereSql ORDER BY created_at DESC LIMIT 12");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->execute();
$shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML előállítása
ob_start();
if (empty($shoes)):
    echo '<div class="col-12 text-center py-5"><p class="text-muted">Nincs találat</p></div>';
else:
    foreach ($shoes as $shoe):
?>
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
<?php
    endforeach;
endif;
$html = ob_get_clean();

echo $html;
?>