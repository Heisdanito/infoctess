<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Add this for development
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start(); 
require_once '../../backend/connection/connection.php';

// Get and validate input
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Debug: Log received data
error_log("Received data: " . print_r($data, true));
error_log("Session data: " . print_r($_SESSION, true));

// Check if data was received
if (!$data) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid JSON data received",
        "debug" => [
            "raw_input" => $rawData,
            "json_error" => json_last_error_msg()
        ]
    ]);
    exit;
}

// Get values with proper null checking
$student_id = $data['student_id'] ?? $_SESSION['student_id'] ?? null;$qrcode = $data['qrcode'] ?? null;
$serial = $data['serial'] ?? 'qrcode';
$latitude = $data['latitude'] ?? $_SESSION['latitude'] ?? null;
$longitude = $data['longitude'] ?? $_SESSION['longitude'] ?? null;

// Validate required fields
if (!$student_id) {
    echo json_encode([
        "status" => "failed",
        "message" => "Student not logged in",
        "debug" => "Session student_id is missing"
    ]);
    exit;
}

if (!$qrcode) {
    echo json_encode([
        "status" => "failed",
        "message" => "QR code is required"
    ]);
    exit;
}

if (!$latitude || !$longitude) {
    echo json_encode([
        "status" => "failed",
        "message" => "Location coordinates are required"
    ]);
    exit;
}

// Escape strings to prevent SQL injection
$student_id = mysqli_real_escape_string($conn, $student_id);
$qrcode = mysqli_real_escape_string($conn, $qrcode);
$serial = mysqli_real_escape_string($conn, $serial);
$latitude = mysqli_real_escape_string($conn, $latitude);
$longitude = mysqli_real_escape_string($conn, $longitude);

// Query to validate student
$sql = mysqli_query($conn, "SELECT group_id, programme, roles 
                            FROM students 
                            WHERE student_id = '$student_id' AND active = '1'");

if (!$sql) {
    echo json_encode([
        "status" => "failed",
        "message" => "Database error: " . mysqli_error($conn)
    ]);
    exit;
}

if (mysqli_num_rows($sql) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "Student not active or not found",
        "student_id" => $student_id
    ]);
    exit;
}

$student = mysqli_fetch_assoc($sql);
$student_group_id = $student['group_id'];
$student_programme = $student['programme'];

// Verify group programme
$sql_group = mysqli_query($conn, "SELECT group_id, programme_id FROM groups
                                 WHERE group_id = '$student_group_id'
                                 AND programme_id = '$student_programme'");

if (!$sql_group) {
    echo json_encode([
        "status" => "failed",
        "message" => "Group query error: " . mysqli_error($conn)
    ]);
    exit;
}

if (mysqli_num_rows($sql_group) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "Group programme mismatch or inactive group",
        "debug" => [
            "group_id" => $student_group_id,
            "programme" => $student_programme
        ]
    ]);
    exit;
}

// Query active QR session - fixed the SQL condition
$sql_qr = mysqli_query($conn, "SELECT * FROM qrcode 
                              WHERE group_id = '$student_group_id'
                              AND is_active = '1' 
                              AND (session_code = '$qrcode' OR qrcode = '$qrcode')");

if (!$sql_qr) {
    echo json_encode([
        "status" => "failed",
        "message" => "QR query error: " . mysqli_error($conn)
    ]);
    exit;
}

if (mysqli_num_rows($sql_qr) < 1) {
    echo json_encode([
        "status" => "failed",
        "QRCode" => $qrcode,
        "message" => "No active QR session found or session expired",
        "debug" => [
            "group_id" => $student_group_id,
            "qrcode" => $qrcode
        ]
    ]);
    exit;
}

$qr = mysqli_fetch_assoc($sql_qr);
$course_id = $qr['course_id'];
$session_code = $qr['session_code'];
$qr_lat = $qr['latitude'];
$qr_long = $qr['longitude'];
$qr_serial = $qr['serial_status'];

// Check login token
$sql_token = mysqli_query($conn, "SELECT * FROM tokens 
                                 WHERE student_id = '$student_id' 
                                 AND is_active = '1'");

if (!$sql_token) {
    echo json_encode([
        "status" => "failed",
        "message" => "Token query error: " . mysqli_error($conn)
    ]);
    exit;
}

if (mysqli_num_rows($sql_token) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid or inactive login token"
    ]);
    exit;
}

// Haversine formula for distance calculation
function haversine($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // meters
    
    // Convert to float if they're strings
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
    return $earth_radius * $c;
}

$distance = haversine($latitude, $longitude, $qr_lat, $qr_long);

// Check if distance is within 100 meters
if ($distance > 100) {
    echo json_encode([
        "status" => "failed",
        "message" => "You are too far from the class location",
        "distance_meters" => round($distance, 2)
    ]);
    exit;
}

// Check if attendance already recorded
$sql_check_att = mysqli_query($conn, "SELECT * FROM attendance 
                                     WHERE student_id = '$student_id' 
                                     AND session_code = '$session_code'");

if (!$sql_check_att) {
    echo json_encode([
        "status" => "failed",
        "message" => "Attendance check error: " . mysqli_error($conn)
    ]);
    exit;
}

if (mysqli_num_rows($sql_check_att) > 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Attendance already recorded",
        "data" => [
            "student_id" => $student_id,
            "session_code" => $session_code
        ]
    ]);
    exit;
}

// Insert attendance using prepared statement
$stmt = $conn->prepare("INSERT INTO attendance 
    (student_id, group_id, course_id, session_user_token, session_code, qrcode, latitude, longitude, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    echo json_encode([
        "status" => "failed",
        "message" => "Prepare statement error: " . $conn->error
    ]);
    exit;
}

$token = $_SESSION['token'] ?? '';

$stmt->bind_param("ssssssss", 
    $student_id, 
    $student_group_id, 
    $course_id,
    $token, 
    $session_code, 
    $qrcode, 
    $latitude, 
    $longitude
);

$result = $stmt->execute();

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Attendance recorded successfully",
        "data" => [
            "student_id" => $student_id,
            "group_id" => $student_group_id,
            "course_id" => $course_id,
            "session_code" => $session_code,
            "distance_meters" => round($distance, 2)
        ]
    ]);
} else {
    echo json_encode([
        "status" => "failed",
        "message" => "Database insertion error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>