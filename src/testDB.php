<?php
require 'database.php';

$sql = "SELECT COUNT(*) AS total FROM staff";
$result = $connection->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo "✔ Database connection OK<br>";
    echo "✔ Staff records found: " . $row['total'];
} else {
    echo "❌ Query failed: " . $connection->error;
}
?>
