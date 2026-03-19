<?php
header("Content-Type: application/json");
require_once '../connection/connection.php';

$stats = [];

// Active sessions
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM qrcode WHERE is_active = '1'");
$stats['active_sessions'] = mysqli_fetch_assoc($result)['count'];

// Total students
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE active = '1'");
$stats['total_students'] = mysqli_fetch_assoc($result)['count'];

// Present today
$today = date('Y-m-d');
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE DATE(created_at) = '$today'");
$stats['present_today'] = mysqli_fetch_assoc($result)['count'];

// Total courses
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM courses");
$stats['total_courses'] = mysqli_fetch_assoc($result)['count'];

echo json_encode($stats);
?>