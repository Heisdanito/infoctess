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
$is_active = isset($_GET['is_active']) ? mysqli_real_escape_string($conn, $_GET['is_active']) : '';
$group_id = isset($_GET['group_id']) ? mysqli_real_escape_string($conn, $_GET['group_id']) : '';
$course_id = isset($_GET['course_id']) ? mysqli_real_escape_string($conn, $_GET['course_id']) : '';
$created_by = isset($_GET['created_by']) ? mysqli_real_escape_string($conn, $_GET['created_by']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build query
$query = "SELECT q.*, 
          s.student_name as created_by_name,
          c.course_name,
          gm.group_name,
          (SELECT COUNT(*) FROM attendance a WHERE a.session_code = q.session_code) as scan_count
          FROM qrcode q
          LEFT JOIN students s ON q.created_by = s.student_id
          LEFT JOIN courses c ON q.course_id = c.course_id
          LEFT JOIN group_main gm ON q.group_id = gm.group_id
          WHERE 1=1";

if ($is_active !== '') {
    $query .= " AND q.is_active = '$is_active'";
}

if (!empty($group_id)) {
    $query .= " AND q.group_id = '$group_id'";
}

if (!empty($course_id)) {
    $query .= " AND q.course_id = '$course_id'";
}

if (!empty($created_by)) {
    $query .= " AND q.created_by = '$created_by'";
}

if (!empty($date_from)) {
    $query .= " AND DATE(q.created_at) >= '$date_from'";
}

if (!empty($date_to)) {
    $query .= " AND DATE(q.created_at) <= '$date_to'";
}

$query .= " ORDER BY q.created_at DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

$sessions = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Check if session is expired
    $is_expired = false;
    if (!empty($row['expire_at']) && $row['expire_at'] != '0000-00-00 00:00:00') {
        $is_expired = strtotime($row['expire_at']) < time();
    }
    
    $sessions[] = [
        'id' => $row['id'],
        'QRcode' => $row['QRcode'],
        'session_code' => $row['session_code'],
        'course_id' => $row['course_id'],
        'course_name' => $row['course_name'],
        'group_id' => $row['group_id'],
        'group_name' => $row['group_name'],
        'longitude' => $row['longitude'],
        'latitude' => $row['latitude'],
        'serial_status' => $row['serial_status'],
        'is_active' => $row['is_active'],
        'is_expired' => $is_expired,
        'created_by' => $row['created_by'],
        'created_by_name' => $row['created_by_name'],
        'created_at' => $row['created_at'],
        'expire_at' => $row['expire_at'],
        'scan_count' => intval($row['scan_count'])
    ];
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM qrcode WHERE 1=1";
if ($is_active !== '') $count_query .= " AND is_active = '$is_active'";
if (!empty($group_id)) $count_query .= " AND group_id = '$group_id'";
if (!empty($course_id)) $count_query .= " AND course_id = '$course_id'";
if (!empty($created_by)) $count_query .= " AND created_by = '$created_by'";
if (!empty($date_from)) $count_query .= " AND DATE(created_at) >= '$date_from'";
if (!empty($date_to)) $count_query .= " AND DATE(created_at) <= '$date_to'";

$count_result = mysqli_query($conn, $count_query);
$total = mysqli_fetch_assoc($count_result)['total'];

echo json_encode([
    'status' => 'success',
    'total' => intval($total),
    'count' => count($sessions),
    'data' => $sessions
]);

mysqli_close($conn);
?>