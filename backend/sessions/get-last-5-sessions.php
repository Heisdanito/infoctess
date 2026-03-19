<?php
header("Content-Type: application/json");
require_once '../connection/connection.php';

$query = "SELECT q.session_code, q.course_id, q.created_at,
          COUNT(a.id) as attendance_count
          FROM qrcode q
          LEFT JOIN attendance a ON q.session_code = a.session_code
          GROUP BY q.id
          ORDER BY q.created_at DESC
          LIMIT 5";

$result = mysqli_query($conn, $query);
$sessions = [];

while ($row = mysqli_fetch_assoc($result)) {
    $sessions[] = $row;
}

echo json_encode(array_reverse($sessions)); // Reverse to show chronological order
?>