<?php
require __DIR__ . '/vendor/autoload.php';

$host = '127.0.0.1';
$dbname = 'si_foreign_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$dbname";

echo "Connecting to DB: $dbname at $host...\n";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $sql = "-- Database Export for Offline App\n";
    $sql .= "-- Database: $dbname\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $sql .= "-- Table structure for `$table`\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $row[1] . ";\n\n";
        
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            $sql .= "-- Data for `$table`\n";
            $sql .= "INSERT INTO `$table` (";
            $keys = array_keys($rows[0]);
            $sql .= "`" . implode("`, `", $keys) . "`) VALUES \n";
            
            $valuesList = [];
            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = "NULL";
                    } else {
                        $values[] = $pdo->quote((string)$value);
                    }
                }
                $valuesList[] = "(" . implode(", ", $values) . ")";
            }
            $sql .= implode(",\n", $valuesList) . ";\n\n";
        }
    }
    
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    file_put_contents(__DIR__ . '/db_pln_awal.sql', $sql);
    echo "Export successful! File created: db_pln_awal.sql\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
