<?php
// ============================================================
// db.php — PDO Database Connection
// Marinduque Pasalubong Hub
// ============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'marinduque_pasalubong');
define('DB_USER',    'root');
define('DB_PASS',    '');             // Change to your password
define('DB_CHARSET', 'utf8mb4');

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    DB_HOST, DB_NAME, DB_CHARSET
);

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
} catch (PDOException $e) {
    // Return JSON error if called from API context
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    } else {
        die('<div style="font-family:sans-serif;padding:2rem;color:#c00;">
            <h2>Database Connection Failed</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <p>Please check your database settings in <code>db.php</code> and make sure MySQL is running.</p>
        </div>');
    }
    exit();
}
