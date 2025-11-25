<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === RENDER (ONLINE) DATABASE ===
if (getenv('DATABASE_URL') && getenv('DATABASE_URL') !== '') {
    $url = parse_url(getenv('DATABASE_URL'));

    $host     = $url['host'];
    $port     = $url['port'] ?? 5432;
    $username = $url['user'];
    $password = $url['pass'];
    $database = ltrim($url['path'], '/');

    $conn = new mysqli($host, $username, $password, $database, $port);

    if ($conn->connect_error) {
        error_log('Render DB error: ' . $conn->connect_error);
        $conn = null;
    }
}
// === LOCAL XAMPP ONLY ===
else {
    $conn = new mysqli('localhost', 'root', '', 'muranga_hooks');
    if ($conn->connect_error) {
        die('Start MySQL in XAMPP!');
    }
}

// === FALLBACK: never crash the site ===
if (!$conn || $conn->connect_error) {
    $conn = new class {
        public function real_escape_string($s) { return addslashes($s); }
        public function query($q) { return true; }
        public function insert_id() { return 1; }
    };
}
?>
