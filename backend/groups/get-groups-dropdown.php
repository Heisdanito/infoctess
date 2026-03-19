<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../connection/connection.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required',
        'data' => []
    ]);
    exit;
}

// Simple query for dropdowns - only active groups
$query = "SELECT DISTINCT g.group_id, g.group_name, gm.group_name as main_group_name
          FROM `groups` g
          LEFT JOIN group_main gm ON g.group_id = gm.group_id
          WHERE g.status = 'active'
          ORDER BY g.group_name ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn),
        'data' => []
    ]);
    exit;
}

$groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    $groups[] = [
        'group_id' => $row['group_id'],
        'group_name' => $row['main_group_name'] ?: $row['group_name']
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => count($groups),
    'data' => $groups
]);

mysqli_close($conn);
?>