    </div>
    <nav class="bottom-nav">
        <a class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><i class="bi bi-house-door"></i>Home</a>
        <a class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'kasir.php' ? 'active' : '' ?>" href="kasir.php"><i class="bi bi-cart"></i>Kasir</a>
        <a class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" href="products.php"><i class="bi bi-box"></i>Produk</a>
        <a class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'purchases.php' ? 'active' : '' ?>" href="purchases.php"><i class="bi bi-bag-plus"></i>Masuk</a>
        <a class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'finance.php' ? 'active' : '' ?>" href="finance.php"><i class="bi bi-cash-coin"></i>Uang</a>
        <a class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php"><i class="bi bi-graph-up"></i>Laporan</a>
        <a class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php"><i class="bi bi-gear"></i>Set</a>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
    <script src="assets/js/app.js"></script>
    <?php endif; ?>
</body>
</html>
