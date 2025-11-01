<?php
/**
 * Migration Script: Transfer addresses from user_profiles to user_addresses
 * Run this ONCE to migrate existing addresses
 * Place this file in your root or admin directory
 */

require_once 'database/starroofing_db.php';

// Start migration
echo "=== Address Migration Script ===\n";
echo "Starting migration...\n\n";

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get all user profiles with addresses
    $sql = "SELECT 
                account_id,
                street,
                barangay_code,
                barangay_name,
                city_code,
                city_name,
                province_code,
                province_name,
                region_code,
                region_name
            FROM user_profiles 
            WHERE account_id IS NOT NULL 
            AND (street IS NOT NULL OR barangay_name IS NOT NULL OR city_name IS NOT NULL)";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $migratedCount = 0;
        $skippedCount = 0;
        
        while ($row = $result->fetch_assoc()) {
            $accountId = $row['account_id'];
            
            // Check if user already has addresses in new table
            $checkSql = "SELECT COUNT(*) as count FROM user_addresses WHERE account_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $accountId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingCount = $checkResult->fetch_assoc()['count'];
            $checkStmt->close();
            
            // Only migrate if user has no addresses in new table yet
            if ($existingCount == 0) {
                // Check if there's actual address data
                $hasAddressData = !empty($row['street']) || 
                                 !empty($row['barangay_name']) || 
                                 !empty($row['city_name']) || 
                                 !empty($row['province_name']);
                
                if ($hasAddressData) {
                    // Insert into user_addresses with "Home" as default label
                    $insertSql = "INSERT INTO user_addresses 
                                 (account_id, address_label, street, barangay_code, barangay_name, 
                                  city_code, city_name, province_code, province_name, region_code, 
                                  region_name, is_default) 
                                 VALUES (?, 'Home', ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                    
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bind_param("isssssssss", 
                        $accountId,
                        $row['street'],
                        $row['barangay_code'],
                        $row['barangay_name'],
                        $row['city_code'],
                        $row['city_name'],
                        $row['province_code'],
                        $row['province_name'],
                        $row['region_code'],
                        $row['region_name']
                    );
                    
                    if ($insertStmt->execute()) {
                        $migratedCount++;
                        echo "✓ Migrated address for account_id: {$accountId}\n";
                    } else {
                        echo "✗ Failed to migrate address for account_id: {$accountId}\n";
                        echo "  Error: " . $insertStmt->error . "\n";
                    }
                    
                    $insertStmt->close();
                } else {
                    $skippedCount++;
                    echo "- Skipped account_id {$accountId} (no address data)\n";
                }
            } else {
                $skippedCount++;
                echo "- Skipped account_id {$accountId} (already has addresses in new table)\n";
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo "\n=== Migration Complete ===\n";
        echo "Migrated: {$migratedCount} addresses\n";
        echo "Skipped: {$skippedCount} users\n";
        echo "\n";
        
        // Ask about dropping old columns
        echo "=== Next Steps ===\n";
        echo "The old address fields in user_profiles are still there.\n";
        echo "If you want to remove them, run this SQL:\n\n";
        echo "ALTER TABLE user_profiles \n";
        echo "DROP COLUMN street,\n";
        echo "DROP COLUMN barangay_code,\n";
        echo "DROP COLUMN barangay_name,\n";
        echo "DROP COLUMN city_code,\n";
        echo "DROP COLUMN city_name,\n";
        echo "DROP COLUMN province_code,\n";
        echo "DROP COLUMN province_name,\n";
        echo "DROP COLUMN region_code,\n";
        echo "DROP COLUMN region_name;\n\n";
        echo "⚠️  BACKUP your database first before dropping columns!\n";
        
    } else {
        echo "No addresses found to migrate.\n";
        $conn->commit();
    }
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
}

$conn->close();
echo "\n=== Script Finished ===\n";
?>