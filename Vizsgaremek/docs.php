<?php
// docs.php - Dokumentációs központ (csak a megadott 6 dokumentummal)
require_once 'config.php';
?>
<?php include 'header.php'; ?>

<div class="docs-container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-4 p-4 p-md-5">
                <h1 class="text-center mb-4">
                    <i class="fas fa-book-open me-2"></i>Dokumentációk
                </h1>
                <p class="text-center text-muted mb-5">
                    Válassz az alábbi dokumentumok közül a részletes információk megtekintéséhez.
                </p>
                
                <div class="row g-4">
                    <!-- Vizsgaremek dokumentáció -->
                    <div class="col-md-6 col-lg-4">
                        <a href="https://github.com/leventeilles028/cipobazar2026mateszalka" class="text-decoration-none" target="_blank">
                            <div class="doc-card">
                                <div class="doc-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h3>Github</h3>
                                <p>Teljes projekt dokumentáció, áttekintés, csapat bemutatása.</p>
                                <span class="badge bg-primary">GitHub</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Fejlesztői dokumentáció -->
                    <div class="col-md-6 col-lg-4">
                        <a href="https://drive.google.com/file/d/1dZN6ok69QqaP1VM6edBLShtZh7-0HhO7/view?usp=sharing" class="text-decoration-none" target="_blank">
                            <div class="doc-card">
                                <div class="doc-icon">
                                    <i class="fas fa-code"></i>
                                </div>
                                <h3>Fejlesztői</h3>
                                <p>Technikai részletek, adatbázis, architektúra, telepítés.</p>
                                <span class="badge bg-primary">Google Drive</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Tesztelői dokumentáció -->
                    <div class="col-md-6 col-lg-4">
                        <a href="https://drive.google.com/file/d/1RXzoEA9CbbrKVhMWNndZYG-xHu-o_6XE/view?usp=sharing" class="text-decoration-none" target="_blank">
                            <div class="doc-card">
                                <div class="doc-icon">
                                    <i class="fas fa-vial"></i>
                                </div>
                                <h3>Tesztelői</h3>
                                <p>Tesztesetek, hibanapló, kompatibilitási és biztonsági tesztek.</p>
                                <span class="badge bg-primary">Google Drive</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Elkészítési dokumentáció -->
                    <div class="col-md-6 col-lg-4">
                        <a href="https://drive.google.com/file/d/1GKHvbsXvCdOL92Wxizu7XlYli8ibDxSf/view" class="text-decoration-none" target="_blank">
                            <div class="doc-card">
                                <div class="doc-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h3>Elkészítési</h3>
                                <p>Projektterv, munkafolyamat, határidők és mérföldkövek.</p>
                                <span class="badge bg-primary">Google Drive</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Adatbázis -->
                    <div class="col-md-6 col-lg-4">
                        <a href="https://github.com/leventeilles028/cipobazar2026mateszalka" class="text-decoration-none" target="_blank">
                            <div class="doc-card">
                                <div class="doc-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <h3>Adatbázis</h3>
                                <p>Adatbázis diagram, táblák leírása, SQL szkriptek.</p>
                                <span class="badge bg-primary">GitHub</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- PowerPoint -->
                    <div class="col-md-6 col-lg-4">
                        <a href="https://1drv.ms/p/c/6c60f63f4e179462/IQDBJImL1PunTZy6lb0SYYawAR-As1eU4G1jG6W93EPqxPc?e=NaSjgs" class="text-decoration-none" target="_blank">
                            <div class="doc-card">
                                <div class="doc-icon">
                                    <i class="fas fa-presentation"></i>
                                </div>
                                <h3>Prezentáció</h3>
                                <p>Összefoglaló diasor a projekt főbb pontjairól.</p>
                                <span class="badge bg-warning text-dark">OneDrive</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <hr class="my-5">
                <p class="text-center text-muted small">
                    Kattints a kívánt dokumentumra a letöltéshez vagy megtekintéshez.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>