<?php
// paypal_checkout.php - PayPal fizetési oldal
require_once 'config.php';
require_once 'paypal_config.php';

if (empty($_SESSION['checkout_items']) || empty($_SESSION['shipping_data'])) {
    header('Location: cart.php');
    exit;
}

$shipping = $_SESSION['shipping_data'];
$total = $_SESSION['checkout_total'];
$shipping_cost = ($shipping['shipping_method'] === 'express') ? 1990 : 990;
$grand_total = $total + $shipping_cost;

?>
<?php include 'header.php'; ?>

<div class="row justify-content-center"> <!-- Középre igazítás -->
    <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="fab fa-paypal me-2"></i>PayPal fizetés</h4>
            </div>
            <div class="card-body p-5">
                <h5 class="text-center mb-4">Rendelés összeg: <?= number_format($grand_total, 0, ',', ' ') ?> Ft</h5>
                
                <!-- PayPal sandbox teszt információ - kiemelt helyen, a gomb előtt -->
                <?php if (PAYPAL_ENVIRONMENT === 'sandbox'): ?>
                    <div class="alert alert-warning mb-4" style="background-color: #fff3cd; border-color: #ffeeba;">
                        <h5 class="alert-heading"><i class="fas fa-flask me-2"></i>Teszt fizetési adatok (Sandbox)</h5>
                        <p class="mb-1"><strong>Email:</strong> sb-436of4749556738@personal.example.com</p>
                        <p class="mb-0"><strong>Jelszó:</strong> +M=#kL3n</p>
                        <hr>
                        <p class="mb-0 small">Ezekkel az adatokkal jelentkezhetsz be a PayPal tesztkörnyezetbe a fizetés során. A PayPal ablak a gombra kattintva jelenik meg.</p>
                    </div>
                <?php endif; ?>

                <p class="text-center mb-4">Kattints a gombra a PayPal fizetés indításához.</p>

                <!-- PayPal gomb konténer -->
                <div id="paypal-button-container" class="text-center"></div>

                <div class="text-center mt-4">
                    <a href="checkout.php" class="btn btn-outline-secondary">Módosítom az adatokat</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?= PAYPAL_CLIENT_ID ?>&currency=HUF"></script>
<script>
  paypal.Buttons({
    createOrder: function(data, actions) {
      return actions.order.create({
        purchase_units: [{
          amount: {
            value: '<?= $grand_total ?>'
          },
          description: 'Vásárlás a Cípőbazárban'
        }]
      });
    },
    onApprove: function(data, actions) {
      return actions.order.capture().then(function(details) {
        window.location.href = '<?= PAYPAL_RETURN_URL ?>?paymentId=' + data.orderID;
      });
    },
    onCancel: function(data) {
      window.location.href = '<?= PAYPAL_CANCEL_URL ?>';
    },
    onError: function(err) {
      console.error('PayPal hiba:', err);
      alert('Hiba történt a fizetés során. Ellenőrizd a konzolt!');
    }
  }).render('#paypal-button-container');
</script>

<?php include 'footer.php'; ?>