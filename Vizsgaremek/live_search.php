<?php
// live_search.php - Élő keresés eredményei
require_once 'config.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    exit;
}

$searchParam = '%' . $query . '%';
$stmt = $pdo->prepare("
    SELECT id, name, price, image_url 
    FROM shoes 
    WHERE name LIKE :search
    ORDER BY 
        CASE 
            WHEN name LIKE :exact THEN 1
            WHEN name LIKE :start THEN 2
            ELSE 3
        END,
        name
    LIMIT 5
");

$exact = $query . '%';
$start = '%' . $query . '%';

$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->bindParam(':exact', $exact, PDO::PARAM_STR);
$stmt->bindParam(':start', $start, PDO::PARAM_STR);
$stmt->execute();

$results = $stmt->fetchAll();

if (empty($results)): ?>
    <div class="search-result-item">
        <div class="text-muted">Nincs találat</div>
    </div>
<?php else: ?>
    <?php foreach ($results as $item): ?>
        <a href="shoe.php?id=<?= $item['id'] ?>" class="search-result-item">
            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div class="search-result-info">
                <h6><?= htmlspecialchars($item['name']) ?></h6>
                <small><?= number_format($item['price'], 0, ',', ' ') ?> Ft</small>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>