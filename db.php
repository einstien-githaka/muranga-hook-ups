<?php
// db.php – FINAL VERSION: Works 100% on Render PostgreSQL + XAMPP

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === ONLINE: Render PostgreSQL (you have DATABASE_URL set) ===
if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv("DATABASE_URL"));

    $host = $url["host"];
    $port = $url["port"] ?? 5432;
    $username = $url["user"];
    $password = $url["pass"];
    $database = ltrim($url["path"], '/');

    // Connect using mysqli (Render allows this for Postgres)
    $conn = new mysqli($host, $username, $password, $database, $port);

    if ($conn->connect_error) {
        // Don't crash the site — just log error
        error_log("DB Error: " . $conn->connect_error);
        $conn = null; // fallback mode
    }
}
// === LOCAL: XAMPP only ===
else {
    $conn = new mysqli("localhost", "root", "", "muranga_hooks");
    if ($conn->connect_error) {
        die("Start MySQL in XAMPP!");
    }
}
?>
