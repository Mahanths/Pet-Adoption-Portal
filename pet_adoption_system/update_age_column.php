<?php
require_once 'config/database.php';

try {
    // First, add a temporary column to store the string age
    $stmt = $conn->prepare("ALTER TABLE pets ADD COLUMN age_category ENUM('Baby', 'Young', 'Adult', 'Senior') AFTER age");
    $stmt->execute();
    
    // Update the temporary column with appropriate values based on age number
    $stmt = $conn->prepare("
        UPDATE pets 
        SET age_category = CASE 
            WHEN age = 0 THEN 'Baby'
            WHEN age BETWEEN 1 AND 3 THEN 'Young'
            WHEN age BETWEEN 4 AND 7 THEN 'Adult'
            WHEN age > 7 THEN 'Senior'
            ELSE 'Young'
        END
    ");
    $stmt->execute();
    
    // Drop the old age column
    $stmt = $conn->prepare("ALTER TABLE pets DROP COLUMN age");
    $stmt->execute();
    
    // Rename age_category to age
    $stmt = $conn->prepare("ALTER TABLE pets CHANGE age_category age ENUM('Baby', 'Young', 'Adult', 'Senior')");
    $stmt->execute();
    
    echo "Successfully updated the age column to use categories.";
} catch (PDOException $e) {
    echo "Error updating age column: " . $e->getMessage();
}
?> 