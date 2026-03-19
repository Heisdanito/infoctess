<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../connection/connection.php';

$query = "SELECT c.*, p.programme_name 
          FROM courses c  
          LEFT JOIN programme p ON c.programme_id = p.programme_code
          ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = [
        'id' => $row['id'],
        'course_id' => $row['course_id'],
        'course_name' => $row['course_name'],
        'programme_id' => $row['programme_id'],
        'programme_name' => $row['programme_name'],
        'group_id' => $row['group_id'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => count($courses),
    'data' => $courses
]);

mysqli_close($conn);
?>