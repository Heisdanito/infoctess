<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once '../../backend/connection/connection.php';
    
// Check if user is logged in
if (!isset($_SESSION['student_id']) && !isset($_SESSION['token'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get student ID from session
$student_id = $_SESSION['student_id'] ?? null;

// If student_id not in session, try to get from token
if (!$student_id && isset($_SESSION['token'])) {
    $token = mysqli_real_escape_string($conn, $_SESSION['token']);
    $token_query = "SELECT student_id FROM tokens WHERE severToken = '$token' AND is_active = '1'";
    $token_result = mysqli_query($conn, $token_query);
    
    if ($token_result && mysqli_num_rows($token_result) > 0) {
        $token_row = mysqli_fetch_assoc($token_result);
        $student_id = $token_row['student_id'];
        $_SESSION['student_id'] = $student_id;
    }
}

if (!$student_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Student ID not found'
    ]);
    exit;
}

// Get POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid data received'
    ]);
    exit;
}

// Validate required fields
$session_code = isset($data['session_code']) ? mysqli_real_escape_string($conn, trim($data['session_code'])) : '';
$latitude = isset($data['latitude']) ? mysqli_real_escape_string($conn, $data['latitude']) : '';
$longitude = isset($data['longitude']) ? mysqli_real_escape_string($conn, $data['longitude']) : '';
$course_id = isset($data['course_id']) ? mysqli_real_escape_string($conn, $data['course_id']) : '';

if (empty($session_code)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session code is required'
    ]);
    exit;
}

// Check if session exists and is active
$session_query = "SELECT q.*, c.course_name, g.group_name 
                  FROM qrcode q
                  LEFT JOIN courses c ON q.course_id = c.course_id
                  LEFT JOIN `groups` g ON q.group_id = g.group_id
                  WHERE q.session_code = '$session_code' AND q.is_active = '1'";

$session_result = mysqli_query($conn, $session_query);

if (!$session_result || mysqli_num_rows($session_result) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or expired session code'
    ]);
    exit;
}

$session = mysqli_fetch_assoc($session_result);

// Check if session has expired
if (!empty($session['expire_at']) && $session['expire_at'] != '0000-00-00 00:00:00') {
    $expire_time = strtotime($session['expire_at']);
    if ($expire_time < time()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Session has expired'
        ]);
        exit;
    }
}

// Get student details
$student_query = "SELECT s.*, p.programme_name 
                  FROM students s
                  LEFT JOIN programme p ON s.programme = p.programme_code
                  WHERE s.student_id = '$student_id' AND s.active = '1'";
$student_result = mysqli_query($conn, $student_query);

if (!$student_result || mysqli_num_rows($student_result) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Student not found or inactive'
    ]);
    exit;
}

$student = mysqli_fetch_assoc($student_result);

// Check if student belongs to the group
if ($student['group_id'] != $session['group_id']) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You are not enrolled in this group'
    ]);
    exit;
}

// Check if student has already marked attendance for this session
$check_query = "SELECT id FROM attendance 
                WHERE student_id = '$student_id' AND session_code = '$session_code'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You have already marked attendance for this session'
    ]);
    exit;
}

// Validate location if session has location set
if (!empty($session['latitude']) && !empty($session['longitude'])) {
    if (empty($latitude) || empty($longitude)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Location is required for this session'
        ]);
        exit;
    }
    
    // Calculate distance
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
        return $earth_radius * $c;
    }
    
    $distance = calculateDistance($latitude, $longitude, $session['latitude'], $session['longitude']);
    
    // Allow 100 meters radius
    if ($distance > 100) {
        echo json_encode([
            'status' => 'error',
            'message' => 'You are too far from the class location',
            'distance' => round($distance, 2) . ' meters'
        ]);
        exit;
    }
}

// Get token for attendance record
$token = $_SESSION['token'] ?? '';
if (empty($token)) {
    // Generate a simple token if not available
    $token = $student_id . time() . rand(1000, 9999);
}

// Insert attendance record
$insert_query = "INSERT INTO attendance 
                (student_id, group_id, course_id, session_user_token, session_code, qrcode, serial, latitude, longitude, created_at) 
                VALUES 
                ('$student_id', '{$session['group_id']}', '{$session['course_id']}', '$token', '$session_code', '{$session['QRcode']}', 'manual', '$latitude', '$longitude', NOW())";

if (mysqli_query($conn, $insert_query)) {
    $attendance_id = mysqli_insert_id($conn);
    
    // Optional: Update student's last attendance time
    mysqli_query($conn, "UPDATE students SET updated_at = NOW() WHERE student_id = '$student_id'");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance marked successfully',
        'data' => [
            'attendance_id' => $attendance_id,
            'session_code' => $session_code,
            'course_name' => $session['course_name'],
            'group_name' => $session['group_name'],
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to mark attendance: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>