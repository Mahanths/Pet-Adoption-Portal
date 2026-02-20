<?php
require_once 'config/database.php';

try {
    // First, update the age column to use categories
    $stmt = $conn->prepare("
        UPDATE pets 
        SET age = CASE 
            WHEN age_number BETWEEN 0 AND 1 THEN 'Baby'
            WHEN age_number BETWEEN 2 AND 3 THEN 'Young'
            WHEN age_number BETWEEN 4 AND 7 THEN 'Adult'
            WHEN age_number > 7 THEN 'Senior'
            ELSE 'Young'
        END
        WHERE age = '0' OR age IS NULL
    ");
    $stmt->execute();
    
    echo "Successfully updated age values in the database.";
} catch (PDOException $e) {
    echo "Error updating ages: " . $e->getMessage();
}
?> 