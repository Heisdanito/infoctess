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
$student_id = isset($_GET['student_id']) ? mysqli_real_escape_string($conn, $_GET['student_id']) : '';
$session_code = isset($_GET['session_code']) ? mysqli_real_escape_string($conn, $_GET['session_code']) : '';
$group_id = isset($_GET['group_id']) ? mysqli_real_escape_string($conn, $_GET['group_id']) : '';
$course_id = isset($_GET['course_id']) ? mysqli_real_escape_string($conn, $_GET['course_id']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build query
$query = "SELECT a.*, 
          s.student_name, 
          s.student_mail,
          s.programme as student_programme,
          p.programme_name,
          c.course_name,
          g.group_name,
          q.latitude as session_latitude,
          q.longitude as session_longitude
          FROM attendance a
          LEFT JOIN students s ON a.student_id = s.student_id
          LEFT JOIN programme p ON s.programme = p.programme_code
          LEFT JOIN qrcode q ON a.session_code = q.session_code
          LEFT JOIN courses c ON q.course_id = c.course_id
          LEFT JOIN `groups` g ON a.group_id = g.group_id
          WHERE 1=1";

// Add filters
if (!empty($student_id)) {
    $query .= " AND a.student_id = '$student_id'";
}

if (!empty($session_code)) {
    $query .= " AND a.session_code = '$session_code'";
}

if (!empty($group_id)) {
    $query .= " AND a.group_id = '$group_id'";
}

if (!empty($course_id)) {
    $query .= " AND q.course_id = '$course_id'";
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
        'message' => 'Database error: ' . mysqli_error($conn),
        'data' => []
    ]);
    exit;
}

$attendance = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Calculate distance if both locations exist
    $distance = null;
    if (!empty($row['latitude']) && !empty($row['longitude']) && 
        !empty($row['session_latitude']) && !empty($row['session_longitude'])) {
        $distance = calculateDistance(
            $row['latitude'], 
            $row['longitude'], 
            $row['session_latitude'], 
            $row['session_longitude']
        );
    }

    $attendance[] = [
        'id' => $row['id'],
        'student_id' => $row['student_id'],
        'student_name' => $row['student_name'],
        'student_mail' => $row['student_mail'],
        'student_programme' => $row['student_programme'],
        'programme_name' => $row['programme_name'],
        'group_id' => $row['group_id'],
        'group_name' => $row['group_name'],
        'course_id' => $row['course_id'],
        'course_name' => $row['course_name'],
        'session_code' => $row['session_code'],
        'qrcode' => $row['qrcode'],
        'serial' => $row['serial'],
        'latitude' => $row['latitude'],
        'longitude' => $row['longitude'],
        'session_latitude' => $row['session_latitude'],
        'session_longitude' => $row['session_longitude'],
        'distance_meters' => $distance,
        'created_at' => $row['created_at']
    ];
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM attendance a WHERE 1=1";

if (!empty($student_id)) {
    $count_query .= " AND a.student_id = '$student_id'";
}
if (!empty($session_code)) {
    $count_query .= " AND a.session_code = '$session_code'";
}
if (!empty($group_id)) {
    $count_query .= " AND a.group_id = '$group_id'";
}
if (!empty($date_from)) {
    $count_query .= " AND DATE(a.created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $count_query .= " AND DATE(a.created_at) <= '$date_to'";
}

$count_result = mysqli_query($conn, $count_query);
$total = mysqli_fetch_assoc($count_result)['total'];

echo json_encode([
    'status' => 'success',
    'total' => intval($total),
    'count' => count($attendance),
    'data' => $attendance
]);

mysqli_close($conn);

// Helper function to calculate distance between two coordinates
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // meters
    
    $lat1 = floatval($lat1);
    $lon1 = floatval($lon1);
    $lat2 = floatval($lat2);
    $lon2 = floatval($lon2);
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return round($earth_radius * $c, 2);
}
?>