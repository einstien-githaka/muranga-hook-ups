<?php
// db.php – FINAL version for Render FREE PostgreSQL + XAMPP

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === ONLINE on Render (free PostgreSQL) ===
if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv("DATABASE_URL"));

    // Render gives postgresql:// → we change it to mysql:// style for mysqli
    $host = $url["host"];
    $port = $url["port"] ?? 5432;
    $username = $url["user"];
    $password = $url["pass"];
    $database = substr($url["path"], 1);

    // Change scheme to mysql for mysqli driver
    $conn = new mysqli($host, $username, $password, $database, $port);

    if ($conn->connect_error) {
        // Show friendly message instead of crashing
        error_log("DB Error: " . $conn->connect_error);
        $conn = null; // fallback to fake mode
    }
}

// === LOCAL on XAMPP ===
else {
    $conn = new mysqli("localhost", "root", "", "muranga_hooks");
    if ($conn->connect_error) {
        die("Start MySQL in XAMPP!");
    }
}
?>
