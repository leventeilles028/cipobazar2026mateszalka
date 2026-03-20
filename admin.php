<?php
// admin.php - Admin felület (Cipők, Felhasználók, Kommentek, Rendelések) három képpel
require_once 'config.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'shoes';

// --- Cipők műveletek ---
if ($tab === 'shoes') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $category = $_POST['category'] ?: 'Egyéb'; // Ha üres, akkor 'Egyéb'
        $is_new = isset($_POST['is_new']) ? 1 : 0;
        $description = $_POST['description'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
        $image2 = $_POST['image2'] ?: 'assets/placeholder.svg';
        $image3 = $_POST['image3'] ?: 'assets/placeholder.svg';
        
        $stmt = $pdo->prepare("INSERT INTO shoes (name, category, is_new, description, price, image_url, image2, image3) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $category, $is_new, $description, $price, $image_url, $image2, $image3])) {
            $message = '<div class="alert alert-success">Cipő sikeresen hozzáadva!</div>';
        } else {
            $error = '<div class="alert alert-danger">Hiba történt a mentés során!</div>';
        }
    }

    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $category = $_POST['category'] ?: 'Egyéb';
        $is_new = isset($_POST['is_new']) ? 1 : 0;
        $description = $_POST['description'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
        $image2 = $_POST['image2'] ?: 'assets/placeholder.svg';
        $image3 = $_POST['image3'] ?: 'assets/placeholder.svg';
        
        $stmt = $pdo->prepare("UPDATE shoes SET name=?, category=?, is_new=?, description=?, price=?, image_url=?, image2=?, image3=? WHERE id=?");
        if ($stmt->execute([$name, $category, $is_new, $description, $price, $image_url, $image2, $image3, $id])) {
            $message = '<div class="alert alert-success">Cipő sikeresen módosítva!</div>';
        } else {
            $error = '<div class="alert alert-danger">Hiba történt a módosítás során!</div>';
        }
    }

    if (isset($_GET['delete_shoe'])) {
        $id = $_GET['delete_shoe'];
        $stmt = $pdo->prepare("DELETE FROM shoes WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = '<div class="alert alert-success">Cipő sikeresen törölve!</div>';
        } else {
            $error = '<div class="alert alert-danger">Hiba történt a törlés során!</div>';
        }
    }

    $stmt = $pdo->query("SELECT * FROM shoes ORDER BY id DESC");
    $shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Egyedi kategóriák lekérése a meglévő cipőkből
    $stmt = $pdo->query("SELECT DISTINCT category FROM shoes WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $existing_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// --- Felhasználók műveletek ---
if ($tab === 'users') {
    if (isset($_GET['delete_user'])) {
        $id = $_GET['delete_user'];
        if ($id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            if ($stmt->execute([$id])) {
                $message = '<div class="alert alert-success">Felhasználó sikeresen törölve!</div>';
            } else {
                $error = '<div class="alert alert-danger">Hiba történt a törlés során!</div>';
            }
        } else {
            $error = '<div class="alert alert-warning">Nem törölheted saját magad!</div>';
        }
    }

    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Kommentek műveletek ---
if ($tab === 'comments') {
    if (isset($_GET['delete_comment'])) {
        $id = $_GET['delete_comment'];
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = '<div class="alert alert-success">Komment sikeresen törölve!</div>';
        } else {
            $error = '<div class="alert alert-danger">Hiba történt a törlés során!</div>';
        }
    }

    $stmt = $pdo->query("SELECT comments.*, shoes.name as shoe_name FROM comments JOIN shoes ON comments.shoe_id = shoes.id ORDER BY comments.created_at DESC");
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Rendelések megjelenítése ---
if ($tab === 'orders') {
    // LEFT JOIN, hogy a vendég rendelések is megjelenjenek (user_id = NULL)
    $stmt = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php include 'header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cog me-2"></i>Admin felület</h2>
</div>

<!-- Tab navigáció -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'shoes' ? 'active' : '' ?>" href="?tab=shoes">
            <i class="fas fa-shoe-prints me-1"></i>Cipők
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'users' ? 'active' : '' ?>" href="?tab=users">
            <i class="fas fa-users me-1"></i>Felhasználók
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'comments' ? 'active' : '' ?>" href="?tab=comments">
            <i class="fas fa-comments me-1"></i>Kommentek
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'orders' ? 'active' : '' ?>" href="?tab=orders">
            <i class="fas fa-history me-1"></i>Rendelések
        </a>
    </li>
</ul>

<?= $message ?>
<?= $error ?>

<?php if ($tab === 'shoes'): ?>
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-1"></i>Új cipő
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle admin-table">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Kép1</th>
                    <th>Kép2</th>
                    <th>Kép3</th>
                    <th>Név</th>
                    <th>Kategória</th>
                    <th>Új</th>
                    <th>Leírás</th>
                    <th>Ár (Ft)</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shoes as $shoe): ?>
                <tr>
                    <td><?= $shoe['id'] ?></td>
                    <td><img src="<?= htmlspecialchars($shoe['image_url']) ?>" style="width:50px;height:50px;object-fit:cover;" class="rounded"></td>
                    <td><img src="<?= htmlspecialchars($shoe['image2']) ?>" style="width:50px;height:50px;object-fit:cover;" class="rounded"></td>
                    <td><img src="<?= htmlspecialchars($shoe['image3']) ?>" style="width:50px;height:50px;object-fit:cover;" class="rounded"></td>
                    <td><?= htmlspecialchars($shoe['name']) ?></td>
                    <td>
                        <?php if ($shoe['category'] && $shoe['category'] != 'Egyéb'): ?>
                            <span class="badge bg-info"><?= htmlspecialchars($shoe['category']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($shoe['category'] ?: 'Nincs') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($shoe['is_new']): ?>
                            <span class="badge bg-success">Új</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nem</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(substr($shoe['description'],0,50)) ?>...</td>
                    <td><?= number_format($shoe['price'],0,',',' ') ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editShoe(<?= $shoe['id'] ?>, '<?= htmlspecialchars(addslashes($shoe['name'])) ?>', '<?= htmlspecialchars(addslashes($shoe['category'])) ?>', <?= $shoe['is_new'] ?>, '<?= htmlspecialchars(addslashes($shoe['description'])) ?>', <?= $shoe['price'] ?>, '<?= htmlspecialchars(addslashes($shoe['image_url'])) ?>', '<?= htmlspecialchars(addslashes($shoe['image2'])) ?>', '<?= htmlspecialchars(addslashes($shoe['image3'])) ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?tab=shoes&delete_shoe=<?= $shoe['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törlöd?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Hozzáadás Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="tab" value="shoes">
                    <div class="modal-header">
                        <h5 class="modal-title">Új cipő hozzáadása</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_name" class="form-label">Cipő neve</label>
                            <input type="text" class="form-control" id="add_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_category" class="form-label">Kategória</label>
                            <select class="form-select" id="add_category" name="category">
                                <option value="Egyéb">Egyéb</option>
                                <option value="Férfi">Férfi</option>
                                <option value="Női">Női</option>
                                <option value="Gyerek">Gyerek</option>
                                <option value="Sport">Sport</option>
                                <option value="Casual">Casual</option>
                                <option value="Elegáns">Elegáns</option>
                                <!-- Meglévő kategóriák a cipőkből -->
                                <?php if (!empty($existing_categories)): ?>
                                    <option disabled>──────────</option>
                                    <?php foreach ($existing_categories as $cat): ?>
                                        <?php if (!in_array($cat, ['Férfi', 'Női', 'Gyerek', 'Sport', 'Casual', 'Elegáns', 'Egyéb'])): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?> (meglévő)</option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="add_new_category" class="form-label">Vagy új kategória megadása</label>
                            <input type="text" class="form-control" id="add_new_category" placeholder="Pl. Téli, Nyári, etc.">
                            <small class="text-muted">Ha itt adsz meg értéket, az kerül használatra</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="add_is_new" name="is_new" value="1">
                                <label class="form-check-label" for="add_is_new">
                                    Új termék
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="add_description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="add_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="add_price" class="form-label">Ár (Ft)</label>
                            <input type="number" class="form-control" id="add_price" name="price" step="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_image_url" class="form-label">Kép 1 URL</label>
                            <input type="url" class="form-control" id="add_image_url" name="image_url" value="assets/placeholder.svg" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_image2" class="form-label">Kép 2 URL (opcionális)</label>
                            <input type="url" class="form-control" id="add_image2" name="image2" value="assets/placeholder.svg">
                        </div>
                        <div class="mb-3">
                            <label for="add_image3" class="form-label">Kép 3 URL (opcionális)</label>
                            <input type="url" class="form-control" id="add_image3" name="image3" value="assets/placeholder.svg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                        <button type="submit" name="add" class="btn btn-primary" onclick="return setCategory()">Hozzáadás</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Szerkesztés Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="tab" value="shoes">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Cipő szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Cipő neve</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Kategória</label>
                            <select class="form-select" id="edit_category" name="category">
                                <option value="Egyéb">Egyéb</option>
                                <option value="Férfi">Férfi</option>
                                <option value="Női">Női</option>
                                <option value="Gyerek">Gyerek</option>
                                <option value="Sport">Sport</option>
                                <option value="Casual">Casual</option>
                                <option value="Elegáns">Elegáns</option>
                                <!-- Meglévő kategóriák a cipőkből -->
                                <?php if (!empty($existing_categories)): ?>
                                    <option disabled>──────────</option>
                                    <?php foreach ($existing_categories as $cat): ?>
                                        <?php if (!in_array($cat, ['Férfi', 'Női', 'Gyerek', 'Sport', 'Casual', 'Elegáns', 'Egyéb'])): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_new_category" class="form-label">Vagy új kategória megadása</label>
                            <input type="text" class="form-control" id="edit_new_category" placeholder="Pl. Téli, Nyári, etc.">
                            <small class="text-muted">Ha itt adsz meg értéket, az kerül használatra</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_new" name="is_new" value="1">
                                <label class="form-check-label" for="edit_is_new">
                                    Új termék
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Ár (Ft)</label>
                            <input type="number" class="form-control" id="edit_price" name="price" step="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_url" class="form-label">Kép 1 URL</label>
                            <input type="url" class="form-control" id="edit_image_url" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image2" class="form-label">Kép 2 URL</label>
                            <input type="url" class="form-control" id="edit_image2" name="image2">
                        </div>
                        <div class="mb-3">
                            <label for="edit_image3" class="form-label">Kép 3 URL</label>
                            <input type="url" class="form-control" id="edit_image3" name="image3">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                        <button type="submit" name="edit" class="btn btn-primary" onclick="return setEditCategory()">Mentés</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function setCategory() {
        var selectValue = document.getElementById('add_category').value;
        var newCategory = document.getElementById('add_new_category').value.trim();
        
        if (newCategory !== '') {
            document.getElementById('add_category').value = newCategory;
        } else if (selectValue === '') {
            document.getElementById('add_category').value = 'Egyéb';
        }
        return true;
    }
    
    function setEditCategory() {
        var selectValue = document.getElementById('edit_category').value;
        var newCategory = document.getElementById('edit_new_category').value.trim();
        
        if (newCategory !== '') {
            document.getElementById('edit_category').value = newCategory;
        } else if (selectValue === '') {
            document.getElementById('edit_category').value = 'Egyéb';
        }
        return true;
    }
    
    function editShoe(id, name, category, is_new, description, price, image_url, image2, image3) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        
        // Kategória kiválasztása
        const categorySelect = document.getElementById('edit_category');
        if (category && category !== 'Egyéb') {
            // Megnézzük, hogy létezik-e az opció
            let optionExists = false;
            for (let i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value === category) {
                    categorySelect.selectedIndex = i;
                    optionExists = true;
                    break;
                }
            }
            if (!optionExists) {
                // Ha nem létezik, hozzáadjuk
                let option = new Option(category, category, true, true);
                categorySelect.add(option);
            }
        } else {
            categorySelect.value = 'Egyéb';
        }
        
        // Új termék checkbox beállítása
        document.getElementById('edit_is_new').checked = is_new == 1;
        
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_image_url').value = image_url;
        document.getElementById('edit_image2').value = image2 || 'assets/placeholder.svg';
        document.getElementById('edit_image3').value = image3 || 'assets/placeholder.svg';
        
        // Új kategória mező ürítése
        document.getElementById('edit_new_category').value = '';
        
        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }
    </script>

<?php elseif ($tab === 'users'): ?>
    <!-- Felhasználók táblázat -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle admin-table">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Keresztnév</th>
                    <th>Vezetéknév</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Admin</th>
                    <th>Regisztráció</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['first_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['last_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                    <td>
                        <?php if ($user['is_admin']): ?>
                            <span class="badge bg-success">Igen</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nem</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $user['created_at'] ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?tab=users&delete_user=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törlöd ezt a felhasználót?')">
                                <i class="fas fa-trash"></i> Törlés
                            </a>
                        <?php else: ?>
                            <span class="text-muted">(saját)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($tab === 'comments'): ?>
    <!-- Kommentek táblázat -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle admin-table">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Cipő</th>
                    <th>Szerző</th>
                    <th>Komment</th>
                    <th>Dátum</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?= $comment['id'] ?></td>
                    <td><?= htmlspecialchars($comment['shoe_name']) ?></td>
                    <td><?= htmlspecialchars($comment['name']) ?></td>
                    <td><?= htmlspecialchars(substr($comment['comment'],0,50)) ?>...</td>
                    <td><?= $comment['created_at'] ?></td>
                    <td>
                        <a href="?tab=comments&delete_comment=<?= $comment['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Biztosan törlöd?')">
                            <i class="fas fa-trash"></i> Törlés
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($tab === 'orders'): ?>
    <!-- Rendelések táblázat -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle admin-table">
            <thead class="table-dark">
                <tr>
                    <th>Rendelés ID</th>
                    <th>Felhasználó</th>
                    <th>Név</th>
                    <th>Telefon</th>
                    <th>Cím</th>
                    <th>Összeg</th>
                    <th>Fizetés azonosító</th>
                    <th>Státusz</th>
                    <th>Dátum</th>
                    <th>Részletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username'] ?? 'Vendég') ?></td>
                    <td><?= htmlspecialchars($order['shipping_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($order['shipping_phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($order['shipping_zip'] ?? '') . ' ' . htmlspecialchars($order['shipping_city'] ?? '') . ', ' . htmlspecialchars($order['shipping_address'] ?? '') ?></td>
                    <td><?= number_format($order['total_amount'],0,',',' ') ?> Ft</td>
                    <td><code><?= htmlspecialchars($order['payment_id']) ?></code></td>
                    <td>
                        <?php if ($order['status'] === 'completed'): ?>
                            <span class="badge bg-success">Teljesítve</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Függőben</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $order['created_at'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderModal<?= $order['id'] ?>">
                            <i class="fas fa-list"></i> Tételek
                        </button>
                        <div class="modal fade" id="orderModal<?= $order['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Rendelés tételek (<?= $order['id'] ?>)</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php
                                        $stmtItems = $pdo->prepare("SELECT oi.*, s.name FROM order_items oi JOIN shoes s ON oi.shoe_id = s.id WHERE oi.order_id = ?");
                                        $stmtItems->execute([$order['id']]);
                                        $items = $stmtItems->fetchAll();
                                        ?>
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Termék</th>
                                                    <th>Méret</th>
                                                    <th>Mennyiség</th>
                                                    <th>Egységár</th>
                                                    <th>Összeg</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($items as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                                    <td><?= htmlspecialchars($item['size']) ?></td>
                                                    <td><?= $item['quantity'] ?></td>
                                                    <td><?= number_format($item['price'],0,',',' ') ?> Ft</td>
                                                    <td><?= number_format($item['price'] * $item['quantity'],0,',',' ') ?> Ft</td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezár</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>