<nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
    <div class="container-fluid">
        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo htmlspecialchars(isset($kullanici['ad'], $kullanici['soyad']) ? $kullanici['ad'] . ' ' . $kullanici['soyad'] : 'Kullanıcı'); ?>
                    <?php if ($adminMi): ?>
                        <span class="badge bg-danger ms-2">Admin</span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="sifre-degistir.php"><i class="fas fa-lock me-2"></i>Şifre Değiştir</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="cikis.php"><i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>