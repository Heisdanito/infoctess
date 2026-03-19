<?php
header("Content-Type: application/json");
require_once '../../backend/connection/connection.php';
session_start();

$student_id = $_SESSION['student_id'] ?? null;
$group_id   = $_SESSION['group_id'] ?? null;

if (!$student_id || !$group_id) {
    echo json_encode([
        "status" => "failed",
        "message" => "Missing student or group ID"
    ]);
    exit;
}

// Step 1: Get programme_id from database
$sql_prog = $conn->query("SELECT programme FROM students WHERE student_id = '$student_id' LIMIT 1");
if (mysqli_num_rows($sql_prog) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "Programme not found for student"
    ]);
    exit;
}
$programme_id = mysqli_fetch_assoc($sql_prog)['programme'];

// Step 2: Check for active QR session
$sql3 = $conn->query("SELECT session_code FROM qrcode WHERE group_id = '$group_id' AND is_active = 1");
if (mysqli_num_rows($sql3) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "No active QR session found"
    ]);
    exit;
}
$token_cc = mysqli_fetch_assoc($sql3)['session_code'];

// Step 3: Count students in group/programme
$sql = $conn->query("SELECT COUNT(*) as attended_students
                     FROM students
                     WHERE group_id = '$group_id' AND programme = '$programme_id'");
if (mysqli_num_rows($sql) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "No students found for this group/programme"
    ]);
    exit;
}
$num_of_group = mysqli_fetch_assoc($sql)['attended_students'];

// Step 4: Count attendance records for this session
$sql_total = $conn->query("SELECT COUNT(*) as total_sessions
                           FROM attendance
                           WHERE group_id = '$group_id' AND session_code = '$token_cc'");
$total_sessions = mysqli_fetch_assoc($sql_total)['total_sessions'];

$percentage = $total_sessions > 0
    ? round(($total_sessions / $num_of_group) * 100, 2)
    : 0;

// Final response
echo json_encode([
    "status" => "success",
    "total_members" => $total_sessions,
    "attended"      => $num_of_group,
    "missed"        => $num_of_group - $total_sessions,
    "percentage"    => $percentage
]);
exit;