<?php
header("Content-Type: application/json");
require_once '../connection/connection.php';

$query = "SELECT q.*, c.course_name,
          (SELECT COUNT(*) FROM attendance WHERE session_code = q.session_code) as scan_count
          FROM qrcode q
          LEFT JOIN courses c ON q.course_id = c.course_id
          WHERE q.is_active = '0' 
          ORDER BY q.created_at DESC";

$result = mysqli_query($conn, $query);
$sessions = [];

while ($row = mysqli_fetch_assoc($result)) {
    $sessions[] = $row;
}

echo json_encode($sessions);
?>