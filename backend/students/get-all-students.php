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

// Get filter parameters
$programme = isset($_GET['programme']) ? mysqli_real_escape_string($conn, $_GET['programme']) : '';
$group = isset($_GET['group']) ? mysqli_real_escape_string($conn, $_GET['group']) : '';
$role = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query with DISTINCT to prevent duplicates
$query = "SELECT DISTINCT s.id, s.student_id, s.student_name, s.student_mail, 
          s.recovery_mail, s.programme, s.roles, s.active, s.group_id, 
          s.created_at, s.updated_at,
          p.programme_name,
          g.group_name,
          (SELECT COUNT(*) FROM attendance a WHERE a.student_id = s.student_id) as total_attendance
          FROM students s
          LEFT JOIN programme p ON s.programme = p.programme_code
          LEFT JOIN `groups` g ON s.group_id = g.group_id
          WHERE 1=1";

// Add filters
if (!empty($programme)) {
    $query .= " AND s.programme = '$programme'";
}

if (!empty($group)) {
    $query .= " AND s.group_id = '$group'";
}

if (!empty($role)) {
    $query .= " AND s.roles = '$role'";
}

if ($status !== '') {
    $query .= " AND s.active = '$status'";
}

if (!empty($search)) {
    $query .= " AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%' OR s.student_mail LIKE '%$search%')";
}

$query .= " GROUP BY s.id ORDER BY s.created_at DESC";

// For debugging - uncomment to see the actual query
// error_log("SQL Query: " . $query);

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn),
        'data' => []
    ]);
    exit;
}

$students = [];
$seen_ids = []; // Track seen IDs to prevent duplicates

while ($row = mysqli_fetch_assoc($result)) {
    // Skip if we've already seen this student ID (extra safety)
    if (in_array($row['student_id'], $seen_ids)) {
        continue;
    }
    $seen_ids[] = $row['student_id'];
    
    // Get recovery mail if exists
    $recovery_mail = isset($row['recovery_mail']) ? $row['recovery_mail'] : '';
    
    $students[] = [
        'id' => $row['id'],
        'student_id' => $row['student_id'],
        'student_name' => $row['student_name'],
        'student_mail' => $row['student_mail'],
        'recovery_mail' => $recovery_mail,
        'programme' => $row['programme'],
        'programme_name' => isset($row['programme_name']) ? $row['programme_name'] : null,
        'group_id' => $row['group_id'],
        'group_name' => isset($row['group_name']) ? $row['group_name'] : null,
        'roles' => $row['roles'],
        'active' => $row['active'],
        'total_attendance' => intval($row['total_attendance']),
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

// If still getting duplicates, let's check the raw data
if (count($students) !== mysqli_num_rows($result)) {
    // There were duplicates filtered out
    error_log("Duplicate students found and filtered");
}

echo json_encode([
    'status' => 'success',
    'count' => count($students),
    'data' => $students
]);

mysqli_close($conn);
?>