<?php
// filter_by_category.php - Kategória szűrés AJAX-hoz
require_once 'config.php';

$category = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT * FROM shoes";
$params = [];

if ($category) {
    $query .= " WHERE category = :category";
    $params['category'] = $category;
}

$query .= " ORDER BY created_at DESC LIMIT 12";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shoes = $stmt->fetchAll();

foreach ($shoes as $shoe): ?>
    <div class="col-md-6 col-lg-4 col-xl-3">
        <div class="product-card">
            <div class="product-image">
                <img src="<?= htmlspecialchars($shoe['image_url']) ?>" 
                     alt="<?= htmlspecialchars($shoe['name']) ?>">
                <!-- Overlay elhagyva -->
            </div>
            
            <div class="product-content">
                <a href="shoe.php?id=<?= $shoe['id'] ?>" class="product-title">
                    <?= htmlspecialchars($shoe['name']) ?>
                </a>
                <div class="product-price">
                    <?= number_format($shoe['price'], 0, ',', ' ') ?> <small>Ft</small>
                </div>
                <div class="product-actions">
                    <a href="shoe.php?id=<?= $shoe['id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-info-circle me-1"></i>Részletek
                    </a>
                    <a href="add_to_cart.php?id=<?= $shoe['id'] ?>" class="btn btn-primary add-to-cart-btn">
                        <i class="fas fa-cart-plus me-1"></i>Kosárba
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>