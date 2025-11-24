<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| 1. RENDER HOSTING (ONLINE) DATABASE
|--------------------------------------------------------------------------
| If DATABASE_URL exists, we assume the site is running online.
*/
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

/*
|--------------------------------------------------------------------------
| 2. LOCAL XAMPP DATABASE
|--------------------------------------------------------------------------
| When developing on localhost.
| MySQL root has no password in XAMPP.
*/
else {

    // Force TCP (fixes "No such file or directory" errors)
    $conn = new mysqli('127.0.0.1', 'root', '', 'muranga-hookups');

    if ($conn->connect_error) {
        die('Start MySQL in XAMPP! Error: ' . $conn->connect_error);
    }
}

/*
|--------------------------------------------------------------------------
| 3. FAILSAFE CONNECTION
|--------------------------------------------------------------------------
| If DB fails, we create a dummy object so the site does not crash.
*/
if (!$conn || $conn->connect_error) {
    $conn = new class {
        public function real_escape_string($s) { return addslashes($s); }
        public function query($q) { return true; }
        public function insert_id() { return 1; }
    };
}
?>
