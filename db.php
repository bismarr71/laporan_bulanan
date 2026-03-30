<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'db_laporan';

try {
    // First connect without selecting a db to create the database if it doesn't exist
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");
    
    // Create reports table if not exists with UNIQUE KEY for upsert queries
    $sql = "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bulan INT NOT NULL,
        tahun INT NOT NULL,
        reportNum INT NOT NULL,
        kegData LONGTEXT,
        attData LONGTEXT,
        notesData LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_month_year (bulan, tahun)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    
    // Create users table and seed default user if not exists
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_users);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['bismar71']);
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('zabuza71', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $insert->execute(['bismar71', $hash]);
    }
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
