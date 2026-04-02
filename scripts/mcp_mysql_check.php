<?php
$dsn = 'mysql:host=127.0.0.1;port=3306;dbname=plp_demo;charset=utf8';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if ($tables) {
        foreach ($tables as $t) {
            echo $t . PHP_EOL;
        }
    } else {
        echo "No tables found\n";
    }
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
