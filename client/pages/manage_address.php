<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['account_id']) || $_SESSION['role_id'] != 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../database/starroofing_db.php';

$userId = $_SESSION['account_id'];
$response = ['success' => false, 'message' => 'Invalid request'];

// Handle different operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Delete address
    if (isset($_POST['delete_address'])) {
        $addressId = intval($_POST['delete_address']);
        
        // Check if address belongs to user
        $checkSql = "SELECT id, is_default FROM user_addresses WHERE id = ? AND account_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $addressId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $address = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($address) {
            $deleteSql = "DELETE FROM user_addresses WHERE id = ? AND account_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("ii", $addressId, $userId);
            
            if ($deleteStmt->execute()) {
                // If deleted address was default, set another address as default
                if ($address['is_default']) {
                    $setDefaultSql = "UPDATE user_addresses SET is_default = 1 WHERE account_id = ? ORDER BY created_at DESC LIMIT 1";
                    $setDefaultStmt = $conn->prepare($setDefaultSql);
                    $setDefaultStmt->bind_param("i", $userId);
                    $setDefaultStmt->execute();
                    $setDefaultStmt->close();
                }
                
                $response = ['success' => true, 'message' => 'Address deleted successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete address'];
            }
            $deleteStmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Address not found'];
        }
    }
    
    // Set default address
    elseif (isset($_POST['set_default'])) {
        $addressId = intval($_POST['set_default']);
        
        // Check if address belongs to user
        $checkSql = "SELECT id FROM user_addresses WHERE id = ? AND account_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $addressId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $checkStmt->close();
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Remove default from all addresses
                $removeDefaultSql = "UPDATE user_addresses SET is_default = 0 WHERE account_id = ?";
                $removeStmt = $conn->prepare($removeDefaultSql);
                $removeStmt->bind_param("i", $userId);
                $removeStmt->execute();
                $removeStmt->close();
                
                // Set new default
                $setDefaultSql = "UPDATE user_addresses SET is_default = 1 WHERE id = ? AND account_id = ?";
                $setStmt = $conn->prepare($setDefaultSql);
                $setStmt->bind_param("ii", $addressId, $userId);
                $setStmt->execute();
                $setStmt->close();
                
                $conn->commit();
                $response = ['success' => true, 'message' => 'Default address updated'];
            } catch (Exception $e) {
                $conn->rollback();
                $response = ['success' => false, 'message' => 'Failed to update default address'];
            }
        } else {
            $checkStmt->close();
            $response = ['success' => false, 'message' => 'Address not found'];
        }
    }
    
    // Add or update address
    elseif (isset($_POST['address_label'])) {
        $addressLabel = trim($_POST['address_label']);
        $street = trim($_POST['street'] ?? '');
        $barangayCode = trim($_POST['barangay_code'] ?? '');
        $barangayName = trim($_POST['barangay_name'] ?? '');
        $cityCode = trim($_POST['city_code'] ?? '');
        $cityName = trim($_POST['city_name'] ?? '');
        $provinceCode = trim($_POST['province_code'] ?? '');
        $provinceName = trim($_POST['province_name'] ?? '');
        $regionCode = trim($_POST['region_code'] ?? '');
        $regionName = trim($_POST['region_name'] ?? '');
        $addressId = isset($_POST['address_id']) ? intval($_POST['address_id']) : null;
        
        // Validate required fields
        if (empty($addressLabel)) {
            $response = ['success' => false, 'message' => 'Address label is required'];
        } else {
            // Update existing address
            if ($addressId) {
                // Check if address belongs to user
                $checkSql = "SELECT id FROM user_addresses WHERE id = ? AND account_id = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("ii", $addressId, $userId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                
                if ($result->num_rows > 0) {
                    $checkStmt->close();
                    
                    $updateSql = "UPDATE user_addresses SET 
                                  address_label = ?, 
                                  street = ?, 
                                  barangay_name = ?, 
                                  city_name = ?, 
                                  province_name = ?, 
                                  region_name = ?
                                  WHERE id = ? AND account_id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("ssssssii", 
                        $addressLabel, $street, $barangayName, 
                        $cityName, $provinceName, $regionName,
                        $addressId, $userId
                    );
                    
                    if ($updateStmt->execute()) {
                        $response = ['success' => true, 'message' => 'Address updated successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to update address'];
                    }
                    $updateStmt->close();
                } else {
                    $checkStmt->close();
                    $response = ['success' => false, 'message' => 'Address not found'];
                }
            }
            // Add new address
            else {
                // Check if this is the first address
                $countSql = "SELECT COUNT(*) as count FROM user_addresses WHERE account_id = ?";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $userId);
                $countStmt->execute();
                $countResult = $countStmt->get_result();
                $count = $countResult->fetch_assoc()['count'];
                $countStmt->close();
                
                // Set as default if it's the first address
                $isDefault = ($count == 0) ? 1 : 0;
                
                $insertSql = "INSERT INTO user_addresses 
                             (account_id, address_label, street, barangay_name, city_name, province_name, region_name, is_default) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param("issssssi", 
                    $userId, $addressLabel, $street, $barangayName, 
                    $cityName, $provinceName, $regionName, $isDefault
                );
                
                if ($insertStmt->execute()) {
                    $response = ['success' => true, 'message' => 'Address added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add address'];
                }
                $insertStmt->close();
            }
        }
    }
}

$conn->close();
echo json_encode($response);
?>