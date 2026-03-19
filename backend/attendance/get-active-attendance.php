<?php
header("Content-Type: application/json");
require_once '../connection/connection.php';

$session_code = isset($_GET['session_code']) ? $_GET['session_code'] : '';

$query = "SELECT a.*, s.student_name 
          FROM attendance a
          LEFT JOIN students s ON a.student_id = s.student_id
          WHERE a.session_code IN (SELECT session_code FROM qrcode WHERE is_active = '1')";

if ($session_code) {
    $session_code = mysqli_real_escape_string($conn, $session_code);
    $query .= " AND a.session_code = '$session_code'";
}

$query .= " ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $query);
$attendance = [];

while ($row = mysqli_fetch_assoc($result)) {
    $attendance[] = $row;
}

echo json_encode($attendance);
?>