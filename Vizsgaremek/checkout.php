<?php
// checkout.php - Pénztár oldal, szállítási adatok megadása maxlength + numeric validációkkal
require_once 'config.php';

// Ellenőrizzük, hogy van-e termék a kosárban
if (empty($_SESSION['checkout_items'])) {
    header('Location: cart.php');
    exit;
}

$userData = null;
if (isLoggedIn()) {
    $userData = getUserData();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adatok feldolgozása
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $shipping_method = $_POST['shipping_method'] ?? 'standard';
    $payment_method = $_POST['payment_method'] ?? 'paypal';

    // Hosszúság ellenőrzések
    $errors = [];
    if (strlen($first_name) > 50) $errors[] = 'A keresztnév legfeljebb 50 karakter lehet.';
    if (strlen($last_name) > 50) $errors[] = 'A vezetéknév legfeljebb 50 karakter lehet.';
    if (strlen($phone) > 20) $errors[] = 'A telefonszám legfeljebb 20 karakter lehet.';
    if (strlen($zip) > 10) $errors[] = 'Az irányítószám legfeljebb 10 karakter lehet.';
    if (strlen($city) > 50) $errors[] = 'A város legfeljebb 50 karakter lehet.';
    if (strlen($address) > 100) $errors[] = 'A cím legfeljebb 100 karakter lehet.';

    // Numerikus ellenőrzések
    if (!empty($phone) && !ctype_digit($phone)) {
        $errors[] = 'A telefonszám csak számjegyeket tartalmazhat.';
    }
    if (!empty($zip) && !ctype_digit($zip)) {
        $errors[] = 'Az irányítószám csak számjegyeket tartalmazhat.';
    }

    if (empty($first_name) || empty($last_name) || empty($phone) || empty($zip) || empty($city) || empty($address)) {
        $error = 'Minden szállítási mező kitöltése kötelező!';
    } elseif (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        // Mentés session-be a későbbi fizetéshez
        $_SESSION['shipping_data'] = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'zip' => $zip,
            'city' => $city,
            'address' => $address,
            'shipping_method' => $shipping_method,
            'payment_method' => $payment_method
        ];

        // Ha be van jelentkezve, frissíthetjük a felhasználó adatait (opcionális)
        if (isLoggedIn() && $userData) {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, zip = ?, city = ?, address = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $zip, $city, $address, $_SESSION['user_id']]);
        }

        // Tovább a fizetési oldalra (most közvetlenül PayPal)
        if ($payment_method === 'paypal') {
            header('Location: paypal_checkout.php');
            exit;
        } else {
            $error = 'Csak PayPal fizetés érhető el.';
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="row">
    <div class="col-md-8">
        <h2 class="mb-4">Szállítási adatok</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="checkout.php">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Személyes adatok</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Keresztnév *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" maxlength="50" value="<?= htmlspecialchars($userData['first_name'] ?? $_POST['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Vezetéknév *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" maxlength="50" value="<?= htmlspecialchars($userData['last_name'] ?? $_POST['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefonszám *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" maxlength="20" inputmode="numeric" pattern="[0-9]*" value="<?= htmlspecialchars($userData['phone'] ?? $_POST['phone'] ?? '') ?>" required onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </div>
                    <h5 class="card-title mb-3 mt-4">Szállítási cím</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="zip" class="form-label">Irányítószám *</label>
                            <input type="text" class="form-control" id="zip" name="zip" maxlength="10" inputmode="numeric" pattern="[0-9]*" value="<?= htmlspecialchars($userData['zip'] ?? $_POST['zip'] ?? '') ?>" required onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="city" class="form-label">Város *</label>
                            <input type="text" class="form-control" id="city" name="city" maxlength="50" value="<?= htmlspecialchars($userData['city'] ?? $_POST['city'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Cím (utca, házszám) *</label>
                        <input type="text" class="form-control" id="address" name="address" maxlength="100" value="<?= htmlspecialchars($userData['address'] ?? $_POST['address'] ?? '') ?>" required>
                    </div>

                    <h5 class="card-title mb-3 mt-4">Szállítási mód</h5>
                    <div class="mb-3">
                        <select class="form-select" name="shipping_method" id="shipping_method">
                            <option value="standard">Standard szállítás (2-3 munkanap) - 990 Ft</option>
                            <option value="express">Expressz szállítás (1 munkanap) - 1990 Ft</option>
                        </select>
                    </div>

                    <h5 class="card-title mb-3 mt-4">Fizetési mód</h5>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" checked>
                            <label class="form-check-label" for="paypal">
                                <i class="fab fa-paypal me-1"></i> PayPal (bankkártya is)
                            </label>
                        </div>
                        <!-- További fizetési módok itt lehetnek -->
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-5">
                <a href="cart.php" class="btn btn-outline-secondary rounded-pill px-4">Vissza a kosárhoz</a>
                <button type="submit" class="btn btn-success rounded-pill px-5">Tovább a fizetéshez</button>
            </div>
        </form>
    </div>

    <!-- Rendelés összegzés -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
            <div class="card-body">
                <h5 class="card-title">Rendelés összegzése</h5>
                <hr>
                <?php 
                $total = $_SESSION['checkout_total'];
                $shipping_cost = 990; // Alapértelmezett
                ?>
                <table class="table table-sm">
                    <tr>
                        <td>Termékek összesen:</td>
                        <td class="text-end"><?= number_format($total, 0, ',', ' ') ?> Ft</td>
                    </tr>
                    <tr>
                        <td>Szállítási költség:</td>
                        <td class="text-end" id="shipping-cost">990 Ft</td>
                    </tr>
                    <tr>
                        <th>Összesen:</th>
                        <th class="text-end" id="grand-total"><?= number_format($total + 990, 0, ',', ' ') ?> Ft</th>
                    </tr>
                </table>
                <p class="text-muted small">A végeredmény a szállítási mód függvényében változhat.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Szállítási költség frissítése
document.getElementById('shipping_method').addEventListener('change', function() {
    let shippingCost = 0;
    if (this.value === 'standard') {
        shippingCost = 990;
    } else if (this.value === 'express') {
        shippingCost = 1990;
    }
    document.getElementById('shipping-cost').textContent = shippingCost.toLocaleString('hu-HU') + ' Ft';
    let total = <?= $total ?> + shippingCost;
    document.getElementById('grand-total').textContent = total.toLocaleString('hu-HU') + ' Ft';
});
</script>

<?php include 'footer.php'; ?>