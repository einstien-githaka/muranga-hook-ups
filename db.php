<?php>

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (getenv('DATABASE_URL') && getenv('DATABASE_URL') !== '') {
    $url = parse_url(getenv("DATABASE_URL"));

    $host     = $url["host"];
    $port     = $url["port"] ?? 5432;
    $username = $url["user"];
    $password = $url["pass"];
    $database = ltrim($url["path"], '/');

    $conn = new mysqli($host, $username, $password, $database, $port);

    if ($conn->connect_error) {
        error_log("Render DB failed: " . $conn->connect_error);
        $conn = null;
    }
} 
    
else {
    $conn = new mysqli("localhost", "root", "", "muranga_hooks");
    if ($conn->connect_error) {
        die("Start MySQL in XAMPP Control Panel!");
    }
}

if (!$conn || $conn->connect_error) {
    $conn = new class {
        public function real_escape_string($s) { return $s; }
        public function query($q) { return true; }
    };
}
?>
