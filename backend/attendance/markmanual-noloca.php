<?php
header("Content-Type: application/json");
session_start();
require_once '../connection/connection.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login first'
    ]);
    exit;
}

$student_id = $_SESSION['student_id'];

// Get POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$session_code = isset($data['session_code']) ? trim($data['session_code']) : '';

if (empty($session_code)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session code is required'
    ]);
    exit;
}

// Escape strings
$session_code = mysqli_real_escape_string($conn, $session_code);
$student_id = mysqli_real_escape_string($conn, $student_id);

// Check if session exists and is active
$session_query = "SELECT * FROM qrcode WHERE session_code = '$session_code' AND is_active = '1'";
$session_result = mysqli_query($conn, $session_query);

if (mysqli_num_rows($session_result) == 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or expired session code'
    ]);
    exit;
}

$session = mysqli_fetch_assoc($session_result);

// Check if already marked
$check_query = "SELECT id FROM attendance WHERE student_id = '$student_id' AND session_code = '$session_code'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Attendance already marked for this session'
    ]);
    exit;
}

// Insert attendance
$insert_query = "INSERT INTO attendance (student_id, group_id, course_id, session_code, qrcode, serial, created_at) 
                VALUES ('$student_id', '{$session['group_id']}', '{$session['course_id']}', '$session_code', '{$session['QRcode']}', 'manual', NOW())";

if (mysqli_query($conn, $insert_query)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance marked successfully!'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>