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

// Optional filters
$student_id = isset($_GET['student_id']) ? mysqli_real_escape_string($conn, $_GET['student_id']) : '';
$session_code = isset($_GET['session_code']) ? mysqli_real_escape_string($conn, $_GET['session_code']) : '';
$group_id = isset($_GET['group_id']) ? mysqli_real_escape_string($conn, $_GET['group_id']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build query
$query = "SELECT a.*, 
          s.student_name, 
          s.programme as student_programme,
          c.course_name,
          gm.group_name
          FROM attendance a
          LEFT JOIN students s ON a.student_id = s.student_id
          LEFT JOIN qrcode q ON a.session_code = q.session_code
          LEFT JOIN courses c ON q.course_id = c.course_id
          LEFT JOIN group_main gm ON a.group_id = gm.group_id
          WHERE 1=1";

if (!empty($student_id)) {
    $query .= " AND a.student_id = '$student_id'";
}

if (!empty($session_code)) {
    $query .= " AND a.session_code = '$session_code'";
}

if (!empty($group_id)) {
    $query .= " AND a.group_id = '$group_id'";
}

if (!empty($date_from)) {
    $query .= " AND DATE(a.created_at) >= '$date_from'";
}

if (!empty($date_to)) {
    $query .= " AND DATE(a.created_at) <= '$date_to'";
}

$query .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

$attendance = [];
while ($row = mysqli_fetch_assoc($result)) {
    $attendance[] = [
        'id' => $row['id'],
        'student_id' => $row['student_id'],
        'student_name' => $row['student_name'],
        'group_id' => $row['group_id'],
        'group_name' => $row['group_name'],
        'course_id' => $row['course_id'],
        'course_name' => $row['course_name'],
        'session_user_token' => $row['session_user_token'],
        'session_code' => $row['session_code'],
        'qrcode' => $row['qrcode'],
        'serial' => $row['serial'],
        'latitude' => $row['latitude'],
        'longitude' => $row['longitude'],
        'created_at' => $row['created_at']
    ];
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM attendance WHERE 1=1";
if (!empty($student_id)) $count_query .= " AND student_id = '$student_id'";
if (!empty($session_code)) $count_query .= " AND session_code = '$session_code'";
if (!empty($group_id)) $count_query .= " AND group_id = '$group_id'";
if (!empty($date_from)) $count_query .= " AND DATE(created_at) >= '$date_from'";
if (!empty($date_to)) $count_query .= " AND DATE(created_at) <= '$date_to'";

$count_result = mysqli_query($conn, $count_query);
$total = mysqli_fetch_assoc($count_result)['total'];

echo json_encode([
    'status' => 'success',
    'total' => intval($total),
    'count' => count($attendance),
    'data' => $attendance
]);

mysqli_close($conn);
?>