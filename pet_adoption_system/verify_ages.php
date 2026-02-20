<?php
require_once 'config/database.php';

try {
    // Check the current structure of the pets table
    $stmt = $conn->query("DESCRIBE pets");
    $tableStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Current Table Structure:</h3>";
    echo "<pre>";
    print_r($tableStructure);
    echo "</pre>";

    // Check all distinct age values
    $stmt = $conn->query("SELECT DISTINCT age FROM pets");
    $ages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Current Age Values:</h3>";
    echo "<pre>";
    print_r($ages);
    echo "</pre>";

    // Check some sample pets
    $stmt = $conn->query("SELECT pet_id, name, age FROM pets LIMIT 5");
    $samplePets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Sample Pets:</h3>";
    echo "<pre>";
    print_r($samplePets);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 