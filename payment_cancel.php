<?php
// payment_cancel.php - Fizetés megszakítva
?>
<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="card shadow-lg border-0 rounded-4 mt-5">
            <div class="card-header bg-warning text-white py-3">
                <h3 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Fizetés megszakítva</h3>
            </div>
            <div class="card-body p-5">
                <div class="mb-4">
                    <i class="fas fa-times-circle text-warning" style="font-size: 5rem;"></i>
                </div>
                <p class="lead mb-4">A fizetési folyamatot megszakítottad.</p>
                <a href="cart.php" class="btn btn-primary rounded-pill px-5">
                    <i class="fas fa-shopping-cart me-1"></i>Vissza a kosárhoz
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>