<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$source = $_GET['source'] ?? 'all'; // 'all', 'form', 'chatbot'
$limit = 50;

// Build the base query
$sql = "SELECT i.id, i.firstname, i.lastname, i.email, i.phone, i.message, 
               i.submitted_at, i.source
        FROM inquiries i
        INNER JOIN (
            SELECT conversation_id, MAX(submitted_at) as latest_inquiry
            FROM inquiries
            WHERE COALESCE(is_accepted,0) = 0";

$params = [];
$types = '';

// Add source filter to subquery
if ($source !== 'all') {
    $sql .= " AND source = ?";
}

$sql .= " GROUP BY conversation_id
        ) latest ON i.conversation_id = latest.conversation_id 
        AND i.submitted_at = latest.latest_inquiry
        WHERE COALESCE(i.is_accepted,0) = 0";

// Add source filter to main query
if ($source !== 'all') {
    $sql .= " AND i.source = ?";
    $params[] = $source;
    $types .= 's';
}

// Add search filter
if (strlen($search) > 0) {
    $sql .= " AND (i.firstname LIKE ? OR i.lastname LIKE ? OR i.email LIKE ? OR i.message LIKE ?)";
    $like = "%" . $search . "%";
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}

$sql .= " ORDER BY i.submitted_at DESC LIMIT ?";
$params[] = $limit; 
$types .= 'i';

$stmt = $conn->prepare($sql);
if ($params) {
    // If source filter is used twice (subquery and main query), we need to duplicate it
    if ($source !== 'all') {
        $sourceParams = [$source, $source];
        $otherParams = array_slice($params, 1); // Skip first source param
        $allParams = array_merge($sourceParams, $otherParams);
        $types = 's' . substr($types, 1); // Adjust types string
        $stmt->bind_param($types, ...$allParams);
    } else {
        $stmt->bind_param($types, ...$params);
    }
} else {
    // No parameters needed
}

$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Wrap the response in a standard structure
$response = [
    'success' => true,
    'inquiries' => $data
];

echo json_encode($response);
?>