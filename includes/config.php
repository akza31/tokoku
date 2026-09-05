<?php
// TokoKu Configuration
define('APP_NAME', 'TokoKu');
define('APP_VERSION', '1.0.0');
define('APP_DB_PATH', __DIR__ . '/../data/tokoku.db');
define('APP_TIMEZONE', 'Asia/Jakarta');
define('APP_CURRENCY', 'Rp');

date_default_timezone_set(APP_TIMEZONE);

// Create data directory if not exists
if (!is_dir(__DIR__ . '/../data')) {
    mkdir(__DIR__ . '/../data', 0755, true);
}
