<?php
// header.php - Modern fejléc
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cipőbazár - Prémium lábbelik</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Modern fontok -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS animációk -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Egyedi CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigáció -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-crown me-2"></i>Cipőbazár
            </a>
            
            <!-- Kereső mező (asztali) -->
            <div class="d-none d-lg-flex search-wrapper mx-4 flex-grow-1">
                <div class="position-relative w-100">
                    <input type="text" style="color: white;" class="form-control search-input" id="searchInput" placeholder="Keresés cipők között..." autocomplete="off">
                    <i class="fas fa-search search-icon"></i>
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Főoldal
                        </a>
                    </li>
                    <!-- Dokumentáció gomb - BELSŐ OLDALRA MUTAT -->
                    <li class="nav-item">
                        <a class="nav-link" href="docs.php">
                            <i class="fas fa-book me-1"></i>Dokumentáció
                        </a>
                    </li>
                    
                    <!-- KOSÁR IKON -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Kosár
                            <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                        </a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="fas fa-cog me-1"></i>Admin
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($_SESSION['username']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Kijelentkezés</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Belépés
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm rounded-pill px-4 ms-2" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Regisztráció
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Mobil kereső -->
    <div class="d-lg-none mobile-search">
        <div class="container">
            <div class="position-relative">
                <input type="text" class="form-control search-input" id="mobileSearchInput" placeholder="Keresés cipők között..." autocomplete="off">
                <i class="fas fa-search search-icon"></i>
                <div id="mobileSearchResults" class="search-results"></div>
            </div>
        </div>
    </div>
    
    <main class="container my-5 pt-4">